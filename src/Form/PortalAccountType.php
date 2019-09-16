<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form;

use App\Service\HammerService;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PortalAccountType extends ChoiceType
{
    /** @var \App\Service\HammerService */
    private $hammerService;

    public function __construct(
        ChoiceListFactoryInterface $choiceListFactory = null,
        HammerService $hammerService
    ) {
        parent::__construct($choiceListFactory);
        $this->hammerService = $hammerService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $options['choice_loader'] = new CallbackChoiceLoader(function () {
        // Note: We assume that all roles are reachable from ROLE_ADMIN.
        $accounts = $this->hammerService->getAllAccounts();

        $optionalAccounts = [];
        foreach ($accounts as $account) {
          $optionalAccounts[$account->name.' ('.$account->key.')'] = $account->key;
        }
        $optionalAccounts = ['-- None --' => null] + $optionalAccounts;

        return $optionalAccounts;
      });

      parent::buildForm($builder, $options);
    }
}
