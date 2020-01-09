<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GraphicServiceOrder\Entity\Debtor;

/**
 * @method Debtor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debtor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debtor[]    findAll()
 * @method Debtor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Debtor::class);
    }
}
