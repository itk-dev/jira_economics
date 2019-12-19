<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Security;

use App\Service\AppService;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class JiraAppVoter extends Voter
{
    /** @var \App\Service\AppService */
    private $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    protected function supports($attribute, $subject)
    {
        return 0 === strpos($attribute, 'JIRA_APP:');
    }

    protected function voteOnAttribute(
        $attribute,
        $subject,
        TokenInterface $token
    ) {
        $user = $token->getUser();

        if (!$user instanceof OAuthUser) {
            // the user must be logged in; if not, deny access
            return false;
        }

        $app = preg_replace('/^JIRA_APP:/', '', $attribute);

        return $this->appService->checkAccessToApp($user, $app);
    }
}
