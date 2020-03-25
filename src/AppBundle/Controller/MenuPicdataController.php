<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 25/01/2019
 * Time: 14:18
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MenuPicdataController extends Controller
{
    /**
     * @return Response
     */
    public function utilisateurAction()
    {
        $utilisateurOperateur = $this->getDoctrine()->getRepository('AppBundle:OperateurUtilisateur')
            ->getUtilisateurOperateur($this->getUser());
        if ($utilisateurOperateur)
            return new Response(Boost::boost($utilisateurOperateur->getUtilisateur()->getId()));

        return new Response(0);
    }
}