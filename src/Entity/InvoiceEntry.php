<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceEntryRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice", inversedBy="invoiceEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invoice;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JiraIssue", mappedBy="InvoiceEntryId")
     */
    private $jiraIssues;

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
}
