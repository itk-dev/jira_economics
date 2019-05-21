<?php

namespace CreateProject\Form;

use App\Service\JiraService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
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
        'help' => $this->translator->trans('The ITK Team that the projected will be associated to')
      ])
      ->add('account', ChoiceType::class, [
        'label' => $this->translator->trans('Account'),
        'choices'  => $this->getAccountChoices($this->translator),
        'constraints' => [
          new NotBlank(),
        ],
        'attr' => ['class' => 'form-control'],
        'help_attr' => ['class' => 'form-text text-muted'],
        'help' => $this->translator->trans('The customer that the projected will be associated to.')
      ])

      ->add('save', SubmitType::class, [
        'label' => $this->translator->trans('Create project'),
        'attr' => ['class' => 'btn btn-primary'],
      ]);
  }

  public function getTeamChoices(TranslatorInterface $translator) {
    $projectCategories = $this->jiraService->getAllProjectCategories();
    $teams = [];
    foreach ($projectCategories as $team) {
      $teams[$team->name] = $team->id;
    }
    return $teams;
  }

  public function getAccountChoices(TranslatorInterface $translator) {
    $accounts = $this->jiraService->getAllAccounts();
    $optionalAccounts = [];
    foreach ($accounts as $account) {
      $optionalAccounts[$account->name] = $account->key;
    }
    return $optionalAccounts;
  }
}