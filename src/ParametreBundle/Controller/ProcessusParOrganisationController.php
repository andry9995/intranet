<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 20/03/2019
 * Time: 08:07
 */

namespace ParametreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessusParOrganisationController extends Controller
{

    function saveRelationAction(Request $request)
    {
        $tableaux = $request->request->get('tableau');
        $proc = $this->getDoctrine()
            ->getRepository('AppBundle:ProcessusParOrganisation')
            ->saveRelation($tableaux);
        return new Response(1);
    }
}