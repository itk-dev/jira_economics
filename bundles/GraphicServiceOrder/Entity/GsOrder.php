<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="GraphicServiceOrder\Repository\GsOrderRepository")
 */
class GsOrder
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
    private $issueKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $orderLines = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $files = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $debitor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $marketingAccount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postalcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $deliveryDescription;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $ownCloudSharedFiles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $orderStatus;

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

    public function getIssueKey(): ?string
    {
        return $this->issueKey;
    }

    public function setIssueKey(string $issueKey): self
    {
        $this->issueKey = $issueKey;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getOrderLines(): ?array
    {
        return $this->orderLines;
    }

    public function setOrderLines(?array $orderLines): self
    {
        $this->orderLines = $orderLines;

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

    public function getFiles(): ?array
    {
        return $this->files;
    }

    public function setFiles(?array $files): self
    {
        $this->files = $files;

        return $this;
    }

    public function getDebitor(): ?int
    {
        return $this->debitor;
    }

    public function setDebitor(?int $debitor): self
    {
        $this->debitor = $debitor;

        return $this;
    }

    public function getMarketingAccount(): ?bool
    {
        return $this->marketingAccount;
    }

    public function setMarketingAccount(?string $marketingAccount): self
    {
        $this->marketingAccount = $marketingAccount;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalcode(): ?int
    {
        return $this->postalcode;
    }

    public function setPostalcode(?int $postalcode): self
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDeliveryDescription(): ?string
    {
        return $this->deliveryDescription;
    }

    public function setDeliveryDescription(?string $deliveryDescription): self
    {
        $this->deliveryDescription = $deliveryDescription;

        return $this;
    }

    public function getOwnCloudSharedFiles(): ?array
    {
        return $this->ownCloudSharedFiles;
    }

    public function setOwnCloudSharedFiles(?array $ownCloudSharedFiles): self
    {
        $this->ownCloudSharedFiles = $ownCloudSharedFiles;

        return $this;
    }

    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(?string $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }
}
