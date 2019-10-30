<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use GraphicServiceOrder\Entity\Debtor;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="fos_user")
 * @UniqueEntity(fields={"email"})
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="json")
     */
    private $portalApps = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity="GraphicServiceOrder\Entity\Debtor", mappedBy="User")
     */
    private $usedDebtors;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $noDefaultSettings;

    public function __construct()
    {
        parent::__construct();
        $this->usedDebtors = new ArrayCollection();
    }

    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }

    public function getPortalApps(): ?array
    {
        return $this->portalApps;
    }

    public function setPortalApps(array $portalApps): self
    {
        $this->portalApps = $portalApps;

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

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getPhone(): ?string
    {
      return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
      $this->phone = $phone;

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

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): self
    {
        $this->postalCode = $postalCode;

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

    /**
     * @return Collection|Debtor[]
     */
    public function getUsedDebtors(): Collection
    {
        return $this->usedDebtors;
    }

    public function addUsedDebtor(Debtor $usedDebtor): self
    {
        if (!$this->usedDebtors->contains($usedDebtor)) {
            $this->usedDebtors[] = $usedDebtor;
            $usedDebtor->addUser($this);
        }

        return $this;
    }

    public function removeUsedDebtor(Debtor $usedDebtor): self
    {
        if ($this->usedDebtors->contains($usedDebtor)) {
            $this->usedDebtors->removeElement($usedDebtor);
            $usedDebtor->removeUser($this);
        }

        return $this;
    }

    public function getNoDefaultSettings(): ?bool
    {
        return $this->noDefaultSettings;
    }

    public function setNoDefaultSettings(?bool $noDefaultSettings): self
    {
        $this->noDefaultSettings = $noDefaultSettings;

        return $this;
    }
}
