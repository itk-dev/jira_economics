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
 * @ORM\Entity(repositoryClass="Billing\Repository\WorklogRepository")
 */
class Worklog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $worklogId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isBilled;

    /**
     * @ORM\ManyToOne(targetEntity="Billing\Entity\InvoiceEntry", inversedBy="worklogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invoiceEntry;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorklogId(): ?int
    {
        return $this->worklogId;
    }

    public function setWorklogId(int $worklogId): self
    {
        $this->worklogId = $worklogId;

        return $this;
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
}
