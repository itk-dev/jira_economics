<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Expense\Controller\Admin;

use App\Service\JiraService;
use App\Service\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Expense\Entity\Category;
use Expense\Form\CategoryType;
use Expense\Repository\ExpenseCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/expense/category", name="expense_admin_category_")
 */
class ExpenseCategoryController extends AbstractController
{
    /** @var \App\Service\JiraService */
    private $jiraService;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /** @var \App\Service\MenuService */
    private $menuService;

    public function __construct(JiraService $jiraService, TranslatorInterface $translator, MenuService $menuService)
    {
        $this->jiraService = $jiraService;
        $this->translator = $translator;
        $this->menuService = $menuService;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(ExpenseCategoryRepository $categoryRepository): Response
    {
        return $this->render('@ExpenseBundle/admin/expense/category/index.html.twig', [
            'global_menu_items' => $this->menuService->getGlobalMenuItems(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/refresh", name="refresh", methods={"POST"})
     */
    public function refresh(ExpenseCategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $categories = $this->jiraService->getExpenseCategories();

        foreach ($categories as $category) {
            $existingCategory = $categoryRepository->find($category->id);
            if (null === $existingCategory) {
                $existingCategory = (new Category())
                    ->setId($category->id)
                    ->setUnitPrice(0);
            }
            $existingCategory->setName($category->name);
            $entityManager->persist($existingCategory);
            $entityManager->flush();
        }

        $this->addFlash('success', $this->translator->trans('expense.category.successfully_refreshed'));

        return $this->redirectToRoute('expense_admin_category_index');
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingCategory = $this->jiraService->getExpenseCategoryByName($category->getName());
            if (null !== $existingCategory) {
                $form->addError(new FormError($this->translator->trans('expense.category.name_already_used', ['%name%' => $category->getName()])));

                return $this->render('@ExpenseBundle/admin/expense/category/new.html.twig', [
                    'category' => $category,
                    'form' => $form->createView(),
                ]);
            }

            $jiraCategory = $this->jiraService->createExpenseCategory($category);
            $category->setId($jiraCategory->id);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('expense_admin_category_index');
        }

        return $this->render('@ExpenseBundle/admin/expense/category/new.html.twig', [
            'global_menu_items' => $this->menuService->getGlobalMenuItems(),
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Category $category): Response
    {
        return $this->render('@ExpenseBundle/admin/expense/category/show.html.twig', [
            'global_menu_items' => $this->menuService->getGlobalMenuItems(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingCategory = $this->jiraService->getExpenseCategoryByName($category->getName());
            if (null !== $existingCategory && $existingCategory->id !== $category->getId()) {
                $form->addError(new FormError($this->translator->trans('expense.category.name_already_used', ['%name%' => $category->getName()])));

                return $this->render('@ExpenseBundle/admin/expense/category/edit.html.twig', [
                    'category' => $category,
                    'form' => $form->createView(),
                ]);
            }

            $this->jiraService->updateExpenseCategory($category);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('expense_admin_category_index');
        }

        return $this->render('@ExpenseBundle/admin/expense/category/edit.html.twig', [
            'global_menu_items' => $this->menuService->getGlobalMenuItems(),
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $this->jiraService->deleteExpenseCategory($category);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('expense_admin_category_index');
    }
}
