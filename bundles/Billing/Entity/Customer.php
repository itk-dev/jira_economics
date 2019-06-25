<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Billing\Repository\CustomerRepository")
 */
class Customer
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
     * @ORM\Column(type="string", length=255)
     */
    private $att;

    /**
     * @ORM\Column(type="integer")
     */
    private $CVR;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $EAN;

    /**
     * @ORM\Column(type="integer")
     */
    private $debtor;

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

    public function getAtt(): ?string
    {
        return $this->att;
    }

    public function setAtt(string $att): self
    {
        $this->att = $att;

        return $this;
    }

    public function getCVR(): ?int
    {
        return $this->CVR;
    }

    public function setCVR(int $CVR): self
    {
        $this->CVR = $CVR;

        return $this;
    }

    public function getEAN(): ?string
    {
        return $this->EAN;
    }

    public function setEAN(string $EAN): self
    {
        $this->EAN = $EAN;

        return $this;
    }

    public function getDebtor(): ?int
    {
        return $this->debtor;
    }

    public function setDebtor(int $debtor): self
    {
        $this->debtor = $debtor;

        return $this;
    }
}
