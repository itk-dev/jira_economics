<?php

namespace Billing\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Billing\Repository\InvoiceRepository")
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
     * @ORM\Column(type="datetime")
     */
    private $created;

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

    public function getRecorded(): ?bool
    {
        return $this->recorded;
    }

    public function setRecorded(bool $recorded): self
    {
        $this->recorded = $recorded;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }
}
