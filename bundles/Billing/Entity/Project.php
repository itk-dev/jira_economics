<?php

namespace Billing\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Billing\Repository\ProjectRepository")
 * @UniqueEntity("jiraId")
 */
class Project
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
     * @ORM\OneToMany(targetEntity="Billing\Entity\Invoice", mappedBy="project", orphanRemoval=true)
     */
    private $invoices;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $jiraKey;

    /**
     * @ORM\Column(type="integer")
     */
    private $jiraId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatarUrl;

    /**
     * @ORM\OneToMany(targetEntity="Billing\Entity\JiraIssue", mappedBy="project")
     */
    private $jiraIssues;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
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

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setProject($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getProject() === $this) {
                $invoice->setProject(null);
            }
        }

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getJiraKey(): ?string
    {
        return $this->jiraKey;
    }

    public function setJiraKey(string $jiraKey): self
    {
        $this->jiraKey = $jiraKey;

        return $this;
    }

    public function getJiraId(): ?int
    {
        return $this->jiraId;
    }

    public function setJiraId(int $jiraId): self
    {
        $this->jiraId = $jiraId;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

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
            $jiraIssue->setProject($this);
        }

        return $this;
    }

    public function removeJiraIssue(JiraIssue $jiraIssue): self
    {
        if ($this->jiraIssues->contains($jiraIssue)) {
            $this->jiraIssues->removeElement($jiraIssue);
            // set the owning side to null (unless already changed)
            if ($jiraIssue->getProject() === $this) {
                $jiraIssue->setProject(null);
            }
        }

        return $this;
    }
}
