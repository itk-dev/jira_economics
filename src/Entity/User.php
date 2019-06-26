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

/**
 * @ORM\Entity()
 * @ORM\Table(name="fos_user")
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
     * @ORM\Column(name="jira_id", type="string", length=255, nullable=true)
     */
    private $jiraId;

    private $jiraAccessToken;

    /**
     * @ORM\Column(type="json")
     */
    private $portals = [];

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }

    /**
     * @return mixed
     */
    public function getJiraId()
    {
        return $this->jiraId;
    }

    /**
     * @param mixed $jiraId
     *
     * @return User
     */
    public function setJiraId($jiraId)
    {
        $this->jiraId = $jiraId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJiraAccessToken()
    {
        return $this->jiraAccessToken;
    }

    /**
     * @param mixed $jiraAccessToken
     *
     * @return User
     */
    public function setJiraAccessToken($jiraAccessToken)
    {
        $this->jiraAccessToken = $jiraAccessToken;

        return $this;
    }

    public function getPortals(): ?array
    {
        return $this->portals;
    }

    public function setPortals(array $portals): self
    {
        $this->portals = $portals;

        return $this;
    }
}
