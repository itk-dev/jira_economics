<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 *
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/projects", name="api_projects")
     */
    public function projectsAction()
    {
        return new JsonResponse(json_decode('
[
  {
    "id": "#id",
    "report_url": "sprint_report/project/10221",
    "sn": "AAPLUS",
    "nm": "Aa+"
  },
  {
    "id": "#id",
    "report_url": "sprint_report/project/10223",
    "sn": "AAKBET",
    "nm": "aakb.dk"
  },
  {
    "id": "#id",
    "report_url": "sprint_report/project/15207",
    "sn": "AAR",
    "nm": "Aarhus-mål 2018-2021"
  }
]
  '));
    }
}
