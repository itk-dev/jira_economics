<?php

namespace Billing\Repository;

use Billing\Entity\Worklog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Worklog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Worklog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Worklog[]    findAll()
 * @method Worklog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorklogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Worklog::class);
    }

    // /**
    //  * @return Worklog[] Returns an array of Worklog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Worklog
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
