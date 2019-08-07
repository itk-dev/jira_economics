<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form;

use App\Service\AppService;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PortalAppsType extends ChoiceType
{
    /** @var \App\Service\AppService */
    private $appService;

    public function __construct(
        ChoiceListFactoryInterface $choiceListFactory = null,
        AppService $appService
    ) {
        parent::__construct($choiceListFactory);
        $this->appService = $appService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['choice_loader'] = new CallbackChoiceLoader(function () {
            return array_flip(array_map(function ($app) {
                return $app['title'];
            }, $this->appService->getApps()));
        });

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'portals' => [],
        ]);
    }
}
