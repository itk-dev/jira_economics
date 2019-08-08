<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Expense\Controller;

use App\Service\JiraService;
use App\Service\MenuService;
use Doctrine\ORM\EntityRepository;
use Expense\Entity\Category;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ExpenseController.
 *
 * @Route("/", name="expense_")
 */
class ExpenseController extends AbstractController
{
    /** @var \App\Service\MenuService */
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * @Route("", name="index")
     */
    public function index(JiraService $jiraService)
    {
        $expenses = $jiraService->getExpenses();

        return $this->render('@ExpenseBundle/expense/index.html.twig', [
            'global_menu_items' => $this->menuService->getGlobalMenuItems(),
            'expenses' => $expenses,
        ]);
    }

    /**
     * @Route("/new", name="new")
     */
    public function new(Request $request, TranslatorInterface $translator, JiraService $jiraService)
    {
        $projects = $jiraService->getProjects();

        // Get project and issue from query string.
        $selectedProject = null;
        $selectedIssue = null;

        if (null !== $projectId = $request->get('project')) {
            // Find project by id or key.
            $selected = array_filter(
                $projects,
                function ($project) use ($projectId) {
                    return $projectId === $project->id || $projectId === $project->key;
                }
            );
            $selectedProject = reset($selected);

            if (null !== $selectedProject) {
                if (null !== $issueId = $request->get('issue')) {
                    try {
                        $selectedIssue = $jiraService->getIssue($issueId);
                        // Check that the selected issue belongs to the selected project.
                        if (!isset($selectedIssue->fields->project->id, $selectedProject->id)
                            || $selectedIssue->fields->project->id !== $selectedProject->id) {
                            $selectedIssue = null;
                        }
                    } catch (ClientException $exception) {
                    }
                }
            }
        }

        $form = $this->createFormBuilder([])
            ->add('project', ChoiceType::class, [
                'label' => 'expense.new.project',
                'placeholder' => 'expense.new.project.placeholder',
                'choices' => $projects,
                'choice_value' => 'key',
                'choice_label' => function ($project) use ($translator) {
                    return sprintf('%s (%s)', $project->name, $project->key);
                },
                'data' => $selectedProject,
            ])
            ->add('issue_key', TextType::class, [
                'label' => 'expense.new.issue',
                'data' => $selectedIssue ? $selectedIssue->key : null,
                'attr' => [
                    'data-issue' => $selectedIssue ? json_encode([
                        'id' => $selectedIssue->id,
                        'key' => $selectedIssue->key,
                        'summary' => $selectedIssue->fields->summary,
                    ]) : null,
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'expense.new.category',
                'placeholder' => 'expense.new.category.placeholder',
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.unitPrice > 0')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => function (Category $category) use ($translator) {
                    return $translator->trans('expense.category.new.label_format', [
                        '%name%' => $category->getName(),
                        '%unit_price%' => money_format('%i', $category->getUnitPrice()),
                    ]);
                },
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'expense.new.quantity',
                'attr' => [
                    'placeholder' => 'expense.new.quantity.placeholder',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'expense.new.description',
                'attr' => [
                    'placeholder' => 'expense.new.description.placeholder',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'expense.new.submit',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $project = $data['project'];
                $issue = $jiraService->getIssue($data['issue_key']);
                $data += [
                    'scope_type' => 'ISSUE',
                    'scope_id' => $issue->id,
                ];

                $expense = $jiraService->createExpense($data);

                $this->addFlash(
                    'raw-success',
                    $translator->trans('expense.created.success', [
                        '%project_key%' => $project->key,
                        '%project_name%' => $project->name,
                        '%issue_key%' => $issue->key,
                        '%issue_url%' => $jiraService->getIssueUrl($issue),
                    ])
                );

                return $this->redirectToRoute('expense_new', [
                    'project' => $data['project']->key,
                    'issue' => $data['issue_key'],
                ]);
            } catch (\Exception $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        $project_issues_url = str_replace(
            [urlencode('{'), urlencode('}')],
            ['{', '}'],
            $this->generateUrl('expense_project_issues', ['project' => '{project}'])
        );

        return $this->render('@ExpenseBundle/expense/new.html.twig', [
            'global_menu_items' => $this->menuService->getGlobalMenuItems(),
            'form' => $form->createView(),
            'project_issues_url' => $project_issues_url,
        ]);
    }

    /**
     * Get project issues. Use query parameter "search" to search the issues ("like" query).
     *
     * @Route("/project/{project}/issues", name="project_issues", methods={"GET"})
     */
    public function getProjectIssues($project, Request $request, JiraService $jiraService)
    {
        $query = $request->get('query') ?? $request->get('search') ?? $request->get('term') ?? $project;
        $result = $jiraService->issuePicker($project, $query ?? '');

        if (!empty($result->sections)) {
            $sections = array_filter($result->sections, function ($section) {
                return !empty($section->issues);
            });
            foreach ($sections as $section) {
                if ('cs' === $section->id) {
                    return new JsonResponse($section);
                }
            }
        }

        return new JsonResponse(['issues' => []]);
    }
}
