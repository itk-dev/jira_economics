<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Billing\Repository\ExpenseRepository")
 */
class Expense
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isBilled;

    /**
     * @ORM\ManyToOne(targetEntity="Billing\Entity\InvoiceEntry", inversedBy="expenses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invoiceEntry;

    /**
     * @ORM\Column(type="integer")
     */
    private $expenseId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsBilled(): ?bool
    {
        return $this->isBilled;
    }

    public function setIsBilled(?bool $isBilled): self
    {
        $this->isBilled = $isBilled;

        return $this;
    }

    public function getInvoiceEntry(): ?InvoiceEntry
    {
        return $this->invoiceEntry;
    }

    public function setInvoiceEntry(?InvoiceEntry $invoiceEntry): self
    {
        $this->invoiceEntry = $invoiceEntry;

        return $this;
    }

    public function getExpenseId(): ?int
    {
        return $this->expenseId;
    }

    public function setExpenseId(int $expenseId): self
    {
        $this->expenseId = $expenseId;

        return $this;
    }
}
