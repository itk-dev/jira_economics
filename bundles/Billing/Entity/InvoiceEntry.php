<?php

namespace Billing\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Billing\Repository\InvoiceEntryRepository")
 */
class InvoiceEntry
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
     * @ORM\ManyToOne(targetEntity="Billing\Entity\Invoice", inversedBy="invoiceEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invoice;

    /**
     * @ORM\OneToMany(targetEntity="Billing\Entity\JiraIssue", mappedBy="InvoiceEntryId")
     */
    private $jiraIssues;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $account;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $product;

    public function __construct()
    {
        $this->jiraIssues = new ArrayCollection();
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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @return Collection|JiraIssue[]
     */
    public function getJiraIssues(): Collection
    {
        return $this->jiraIssues;
    }

    public function addJiraIssue(JiraIssue $jiraIssue): self
    {
        if (!$this->jiraIssues->contains($jiraIssue)) {
            $this->jiraIssues[] = $jiraIssue;
            $jiraIssue->setInvoiceEntryId($this);
        }

        return $this;
    }

    public function removeJiraIssue(JiraIssue $jiraIssue): self
    {
        if ($this->jiraIssues->contains($jiraIssue)) {
            $this->jiraIssues->removeElement($jiraIssue);
            // set the owning side to null (unless already changed)
            if ($jiraIssue->getInvoiceEntryId() === $this) {
                $jiraIssue->setInvoiceEntryId(null);
            }
        }

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
}
