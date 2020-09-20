<?php

namespace App\Repository;

use App\Entity\Parser\File;
use App\Enum\FileStatus;
use App\Enum\FileType;
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

    public function getQb(): QueryBuilder
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
            ->getQuery()
            ->getArrayResult();
    }

    public function getFilesCountData(string $status = FileStatus::Queued): array
    {
        $filters = [
            'select' => 'COUNT(f.id) as totalCount, SUM(f.size) as totalSize',
            'status' => $status
        ];

        return $this->getFilesQb($filters)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getQueuedFiles(int $limit = 10, bool $asArray = true): array
    {
        return $this->getFilesQb(['status' => FileStatus::Queued, 'limit' => $limit])
            ->getQuery()
            ->getResult(($asArray) ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }

    public function countAllQueuedFiles(): int
    {
        $result = $this->getFilesQb(['status' => FileStatus::Queued, 'select' => 'COUNT(f.id) as queuedFilesCount'])
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return (int)$result['queuedFilesCount'];
    }

    /**
     * Selects random file entities from database;
     *
     * @param string $parser
     * @param int $limit
     * @param bool $imagesOnly
     * @return array
     */
    public function getRandomFiles(string $parser = null, int $limit = 1, bool $imagesOnly = true): array
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('f')
            ->from('App:Parser\File', 'f')
            ->addSelect('RAND() as HIDDEN rand');

        if ($parser)
            $qb->where('f.parser = :parser')->setParameter('parser', $parser);

        if ($imagesOnly)
            $qb->andWhere('f.type = :imageType')
                ->andWhere('f.binHash IS NOT NULL')
                ->setParameter('imageType', FileType::Image);

        return $qb->orderBy('rand')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Searches for files entries similar to passed file entity;
     *
     * @param File $file
     * @return array
     */
    public function getSimilarFiles(File $file): array
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('f')
            ->from('App:Parser\File', 'f')
            ->andWhere('f.downloadedAt IS NOT NULL');

        $minDimensionRatio = round(($file->getDimensionRatio() * 0.98), 2);
        $maxDimensionRatio = round(($file->getDimensionRatio() * 1.02), 2);

        if ($file->getType() === FileType::Image) {
            $qb->andWhere('(f.hexHash IS NOT NULL)')
                ->andWhere('(f.width = :imageWidth AND f.height = :imageHeight)
                OR (f.dimensionRatio > :minDimensionRatio AND f.dimensionRatio < :maxDimensionRatio)'
            )->setParameters([
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

        if ($file->getId())
            $qb->andWhere('f.id <> :fileID')->setParameter('fileID', $file->getId());

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

    /**
     * Completes filters array with empty values if specified keys does not exists;
     *
     * @param array $filters
     * @return array
     */
    private function completeFilters(array &$filters): array
    {
        $filters = array_merge([
            'status' => null,
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

    /**
     * Completes passed query based on array with filters;
     *
     * @param QueryBuilder $qb
     * @param array $filters
     * @return QueryBuilder
     */
    private function completeQueryFromFilters(QueryBuilder &$qb, array $filters = []): QueryBuilder
    {
        $this->completeFilters($filters);

        if ($filters['select'])
            $qb->select($filters['select']);
        else
            $qb->select('f');

        $qb->from("App:Parser\File", 'f');

        if ($filters['status']) {
            switch ($filters['status']) {
                case FileStatus::Queued:
                    $qb->where('f.downloadedAt IS NULL AND f.duplicateOf IS NULL');
                    break;

                case FileStatus::Downloaded:
                    $qb->where('f.downloadedAt IS NOT NULL AND f.duplicateOf IS NULL');
                    break;

                case FileStatus::Duplicated:
                    $qb->where('f.duplicateOf IS NOT NULL');
                    break;
            }
        }

        if ($filters['createdFrom'])
            $qb->andWhere('f.createdAt >= :timeFrom')->setParameter('timeFrom', $filters['createdFrom']);
        if ($filters['createdTo'])
            $qb->andWhere('f.createdAt >= :timeTo')->setParameter('timeTo', $filters['createdTo']);
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
