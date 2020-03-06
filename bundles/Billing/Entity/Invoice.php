<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Entity;

use App\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Billing\Repository\InvoiceRepository")
 */
class Invoice extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Billing\Entity\Project", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToMany(targetEntity="Billing\Entity\InvoiceEntry", mappedBy="invoice", orphanRemoval=true)
     */
    private $invoiceEntries;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recorded;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $customerAccountId;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $recordedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $exportedDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lockedCustomerKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lockedContactName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lockedType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lockedAccountKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lockedSalesChannel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paidByAccount;

    public function __construct()
    {
        $this->invoiceEntries = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection|InvoiceEntry[]
     */
    public function getInvoiceEntries(): Collection
    {
        return $this->invoiceEntries;
    }

    public function addInvoiceEntry(InvoiceEntry $invoiceEntry): self
    {
        if (!$this->invoiceEntries->contains($invoiceEntry)) {
            $this->invoiceEntries[] = $invoiceEntry;
            $invoiceEntry->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceEntry(InvoiceEntry $invoiceEntry): self
    {
        if ($this->invoiceEntries->contains($invoiceEntry)) {
            $this->invoiceEntries->removeElement($invoiceEntry);
            // set the owning side to null (unless already changed)
            if ($invoiceEntry->getInvoice() === $this) {
                $invoiceEntry->setInvoice(null);
            }
        }

        return $this;
    }

    public function getRecorded(): ?bool
    {
        return $this->recorded;
    }

    public function setRecorded(bool $recorded): self
    {
        $this->recorded = $recorded;

        return $this;
    }

    public function getCustomerAccountId(): ?int
    {
        return $this->customerAccountId;
    }

    public function setCustomerAccountId(?int $customerAccountId): self
    {
        $this->customerAccountId = $customerAccountId;

        return $this;
    }

    public function getRecordedDate(): ?\DateTimeInterface
    {
        return $this->recordedDate;
    }

    public function setRecordedDate(?\DateTimeInterface $recordedDate): self
    {
        $this->recordedDate = $recordedDate;

        return $this;
    }

    public function getExportedDate(): ?\DateTimeInterface
    {
        return $this->exportedDate;
    }

    public function setExportedDate(?\DateTimeInterface $exportedDate): self
    {
        $this->exportedDate = $exportedDate;

        return $this;
    }

    public function getLockedCustomerKey(): ?string
    {
        return $this->lockedCustomerKey;
    }

    public function setLockedCustomerKey(?string $lockedCustomerKey): self
    {
        $this->lockedCustomerKey = $lockedCustomerKey;

        return $this;
    }

    public function getLockedContactName(): ?string
    {
        return $this->lockedContactName;
    }

    public function setLockedContactName(?string $lockedContactName): self
    {
        $this->lockedContactName = $lockedContactName;

        return $this;
    }

    public function getLockedType(): ?string
    {
        return $this->lockedType;
    }

    public function setLockedType(?string $lockedType): self
    {
        $this->lockedType = $lockedType;

        return $this;
    }

    public function getLockedAccountKey(): ?string
    {
        return $this->lockedAccountKey;
    }

    public function setLockedAccountKey(?string $lockedAccountKey): self
    {
        $this->lockedAccountKey = $lockedAccountKey;

        return $this;
    }

    public function getLockedSalesChannel(): ?string
    {
        return $this->lockedSalesChannel;
    }

    public function setLockedSalesChannel(?string $lockedSalesChannel): self
    {
        $this->lockedSalesChannel = $lockedSalesChannel;

        return $this;
    }

    public function getPaidByAccount(): ?string
    {
        return $this->paidByAccount;
    }

    public function setPaidByAccount(?string $paidByAccount): self
    {
        $this->paidByAccount = $paidByAccount;

        return $this;
    }
}
