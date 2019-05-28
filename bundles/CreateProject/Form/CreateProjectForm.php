<?php

namespace CreateProject\Form;

use App\Service\JiraService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class CreateProjectForm extends AbstractType
{
  private $translator;
  private $jiraService;

  public function __construct(JiraService $jiraService, TranslatorInterface $translator)
  {
    $this->translator = $translator;
    $this->jiraService = $jiraService;
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('project_name', TextType::class, [
        'label' => $this->translator->trans('Project name'),
        'constraints' => [
          new NotBlank(),
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('A name used to identify the project'),
      ])

      ->add('project_key', TextType::class, [
        'label' => $this->translator->trans('Project key'),
        'constraints' => [
          new NotBlank(),
          new Regex([
            'pattern' => '/^[a-zA-Z]+$/',
            'message' => $this->translator->trans('Only letters allowed'),
          ]),
          new Length([
            'min' => 2,
            'minMessage' => $this->translator->trans('Min. 2 letters required'),
            'max' => 7,
            'maxMessage' => $this->translator->trans('Max. 7 letters allowed'),
          ])
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('A key used as prefix on all project issues (2-7 letters without spacing).'),
      ])

      ->add('description', TextareaType::class, [
        'label' => $this->translator->trans('Description'),
        'constraints' => [
          new NotBlank(),
        ],
        'attr' => ['class' => 'form-control', 'required'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('A description of the project')
      ])

      ->add('team', ChoiceType::class, [
        'label' => $this->translator->trans('Team'),
        'choices'  => $this->getTeamChoices($this->translator),
        'constraints' => [
          new NotBlank(),
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('The ITK Team that the project will be associated to')
      ])

      ->add('account', ChoiceType::class, [
        'label' => $this->translator->trans('Account'),
        'choices'  => $this->getAccountChoices($this->translator),
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('The account that the project will be associated to')
      ])

      ->add('new_account', CheckboxType::class, [
        'label' => $this->translator->trans('Create new account'),
        'label_attr' => ['class' => 'form-check-label'],
        'attr' => [
          'class' => 'form-check-input',
          'data-toggle' => 'collapse',
          'data-target' => '.toggle-account-group',
        ],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('This will create a new account in Jira')
      ])

      ->add('new_account_name', TextType::class, [
        'label' => $this->translator->trans('Account name'),
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('A name used to identify the account'),
      ])

      ->add('new_account_customer', ChoiceType::class, [
        'label' => $this->translator->trans('Customer'),
        'choices'  => $this->getCustomerChoices($this->translator),
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('The customer that represents the account')
      ])

      ->add('new_account_contact', TextType::class, [
        'label' => $this->translator->trans('Contact'),
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('Person representing the customer.')
      ])

      ->add('new_customer', CheckboxType::class, [
        'label' => $this->translator->trans('Create new customer'),
        'label_attr' => ['class' => 'form-check-label'],
        'attr' => [
          'class' => 'form-check-input',
          'data-toggle' => 'collapse',
          'data-target' => '.toggle-customer-group',
        ],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('This will create a new customer in Jira')
      ])
      
      ->add('new_customer_name', TextType::class, [
        'label' => $this->translator->trans('Customer name'),
        'attr' => ['class' => 'form-control'],
        'required' => false,
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('A name used to identify the customer'),
      ])

      ->add('save', SubmitType::class, [
        'label' => $this->translator->trans('Create project'),
        'attr' => ['class' => 'btn btn-primary'],
      ]);
  }

  private function getTeamChoices(TranslatorInterface $translator) {
    $projectCategories = $this->jiraService->getAllProjectCategories();
    $teams = [];
    foreach ($projectCategories as $team) {
      $teams[$team->name] = $team->id;
    }
    return $teams;
  }

  private function getAccountChoices(TranslatorInterface $translator) {
    $accounts = $this->jiraService->getAllAccounts();
    $optionalAccounts = [];
    foreach ($accounts as $account) {
      $optionalAccounts[$account->name] = $account->key;
    }
    return $optionalAccounts;
  }

  private function getCustomerChoices(TranslatorInterface $translator) {
    //@todo
    $accounts = $this->jiraService->getAllCustomers();
    $optionalAccounts = [];
    foreach ($accounts as $account) {
      $optionalAccounts[$account->name] = $account->key;
    }
    return $optionalAccounts;
  }
}