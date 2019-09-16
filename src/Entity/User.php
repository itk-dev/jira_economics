<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
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
    private $UserFullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $UserDepartment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $UserAddress;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $UserPostalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $UserCity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $UserAccount;

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

    public function getUserFullName(): ?string
    {
        return $this->UserFullName;
    }

    public function setUserFullName(?string $UserFullName): self
    {
        $this->UserFullName = $UserFullName;

        return $this;
    }

    public function getUserDepartment(): ?string
    {
        return $this->UserDepartment;
    }

    public function setUserDepartment(?string $UserDepartment): self
    {
        $this->UserDepartment = $UserDepartment;

        return $this;
    }

    public function getUserAddress(): ?string
    {
        return $this->UserAddress;
    }

    public function setUserAddress(?string $UserAddress): self
    {
        $this->UserAddress = $UserAddress;

        return $this;
    }

    public function getUserPostalCode(): ?int
    {
        return $this->UserPostalCode;
    }

    public function setUserPostalCode(?int $UserPostalCode): self
    {
        $this->UserPostalCode = $UserPostalCode;

        return $this;
    }

    public function getUserCity(): ?string
    {
        return $this->UserCity;
    }

    public function setUserCity(?string $UserCity): self
    {
        $this->UserCity = $UserCity;

        return $this;
    }

    public function getUserAccount(): ?string
    {
        return $this->UserAccount;
    }

    public function setUserAccount(?string $UserAccount): self
    {
        $this->UserAccount = $UserAccount;

        return $this;
    }
}
