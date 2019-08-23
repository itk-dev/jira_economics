<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Controller;

use App\Service\JiraService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Billing\Service\BillingService;

/**
 * Class ApiController.
 *
 * @Route("/jira_api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/project/{jiraProjectId}", name="api_project")
     * defaults={"jiraProjectId"="...."})
     */
    public function projectAction(
        BillingService $billingService,
        Request $request
    ) {
        $jiraProjectId = $request->get('jiraProjectId');
        $project = $billingService->getJiraProject($jiraProjectId);

        return new JsonResponse([
            'jiraId' => $project->getJiraId(),
            'jiraKey' => $project->getJiraKey(),
            'name' => $project->getName(),
            'url' => $project->getUrl(),
            'avatarUrl' => $project->getAvatarUrl(),
        ]);
    }

    /**
     * @Route("/projects", name="api_projects")
     */
    public function projectsAction(BillingService $billingService)
    {
        return new JsonResponse($billingService->getProjects());
    }

    /**
     * @Route("/invoice/{invoiceId}", name="api_invoice_get", methods={"GET"})
     * defaults={"invoiceId"="...."})
     */
    public function invoiceGetAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceId = $request->get('invoiceId');
        $result = $billingService->getInvoice($invoiceId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice", name="api_invoice_post", methods={"POST"})
     */
    public function invoicePostAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceData = json_decode($request->getContent(), true);
        $result = $billingService->postInvoice($invoiceData);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice/{invoiceId}", name="api_invoice_put", methods={"PUT"})
     * defaults={"invoiceId"="...."})
     */
    public function invoicePutAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceData = json_decode($request->getContent(), true);
        $result = $billingService->putInvoice($invoiceData);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice/{invoiceId}", name="api_invoice_delete", methods={"DELETE"})
     * defaults={"invoiceId"="...."})
     */
    public function invoiceDeleteAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceId = $request->get('invoiceId');
        $result = $billingService->deleteInvoice($invoiceId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoices/{jiraProjectId}", name="api_invoices")
     * defaults={"jiraProjectId"="...."})
     */
    public function invoicesAction(
        BillingService $billingService,
        Request $request
    ) {
        $jiraProjectId = $request->get('jiraProjectId');
        $result = $billingService->getInvoices($jiraProjectId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoices_all", name="api_invoices_all")
     */
    public function allInvoicesAction(
        BillingService $billingService,
        Request $request
    ) {
        $result = $billingService->getAllInvoices();

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry_get", methods={"GET"})
     * defaults={"invoiceEntryId"="...."})
     */
    public function invoiceEntryGetAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceEntryId = $request->get('invoiceEntryId');
        $result = $billingService->getInvoiceEntry($invoiceEntryId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry", name="api_invoice_entry_post", methods={"POST"})
     */
    public function invoiceEntryPostAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceEntryData = json_decode($request->getContent(), true);
        $result = $billingService->postInvoiceEntry($invoiceEntryData);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry_put", methods={"PUT"})
     * defaults={"invoiceEntryId"="...."})
     */
    public function invoiceEntryPutAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceEntryData = json_decode($request->getContent(), true);
        $result = $billingService->putInvoiceEntry($invoiceEntryData);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry_delete", methods={"DELETE"})
     * defaults={"invoiceEntryId"="...."})
     */
    public function invoiceEntryDeleteAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceEntryId = $request->get('invoiceEntryId');
        $result = $billingService->deleteInvoiceEntry($invoiceEntryId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entries/{invoiceId}", name="api_invoice_entries")
     * defaults={"invoiceId"="...."})
     */
    public function invoiceEntriesAction(
        BillingService $billingService,
        Request $request
    ) {
        $invoiceId = $request->get('invoiceId');
        $result = $billingService->getInvoiceEntries($invoiceId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entries_all", name="api_invoice_entries_all")
     */
    public function allInvoiceEntriesAction(
        BillingService $billingService,
        Request $request
    ) {
        $result = $billingService->getAllInvoiceEntries();

        return new JsonResponse($result);
    }

    /**
     * @Route("/to_accounts", name="api_to_accounts", methods={"GET"})
     *
     * @param $boundToAccounts
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function toAccounts($boundToAccounts)
    {
        return new JsonResponse($boundToAccounts);
    }

    /**
     * @Route("/current_user", name="api_current_user")
     */
    public function currentUserAction(JiraService $jiraService)
    {
        return new JsonResponse($jiraService->getCurrentUser());
    }

    /**
     * @Route("/jira_issues/{jiraProjectId}", name="api_jira_issues")
     * defaults={"jiraProjectId"="...."})
     */
    public function jiraIssuesAction(
        BillingService $billingService,
        Request $request
    ) {
        $jiraProjectId = $request->get('jiraProjectId');
        $result = $billingService->getJiraIssues($jiraProjectId);

        return new JsonResponse($result);
    }

    /**
     * @Route("/account/project/{projectId}", name="get_accounts_by_project_id")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Billing\Service\BillingService           $billingService
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAccountsByProjectId(
        Request $request,
        BillingService $billingService,
        $projectId
    ) {
        $accountIds = $billingService->getAccountIdsByProject($projectId);
        $accounts = [];
        foreach ($accountIds as $accountId) {
            $accounts[$accountId] = $billingService->getAccount($accountId);
            $accounts[$accountId]->defaultPrice = $billingService->getAccountDefaultPrice($accountId);
        }

        return new JsonResponse($accounts);
    }
}
