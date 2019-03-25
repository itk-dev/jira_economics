<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\InvoiceEntry", mappedBy="invoice", orphanRemoval=true)
     */
    private $invoiceEntries;

    public function __construct()
    {
        $this->invoiceEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
