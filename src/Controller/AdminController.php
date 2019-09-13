<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AdminController extends EasyAdminController
{
    protected $translator;

    protected $twig;

    public function __construct(TranslatorInterface $translator, Environment $twig)
    {
        $this->translator = $translator;
        $this->twig = $twig;
    }

    protected function showSuccess(string $message, array $parameters = [])
    {
        $this->showMessage('success', $message, $parameters);
    }

    protected function showInfo(string $message, array $parameters = [])
    {
        $this->showMessage('info', $message, $parameters);
    }

    protected function showWarning(string $message, array $parameters = [])
    {
        $this->showMessage('warning', $message, $parameters);
    }

    protected function showError(string $message, array $parameters = [])
    {
        $this->showMessage('error', $message, $parameters);
    }

    protected function showMessage(string $type, string $message, array $parameters = [])
    {
        // If message looks like a twig template filename we render it as a template.
        if (preg_match('/\.(html|txt)\.twig$/', $message)) {
            $message = $this->twig->render($message, $parameters);
        } else {
            $message = $this->translator->trans($message, $parameters);
        }

        $this->addFlash($type, $message);
    }
}
