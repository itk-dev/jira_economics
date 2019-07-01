<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace CreateProject\Form;

use App\Service\JiraService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateProjectForm extends AbstractType
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
      ->add('project_name', TextType::class, [
        'label' => 'create_project_form.project_name.label',
        'constraints' => [
          new NotNull(['groups' => 'base']),
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.project_name.help',
        'required' => false,
      ])

      ->add('project_key', TextType::class, [
        'label' => 'create_project_form.project_key.label',
        'constraints' => [
          new NotNull(['groups' => 'base']),
          new Regex([
            'pattern' => '/^[a-zA-Z]+$/',
            'message' => 'create_project_form.project_key.constraint.regex',
          ]),
          new Length([
            'min' => 2,
            'minMessage' => 'create_project_form.project_key.constraint.min',
            'max' => 7,
            'maxMessage' => 'create_project_form.project_key.constraint.max',
          ]),
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.project_key.help',
        'required' => false,
      ])

      ->add('description', TextareaType::class, [
        'label' => 'create_project_form.description.label',
        'constraints' => [
          new NotNull(['groups' => 'base']),
        ],
        'attr' => ['class' => 'form-control', 'required'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.description.help',
        'required' => false,
      ])

      ->add('team', ChoiceType::class, [
        'label' => 'create_project_form.team.label',
        'choices' => $this->getTeamChoices(),
        'choice_translation_domain' => false,
        'constraints' => [
          new NotNull([
            'message' => 'create_project_form.team.constraint.not_blank',
            'groups' => 'base',
          ]),
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.team.help',
      ])

      ->add('account', ChoiceType::class, [
        'label' => 'create_project_form.account.label',
        'choices' => $this->getAccountChoices(),
        'choice_translation_domain' => false,
        'attr' => ['class' => 'form-control js-select2'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.account.help',
        'constraints' => [
          new NotNull([
            'groups' => 'select_account',
            'message' => 'create_project_form.account.constraint.not_null',
          ]),
        ],
      ])

      ->add('new_account', CheckboxType::class, [
        'label' => 'create_project_form.create_new_account.label',
        'label_attr' => ['class' => 'form-check-label'],
        'attr' => [
          'class' => 'form-check-input',
          'data-toggle' => 'collapse',
          'data-target' => '.toggle-account-group',
        ],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.create_new_account.help',
        'required' => false,
        'validation_groups' => ['account'],
      ])

      ->add('new_account_name', TextType::class, [
        'label' => 'create_project_form.new_account_name.label',
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_account_name.help',
        'constraints' => [
          new NotNull(['groups' => 'account']),
        ],
      ])

      ->add('new_account_key', TextType::class, [
        'label' => 'create_project_form.new_account_key.label',
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_account_key.help',
        'constraints' => [
          new NotNull(['groups' => 'account']),
        ],
      ])

      ->add('new_account_contact', TextType::class, [
        'label' => 'create_project_form.new_account_contact.label',
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_account_contact.help',
        'constraints' => [
          new NotNull(['groups' => 'account']),
        ],
      ])

      ->add('new_account_customer', ChoiceType::class, [
        'label' => 'create_project_form.new_account_customer.label',
        'choices' => $this->getCustomerChoices(),
        'choice_translation_domain' => false,
        'required' => false,
        'attr' => ['class' => 'form-control js-select2'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_account_customer.help',
        'constraints' => [
          new NotNull([
            'groups' => 'select_customer',
            'message' => 'create_project_form.new_account_customer.constraint.not_null',
          ]),
        ],
      ])

      ->add('new_customer', CheckboxType::class, [
        'label' => 'create_project_form.new_customer.label',
        'label_attr' => ['class' => 'form-check-label'],
        'attr' => [
          'class' => 'form-check-input',
          'data-toggle' => 'collapse',
          'data-target' => '.toggle-customer-group',
        ],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_customer.help',
        'validation_groups' => ['customer'],
      ])

      ->add('new_customer_name', TextType::class, [
        'label' => 'create_project_form.new_customer_name.label',
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_customer_name.help',
        'constraints' => [
          new NotNull(['groups' => 'customer']),
        ],
      ])

      ->add('new_customer_key', TextType::class, [
        'label' => 'create_project_form.new_customer_key.label',
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => 'create_project_form.new_customer_key.help',
        'constraints' => [
          new NotNull(['groups' => 'customer']),
          new Regex([
            'pattern' => '/^([0-9]{4}|[0-9]{13})$/',
            'message' => 'create_project_form.new_customer_key.constraint.regex',
            'groups' => 'customer',
          ]),
        ],
      ])

      ->add('save', SubmitType::class, [
        'label' => 'create_project_form.save.label',
        'attr' => ['class' => 'btn btn-primary'],
      ]);
    }

    /**
     * Generate an array of teams from project categories.
     *
     * @return array
     *               A list of teams and their id
     */
    private function getTeamChoices()
    {
        $projectCategories = $this->jiraService->getAllProjectCategories();
        $teams = [];
        foreach ($projectCategories as $team) {
            $teams[$team->name] = $team->id;
        }
        $teams = ['-- Select --' => null] + $teams;

        return $teams;
    }

    /**
     * Generate an array of accounts from tempo accounts.
     *
     * @return array
     *               A list of tempo accounts and their key
     */
    private function getAccountChoices()
    {
        $accounts = $this->jiraService->getAllAccounts();
        $optionalAccounts = [];
        foreach ($accounts as $account) {
            $optionalAccounts[$account->name.' ('.$account->key.')'] = $account->key;
        }
        $optionalAccounts = ['-- Select --' => null] + $optionalAccounts;

        return $optionalAccounts;
    }

    /**
     * Generate an array of customers from tempo customers.
     *
     * @return array
     *               A list of tempo customers and their key
     */
    private function getCustomerChoices()
    {
        $customers = $this->jiraService->getAllCustomers();
        $optionalCustomerChoices = [];
        foreach ($customers  as $customer) {
            $optionalCustomerChoices[$customer->name.' ('.$customer->key.')'] = $customer->key;
        }
        $optionalCustomerChoices = ['-- Select --' => null] + $optionalCustomerChoices;

        return $optionalCustomerChoices;
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
      'validation_groups' => function (FormInterface $form) {
          $data = $form->getData();
          if (true === $data['new_account']) {
              if (true === $data['new_customer']) {
                  return ['Default', 'base', 'account', 'customer'];
              }

              return ['Default', 'base', 'account', 'select_customer'];
          }

          return ['Default', 'base', 'select_account'];
      },
    ]);
    }
}
