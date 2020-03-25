<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 25/10/2019
 * Time: 10:30
 */

namespace UtilisateurBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChefSuperieurController extends Controller
{
    public function listeAction(Request $request)
    {
        $orgId = $request->query->get('posteId');
        $chefs = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getChefSuperieur($orgId);
        return $this->render('@Utilisateur/Rattachement/liste.html.twig', array(
            'rattachements' => $chefs,
        ));
    }

}