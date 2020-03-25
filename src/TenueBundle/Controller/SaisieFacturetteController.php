<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 20/08/2018
 * Time: 16:36
 */

namespace TenueBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SaisieFacturetteController extends Controller
{


    public function saisieFacturetteAction(){


        $operateur = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->find(59);

        $paniers = $this->getDoctrine()
            ->getRepository('AppBundle:Panier')
            ->getPanierNdfFacturette($operateur);

//        var_dump($paniers);

        return $this->render('@Tenue/Saisie/saisie-facturette.html.twig');



    }




}