<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\JiraIssueRepository")
 */
class JiraIssue
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
    private $issueId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $summary;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finished;

    /**
     * @ORM\Column(type="array")
     */
    private $jiraUsers = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $timeSpent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="jiraIssues")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\InvoiceEntry", inversedBy="jiraIssues")
     */
    private $InvoiceEntryId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssueId(): ?int
    {
        return $this->issueId;
    }

    public function setIssueId(int $issueId): self
    {
        $this->issueId = $issueId;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

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

    public function getFinished(): ?\DateTimeInterface
    {
        return $this->finished;
    }

    public function setFinished(?\DateTimeInterface $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getJiraUsers(): ?array
    {
        return $this->jiraUsers;
    }

    public function setJiraUsers(array $jiraUsers): self
    {
        $this->jiraUsers = $jiraUsers;

        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(int $timeSpent): self
    {
        $this->timeSpent = $timeSpent;

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

    public function getInvoiceEntryId(): ?InvoiceEntry
    {
        return $this->InvoiceEntryId;
    }

    public function setInvoiceEntryId(?InvoiceEntry $InvoiceEntryId): self
    {
        $this->InvoiceEntryId = $InvoiceEntryId;

        return $this;
    }
}
