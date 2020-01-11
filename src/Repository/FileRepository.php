<?php

namespace App\Repository;

use App\Entity\Parser\File;
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

    public function findByFileData(array $fileData = [])
    {

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

    private function completeQueryFromFilters(QueryBuilder &$qb, array $filters = []): QueryBuilder
    {
        $filters = array_merge([
            'type' => null,
            'createdFrom' => null,
            'createdTo' => null,
            'downloadedFrom' => null,
            'downloadedTo' => null,
            'limit' => 10,
            'offset' => 0,
            'select' => null
        ], $filters);

        if ($filters['select'])
            $qb->select($filters['select']);
        if (!$filters['select'])
            $qb->select('f');

        $qb->from("App:Parser\File", 'f');

        if ($filters['type']) {
            switch ($filters['type']) {
                case 'queued':
                    $qb->where('f.downloadedAt IS NULL');
                    break;

                case 'downloaded':
                    $qb->where('f.downloadedAt IS NOT NULL');
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
