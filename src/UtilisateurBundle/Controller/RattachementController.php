<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 13/02/2019
 * Time: 09:50
 */

namespace UtilisateurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RattachementController extends Controller
{
    public function listeAction(Request $request, $json)
    {
        $rattachements = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getManagerAndSuperviseur();

        if ($json == 1) {
            return new JsonResponse($rattachements);
        } else {
            return $this->render('@Utilisateur/Rattachement/liste.html.twig', array(
                'rattachements' => $rattachements,
            ));
        }
    }
}