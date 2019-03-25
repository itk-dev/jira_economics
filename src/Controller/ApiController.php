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
     * @Route("/invoice/{jiraProjectId}", name="api_invoice")
     * defaults={"jiraProjectId"="...."})
     */
    public function invoiceAction(JiraService $jiraService, Request $request)
    {
        $invoiceId = $request->get('jiraProjectId');
        $result = $jiraService->getInvoice($invoiceId);
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
     * @Route("/invoice_entry/{invoiceEntryId}", name="api_invoice_entry")
     * defaults={"invoiceEntryId"="...."})
    */
    public function invoiceEntryAction(JiraService $jiraService, Request $request)
    {
        $invoiceEntryId = $request->get('invoiceEntryId');
        $result = $jiraService->getInvoiceEntry($invoiceEntryId);
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
}
