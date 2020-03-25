<?php

namespace ParametreBundle\Controller;

use AppBundle\Entity\Departement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Entite;

class ParametreController extends Controller
{
    /**
     * Index Parametre Bundle
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('ParametreBundle:Parametre:index.html.twig');
    }



    // GESTION DES GAMMES
    public function gammeAction()
    {
        $menus = $this->getDoctrine()
                      ->getRepository('AppBundle:MenuIntranet')
                      ->findBy(array(
                          'menuIntranet' => 1
                      ));
        return $this->render('ParametreBundle:Parametre:index.html.twig', array(
            'menus' => $menus
        ));
    }

    // GESTION DES APPLICATIONS
    public function ApplicationAction()
    {
        $menus = $this->getDoctrine()
                      ->getRepository('AppBundle:MenuIntranet')
                      ->findAll();
        return $this->render('ParametreBundle:Parametre:index.html.twig', array(
            'menus' => $menus
        ));
    }


    public function lancerGestionCatNatureAction()
    {
        $etapeId = 25; //$request->request->get('ETAPE_ID');
        $data = $this->getDoctrine()
            ->getRepository('AppBundle:UserApplication')
            ->lancerGestionNature($etapeId, $this->getUser()->getId());
        return new JsonResponse($data);
    }




}
