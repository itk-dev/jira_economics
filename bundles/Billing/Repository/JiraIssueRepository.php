<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Repository;

use Billing\Entity\JiraIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JiraIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method JiraIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method JiraIssue[]    findAll()
 * @method JiraIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JiraIssueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JiraIssue::class);
    }

    // /**
    //  * @return JiraIssue[] Returns an array of JiraIssue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JiraIssue
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
