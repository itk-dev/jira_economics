<?php

namespace App\Controller;

use App\Service\JiraService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 *
 * @Route("/jira_api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/project/{jiraProjectId}", name="api_project")
     * defaults={"jiraProjectId"="...."})
     */
    public function projectAction(JiraService $jiraService, Request $request)
    {
        $jiraProjectId = $request->get('jiraProjectId');
        $result = $jiraService->getProject($jiraProjectId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/projects", name="api_projects")
     */
    public function projectsAction(JiraService $jiraService)
    {
        return new JsonResponse($jiraService->getProjects());
    }

    /**
     * @Route("/invoice/{jiraProjectId}", name="api_invoice_get", methods={"GET"})
     * defaults={"jiraProjectId"="...."})
     */
    public function invoiceGetAction(JiraService $jiraService, Request $request)
    {
        $invoiceId = $request->get('jiraProjectId');
        $result = $jiraService->getInvoice($invoiceId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice", name="api_invoice_post", methods={"POST"})
     */
    public function invoicePostAction(JiraService $jiraService, Request $request) {
        $invoiceData = json_decode($request->getContent(), true);
        $result = $jiraService->postInvoice($invoiceData);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice/{jiraProjectId}", name="api_invoice_put", methods={"PUT"})
     * defaults={"jiraProjectId"="...."})
     */
    public function invoicePutAction(JiraService $jiraService, Request $request)
    {
        $invoiceData = json_decode($request->getContent(), true);
        $result = $jiraService->putInvoice($invoiceData);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice/{invoiceId}", name="api_invoice_delete", methods={"DELETE"})
     * defaults={"invoiceId"="...."})
     */
    public function invoiceDeleteAction(JiraService $jiraService, Request $request)
    {
        $invoiceId = $request->get('invoiceId');
        $result = $jiraService->deleteInvoice($invoiceId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoices/{invoiceId}", name="api_invoices")
     * defaults={"invoiceId"="...."})
    */
    public function invoicesAction(JiraService $jiraService, Request $request)
    {
        $jiraProjectId = $request->get('invoiceId');
        $result = $jiraService->getInvoices($jiraProjectId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry_get", methods={"GET"})
     * defaults={"invoiceEntryId"="...."})
    */
    public function invoiceEntryGetAction(JiraService $jiraService, Request $request)
    {
        $invoiceEntryId = $request->get('invoiceEntryId');
        $result = $jiraService->getInvoiceEntry($invoiceEntryId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry", name="api_invoice_entry_post", methods={"POST"})
     */
    public function invoiceEntryPostAction(JiraService $jiraService, Request $request) {
        $invoiceEntryData = json_decode($request->getContent(), true);
        $result = $jiraService->postInvoiceEntry($invoiceEntryData);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry_put", methods={"PUT"})
     * defaults={"invoiceEntryId"="...."})
     */
    public function invoiceEntryPutAction(JiraService $jiraService, Request $request)
    {
        $invoiceEntryData = json_decode($request->getContent(), true);
        $result = $jiraService->putInvoiceEntry($invoiceEntryData);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry_delete", methods={"DELETE"})
     * defaults={"invoiceEntryId"="...."})
     */
    public function invoiceEntryDeleteAction(JiraService $jiraService, Request $request)
    {
        $invoiceEntryId = $request->get('invoiceEntryId');
        $result = $jiraService->deleteInvoiceEntry($invoiceEntryId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_entries/{invoiceId}", name="api_invoice_entries")
     * defaults={"invoiceId"="...."})
    */
    public function invoiceEntriesAction(JiraService $jiraService, Request $request)
    {
        $jiraProjectId = $request->get('invoiceId');
        $result = $jiraService->getInvoiceEntries($jiraProjectId);
        return new JsonResponse($result);
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
    public function jiraIssuesAction(JiraService $jiraService, Request $request)
    {
        $jiraProjectId = $request->get('jiraProjectId');
        $result = $jiraService->getJiraIssues($jiraProjectId);
        return new JsonResponse($result);
    }
}
