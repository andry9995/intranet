<?php

namespace UtilisateurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function listeAction(Request $request, $json)
    {
        $roles = $this->getDoctrine()
            ->getRepository('AppBundle:AccesOperateur')
            ->getAllAcessOperateur();

        if ($json == 1) {
            return new JsonResponse($roles);
        } else {
            return $this->render('@Utilisateur/Role/liste.html.twig', array(
                'roles' => $roles,
            ));
        }
    }
}
