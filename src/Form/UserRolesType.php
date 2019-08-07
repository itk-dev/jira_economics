<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form;

use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserRolesType extends ChoiceType
{
    /** @var \Symfony\Component\Security\Core\Role\RoleHierarchyInterface */
    private $roleHierarchy;

    public function __construct(
        ChoiceListFactoryInterface $choiceListFactory = null,
        RoleHierarchyInterface $roleHierarchy
    ) {
        parent::__construct($choiceListFactory);
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['choice_loader'] = new CallbackChoiceLoader(function () {
            $roles = $this->roleHierarchy->getReachableRoleNames(['ROLE_ADMIN']);

            return array_combine($roles, $roles);
        });

        parent::buildForm($builder, $options);
    }
}
