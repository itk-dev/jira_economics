<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Repository;

use Billing\Entity\InvoiceEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InvoiceEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceEntry[]    findAll()
 * @method InvoiceEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceEntryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InvoiceEntry::class);
    }
}
