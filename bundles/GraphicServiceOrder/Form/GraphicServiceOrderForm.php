<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Form;

use App\Service\JiraService;
use GraphicServiceOrder\Entity\GsOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class GraphicServiceOrderForm extends AbstractType
{
    private $jiraService;

    public function __construct(JiraService $jiraService, array $options = [])
    {
        $this->jiraService = $jiraService;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
    }

    /**
     * Build the form.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *                                                              The form builder
     * @param array                                        $options
     *                                                              Options related to the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('job_title', TextType::class, [
                'label' => 'service_order_form.job_description.title.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_description.title.help',
                'required' => false,
            ])
            ->add('order_lines', CollectionType::class, [
                'entry_type' => GsOrderLineForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => ['label' => false],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'service_order_form.job_description.description.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control', 'required'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_description.description.help',
                'required' => false,
            ])
            ->add('files', FileType::class, [
                'label' => 'service_order_form.job_description.files.label',
                'constraints' => [
                    new All([
                        new Image(),
                        new File([
                            'maxSize' => getenv('FORM_FILE_GS_UPLOAD_SIZE'),
                        ]),
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'jquery_filer' => 'filer_input'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_description.files.help',
                'required' => 0,
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('debitor', TextType::class, [
                'label' => 'service_order_form.job_payment.debitor.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('marketing_account', CheckboxType::class, [
                'label' => 'service_order_form.job_payment.marketing_account.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-check-input'],
                'required' => false,
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_payment.marketing_account.help',
            ])
            ->add('department', TextType::class, [
                'label' => 'service_order_form.job_delivery.department.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'service_order_form.job_delivery.address.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('postal_code', TextType::class, [
                'label' => 'service_order_form.job_delivery.postal_code.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'service_order_form.job_delivery.city.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'label' => 'service_order_form.job_delivery.date.label',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control js-datepicker'],
                'required' => false,
            ])
            ->add('delivery_description', TextareaType::class, [
                'label' => 'service_order_form.job_delivery.delivery_description.label',
                'constraints' => [
                    new NotNull(['groups' => 'base']),
                ],
                'attr' => ['class' => 'form-control', 'required'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_delivery.delivery_description.help',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'service_order_form.save.label',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    /**
     * Perform validation in groups based on choices during submit.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *                                                                     Options related to form
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GsOrder::class,
            'validation_groups' => function (FormInterface $form) {
                return ['Default', 'base'];
            },
        ]);
    }
}
