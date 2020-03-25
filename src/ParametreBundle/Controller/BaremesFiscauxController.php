<?php

namespace ParametreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaremesFiscauxController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Parametre/BaremesFiscaux/index.html.twig');
    }
}
