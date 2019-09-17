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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GraphicServiceOrderForm extends AbstractType
{
    private $hammerService;
    private $container;
    private $tokenStorage;

    public function __construct(HammerService $hammerService, ContainerInterface $container, array $options = [], TokenStorageInterface $tokenStorage)
    {
        $this->hammerService = $hammerService;
        $this->container = $container;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Build the form.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder The form builder
     * @param array                                        $options Options related to the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $allowed_file_types = [
            'application/pdf',
            'application/zip',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        // Add file upload endpoint.
        $helper = $this->container->get('oneup_uploader.templating.uploader_helper');
        $endpoint = $helper->endpoint('gsorder');
        $token = $this->tokenStorage->getToken();
        $user = null !== $token ? $token->getUser() : NULL;
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
                'attr' => ['class' => 'form-control', 'required'],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_description.description.help',
                'required' => false,
            ])
      // Using OneupUploaderBundle and ajax for uploading the files, see GsUploadListener and jquery-fileupload-config.js
            ->add('files', FileType::class, [
                'label' => 'service_order_form.job_description.files.label',
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => getenv('FORM_FILE_GS_UPLOAD_SIZE'),
                            'mimeTypes' => $allowed_file_types,
                        ]),
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'data-url' => $endpoint],
                'help_attr' => ['class' => 'form-text text-muted'],
                'help' => 'service_order_form.job_description.files.help',
                'required' => 0,
                'multiple' => true,
            ])
      // Using OneupUploaderBundle and ajax for uploading the files, causes the 'files' field to be empty on submit.
      // We add the uploaded files to a hidden field, to store them until form submit.
            ->add('files_uploaded', HiddenType::class, [
                'mapped' => false,
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
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('marketing_account', CheckboxType::class, [
                'label' => 'service_order_form.job_payment.marketing_account.label',
                'constraints' => [
                    new NotNull(['groups' => 'marketing_account']),
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
