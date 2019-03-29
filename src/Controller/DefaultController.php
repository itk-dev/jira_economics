<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    //TODO find a nicer way to exclude routes that shouldn't be handled by React
    /**
     * @Route("/{reactRouting}", name="index", defaults={"reactRouting": null}, requirements={"reactRouting"="^(?!api|login|connect|favicon).+"})
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }
}
