<?php

namespace GraphicServiceBilling\Service;

use App\Service\JiraService;

class GraphicServiceBillingService extends JiraService
{
    public function createExport() {
        $this->getProjectWorklogs('IGSTP');
    }
}
