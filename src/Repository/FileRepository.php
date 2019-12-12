<?php

namespace App\Repository;

use App\Entity\Parser\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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
}
