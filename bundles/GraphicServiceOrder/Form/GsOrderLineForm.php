<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GsOrderLineForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class, [
                'label' => 'service_order_form.order_line.amount.label',
                'attr' => ['class' => 'form-control'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.order_line.amount.help',
                'required' => false,
                'html5' => true,
            ])
            ->add('type', TextType::class, [
                'label' => 'service_order_form.order_line.type.label',
                'attr' => ['class' => 'form-control'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.order_line.type.help',
                'required' => false,
            ]);
    }
}
