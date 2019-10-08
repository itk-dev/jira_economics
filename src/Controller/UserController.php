<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use ItkDev\UserManagementBundle\Doctrine\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class UserController extends AdminController implements EventSubscriberInterface
{
    /** @var \ItkDev\UserManagementBundle\Doctrine\UserManager */
    private $userManager;

    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        UserManager $userManager
    ) {
        parent::__construct($translator, $twig);
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_PERSIST => ['prePersist'],
            EasyAdminEvents::PRE_UPDATE => ['preUpdate'],
        ];
    }

    // @see http://symfony.com/doc/current/bundles/EasyAdminBundle/integration/fosuserbundle.html
    public function createNewUserEntity()
    {
        $user = $this->userManager->createUser();

        return $user;
    }

    public function prePersist(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof User)) {
            return;
        }

        $this->prePersistUserEntity($entity);
    }

    public function preUpdate(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof User)) {
            return;
        }

        $this->preUpdateUserEntity($entity);
    }

    /**
     * @param \App\Entity\User $user
     */
    public function prePersistUserEntity(User $user)
    {
        $this->userManager->updateUser($user, false);
        $this->userManager->notifyUserCreated($user, false);
        $this->showInfo('User %user% notified', ['%user%' => $user]);
    }

    public function preUpdateUserEntity(User $user)
    {
        $this->userManager->updateUser($user, false);
    }

    public function notifyUserCreatedAction()
    {
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $user = $easyadmin['item'];

        $this->userManager->notifyUserCreated($user, true);
        $this->showInfo('User %user% notified', ['%user%' => $user]);

        $refererUrl = $this->request->query->get('referer');

        return $refererUrl ? $this->redirect(urldecode($refererUrl))
            : $this->redirectToRoute('easyadmin', ['action' => 'edit', 'entity' => 'User', 'id' => $user->getId()]);
    }

    public function resetPasswordAction()
    {
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $user = $easyadmin['item'];

        $this->userManager->resetPassword($user, true);
        $this->showInfo('Password for %user% reset', ['%user%' => $user]);

        $refererUrl = $this->request->query->get('referer');

        return $refererUrl ? $this->redirect(urldecode($refererUrl))
            : $this->redirectToRoute('easyadmin', ['action' => 'edit', 'entity' => 'User', 'id' => $user->getId()]);
    }
}
