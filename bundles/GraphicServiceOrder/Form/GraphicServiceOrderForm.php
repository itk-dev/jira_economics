<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Form;

use App\Service\HammerService;
use GraphicServiceOrder\Entity\GsOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GraphicServiceOrderForm extends AbstractType
{
    private $hammerService;
    private $container;
    private $params;

    public function __construct(HammerService $hammerService, ContainerInterface $container, ParameterBagInterface $params, array $options = [])
    {
        $this->hammerService = $hammerService;
        $this->container = $container;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->params = $params;
    }

    /**
     * Build the form.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder The form builder
     * @param array                                        $options Options related to the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('full_name', TextType::class, [
                'label' => 'service_order_form.full_name.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'help' => 'service_order_form.full_name.help',
                'required' => false,
            ])

            ->add('job_title', TextType::class, [
                'label' => 'service_order_form.job_description.title.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'help' => 'service_order_form.job_description.title.help',
                'required' => false,
            ])
            ->add('order_lines', CollectionType::class, [
                'entry_type' => GsOrderLineForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => ['label' => false],
            ])
            ->add('multi_upload', CollectionType::class, [
                'label' => 'service_order_form.job_description.files.label',
                'entry_type' => FileType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
                'entry_options' => [
                    'label' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => $this->params->get('form_file_gs_upload_size'),
                            'mimeTypes' => $allowed_file_types,
                            'mimeTypesMessage' => 'Please upload a valid file: '.implode(', ', array_keys($allowed_file_types)),
                        ]),
                    ],
                ],
                'help' => 'service_order_form.job_description.files.help',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'service_order_form.job_description.description.label',
                'help' => 'service_order_form.job_description.description.help',
                'required' => false,
            ])
            ->add('debitor', NumberType::class, [
                'label' => 'service_order_form.job_payment.debitor.label',
                'constraints' => [
                    new NotNull([
                        'groups' => 'debitor',
                        'message' => 'service_order_form.job_payment.debitor.constraint.not_null',
                    ]),
                    new Length([
                        'groups' => 'debitor',
                        'min' => 4,
                        'minMessage' => 'service_order_form.job_payment.debitor.constraint.min',
                        'max' => 4,
                        'maxMessage' => 'service_order_form.job_payment.debitor.constraint.max',
                    ]),
                ],
                'required' => false,
            ])
            ->add('marketing_account', CheckboxType::class, [
                'label' => 'service_order_form.job_payment.marketing_account.label',
                'constraints' => [
                    new NotNull(['groups' => 'marketing_account']),
                ],
                'required' => false,
                'help' => 'service_order_form.job_payment.marketing_account.help',
            ])
            ->add('department', TextType::class, [
                'label' => 'service_order_form.job_delivery.department.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'service_order_form.job_delivery.address.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'required' => false,
            ])
            ->add('postal_code', NumberType::class, [
                'label' => 'service_order_form.job_delivery.postal_code.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'service_order_form.job_delivery.city.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'label' => 'service_order_form.job_delivery.date.label',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'required' => false,
            ])
            ->add('delivery_description', TextareaType::class, [
                'label' => 'service_order_form.job_delivery.delivery_description.label',
                'help' => 'service_order_form.job_delivery.delivery_description.help',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'service_order_form.save.label',
                'attr' => ['class' => 'btn-primary'],
            ]);
    }

    /**
     * Perform validation in groups based on choices during submit.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options related to form
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GsOrder::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if (true === $data->getMarketingAccount()) {
                    return ['Default', 'base', 'marketing_account'];
                }

                return ['Default', 'base', 'debitor'];
            },
        ]);
    }
}
