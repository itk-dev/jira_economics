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
 * @ORM\Entity(repositoryClass="Billing\Repository\InvoiceEntryRepository")
 */
class InvoiceEntry extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Billing\Entity\Invoice", inversedBy="invoiceEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invoice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $account;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $product;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $amount;

    /**
     * @ORM\OneToMany(targetEntity="Billing\Entity\Worklog", mappedBy="invoiceEntry", orphanRemoval=true)
     */
    private $worklogs;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $entryType;

    /**
     * @ORM\OneToMany(targetEntity="Billing\Entity\Expense", mappedBy="invoiceEntry", orphanRemoval=true)
     */
    private $expenses;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $materialNumber;

    public function __construct()
    {
        $this->worklogs = new ArrayCollection();
        $this->expenses = new ArrayCollection();
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAccount(): ?string
    {
        return $this->account;
    }

    public function setAccount(string $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(?string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Collection|Worklog[]
     */
    public function getWorklogs(): Collection
    {
        return $this->worklogs;
    }

    public function addWorklog(Worklog $worklog): self
    {
        if (!$this->worklogs->contains($worklog)) {
            $this->worklogs[] = $worklog;
            $worklog->setInvoiceEntry($this);
        }

        return $this;
    }

    public function removeWorklog(Worklog $worklog): self
    {
        if ($this->worklogs->contains($worklog)) {
            $this->worklogs->removeElement($worklog);
            // set the owning side to null (unless already changed)
            if ($worklog->getInvoiceEntry() === $this) {
                $worklog->setInvoiceEntry(null);
            }
        }

        return $this;
    }

    public function getEntryType(): ?string
    {
        return $this->entryType;
    }

    public function setEntryType(string $entryType): self
    {
        $this->entryType = $entryType;

        return $this;
    }

    /**
     * @return Collection|Expense[]
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setInvoiceEntry($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->contains($expense)) {
            $this->expenses->removeElement($expense);
            // set the owning side to null (unless already changed)
            if ($expense->getInvoiceEntry() === $this) {
                $expense->setInvoiceEntry(null);
            }
        }

        return $this;
    }

    public function getMaterialNumber(): ?string
    {
        return $this->materialNumber;
    }

    public function setMaterialNumber(?string $materialNumber): self
    {
        $this->materialNumber = $materialNumber;

        return $this;
    }
}
