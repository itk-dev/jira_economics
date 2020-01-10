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
use GraphicServiceOrder\Entity\GsOrder;

/**
 * @method GsOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method GsOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method GsOrder[]    findAll()
 * @method GsOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GsOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GsOrder::class);
    }
}
