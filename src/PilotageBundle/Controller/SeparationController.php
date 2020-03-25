<?php

namespace PilotageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SeparationController extends Controller
{
    public function indexAction()
    {
        return $this->render('PilotageBundle:Separation:index.html.twig');
    }

    public function separationCategorieAction()
    {
        return $this->render('PilotageBundle:Separation:separation-categorie.html.twig');
    }
}
