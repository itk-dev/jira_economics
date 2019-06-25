<?php

namespace App\Repository;

use App\Entity\GsFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GsFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method GsFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method GsFile[]    findAll()
 * @method GsFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GsFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GsFile::class);
    }

    // /**
    //  * @return GsFile[] Returns an array of GsFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GsFile
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
