<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Twig;

use App\Service\ContextService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private const PATH_JIRA = 'jira';
    private const PATH_PORTAL = 'jira';

    /** @var \App\Service\ContextService */
    private $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('jira_path', [$this, 'getJiraPath']),
            new TwigFunction('template_path', [$this, 'getTemplatePath']),
        ];
    }

    public function getJiraPath(string $path = null)
    {
        return self::PATH_JIRA.'/'.$path;
    }

    public function getTemplatePath(string $path = null)
    {
        $prefix = $this->getTemplatePathPrefix();

        return $prefix.'/'.$path;
    }

    private function getTemplatePathPrefix()
    {
        switch ($this->contextService->getContext()) {
            case ContextService::JIRA:
                return self::PATH_JIRA;
        }

        return self::PATH_PORTAL;
    }
}
