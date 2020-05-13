<?php

namespace App\Repository;

use App\Entity\Parser\File;
use App\Enum\FileType;
use App\Model\ParsedFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function getQb()
    {
        return $this->_em->createQueryBuilder()
            ->select('f')
            ->from(File::class, 'f');
    }

    public function getStoredFilesArray(string $parserName, array $filesIdentifiers): array
    {
        return $this->getQb()->where('f.parser = :parserName')
            ->andWhere('f.identifier IN (:filesIdentifiers)')
            ->setParameter('parserName', $parserName)
            ->setParameter('filesIdentifiers', $filesIdentifiers)
            ->getQuery()->getArrayResult();
    }

    public function getFilesCountData(string $type = 'queued'): array
    {
        $filters = [
            'select' => 'COUNT(f.id) as totalCount, SUM(f.size) as totalSize',
            'type' => $type
        ];

        return $this->getFilesQb($filters)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getQueuedFiles(int $limit = 10, bool $asArray = true): array
    {
        return $this->getFilesQb(['type' => 'queued', 'limit' => $limit])
            ->getQuery()
            ->getResult(($asArray) ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }

    public function getSimilarFiles(File $file): array
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('f')
            ->from('App:Parser\File', 'f')
            ->where('f.downloadedAt IS NOT NULL');

        $minDimensionRatio = round(($file->getDimensionRatio() * 0.98), 2);
        $maxDimensionRatio = round(($file->getDimensionRatio() * 1.02), 2);

        if ($file->getType() === FileType::Image) {
            $qb->andWhere('(f.hexHash IS NOT NULL)')
                ->andWhere('(f.width = :imageWidth AND f.height = :imageHeight)
                OR (f.dimensionRatio > :minDimensionRatio AND f.dimensionRatio < :maxDimensionRatio)            
            ')->setParameters([
                'minDimensionRatio' => $minDimensionRatio,
                'maxDimensionRatio' => $maxDimensionRatio,
                'imageWidth' => $file->getWidth(),
                'imageHeight' => $file->getHeight()
            ]);
        } elseif ($file->getType() === FileType::Video) {
            if ($file->getWidth())
                $qb->andWhere('(f.width IS NULL OR f.width = :videoWidth)')
                    ->setParameter('videoWidth', $file->getWidth());

            if ($file->getHeight())
                $qb->andWhere('(f.height IS NULL OR f.height = :videoHeight)')
                    ->setParameter('videoHeight', $file->getHeight());

            if ($file->getLength())
                $qb->andWhere('f.length = :videoLength')
                    ->setParameter('videoLength', $file->getLength());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $filters
     * @return QueryBuilder
     */
    public function getFilesQb(array $filters = []): QueryBuilder
    {
        $qb = $this->_em->createQueryBuilder();

        $this->completeQueryFromFilters($qb, $filters);

        return $qb;
    }


    public function getRandomFiles(string $parser, int $limit = 1, bool $imagesOnly = true): array
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('f')
            ->from('App:Parser\File', 'f')
            ->addSelect('RAND() as HIDDEN rand')
            ->where('f.parser = :parser')
            ->setParameter('parser', $parser);

        if ($imagesOnly)
            $qb->andWhere('f.type = :imageType')
                ->setParameter('imageType', FileType::Image);

        return $qb
            ->orderBy('rand')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $filters
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countFiles(array $filters = []): int
    {
        $qb = $this->_em->createQueryBuilder();

        $filters['select'] = 'COUNT(f.id) as files_count';

        $this->completeQueryFromFilters($qb, $filters);

        $counter = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        return $counter['files_count'];
    }

    private function completeFilters(array &$filters = []): array
    {
        $filters = array_merge([
            'type' => null,
            'createdFrom' => null,
            'createdTo' => null,
            'downloadedFrom' => null,
            'downloadedTo' => null,
            'limit' => 10,
            'offset' => 0,
            'select' => null,
            'singleResult' => false,
            'arrayResult' => false
        ], $filters);

        return $filters;
    }

    private function completeQueryFromFilters(QueryBuilder &$qb, array $filters = []): QueryBuilder
    {
        $this->completeFilters();

        if ($filters['select'])
            $qb->select($filters['select']);
        if (!$filters['select'])
            $qb->select('f');

        $qb->from("App:Parser\File", 'f');

        if ($filters['type']) {
            switch ($filters['type']) {
                case 'queued':
                    $qb->where('f.downloadedAt IS NULL AND f.duplicateOf IS NULL');
                    break;

                case 'downloaded':
                    $qb->where('f.downloadedAt IS NOT NULL AND f.duplicateOf IS NULL');
                    break;
            }
        }

        if ($filters['createdFrom'])
            $qb->andWhere('f.createdAt >= :timeFrom')->setParameter('timeFrom', $filters['createdFrom']);
        if ($filters['createdTo'])
            $qb->andWhere('f.createdAt >= :timeTo')->setParameter('timeTo', $filters['createdFrom']);
        if ($filters['downloadedFrom'])
            $qb->andWhere('f.downloadedAt >= :downloadedFrom')->setParameter('downloadedFrom', $filters['downloadedFrom']);
        if ($filters['downloadedTo'])
            $qb->andWhere('f.downloadedAt >= :downloadedTo')->setParameter('downloadedTo', $filters['downloadedTo']);
        if ($filters['limit'] > 0)
            $qb->setMaxResults($filters['limit']);
        if ($filters['offset'] > 0)
            $qb->setFirstResult($filters['offset']);

        return $qb;
    }
}
