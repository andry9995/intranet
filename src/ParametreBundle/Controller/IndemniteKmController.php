<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 06/08/2019
 * Time: 11:40
 */

namespace ParametreBundle\Controller;


use AppBundle\Entity\NdfDistanceIndemniteKm;
use AppBundle\Entity\NdfFraisKilometrique;
use AppBundle\Entity\NdfIndemniteKm;
use AppBundle\Entity\NdfPuissanceFiscal;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndemniteKmController extends Controller
{
    public function indexAction(){

        $typeVehicules = $this->getDoctrine()
            ->getRepository('AppBundle:NdfTypeVehicule')
            ->findAll();

        $date_now = new \DateTime();
        $current_year = intval($date_now->format('Y'));
        $exercices = array();

        for($i=0;$i<5;$i++)
            $exercices[] = $current_year + 1 - $i;

        return $this->render('ParametreBundle:IndemniteKm:index.html.twig', [
            'typeVehicules' => $typeVehicules,
            'exercices' => $exercices
        ]);

    }

    public function tableauAction(Request $request){
        $post = $request->request;
        $annee =$post->get('annee');
        $typeVehiculeId = $post->get('typeVehicule');

        $typeVehicule = null;
        if($typeVehiculeId != ''){
            $typeVehicule = $this->getDoctrine()
                ->getRepository('AppBundle:NdfTypeVehicule')
                ->find($typeVehiculeId);
        }

        $fraisKms = [];
        /** @var NdfFraisKilometrique fraisKms */
//        if($annee != '' && $typeVehicule != null){
//            $fraisKms = $this->getDoctrine()
//                ->getRepository('AppBundle:NdfFraisKilometrique')
//                ->getFraisKmByTypeVehiculeAnnee($annee, $typeVehicule);
//        }
//
//
//
//
//
//
//        return $this->render('ParametreBundle:IndemniteKm:indemniteKmTable.html.twig',
//            ['fraisKms' => $fraisKms]
//        );

        /** @var NdfDistanceIndemniteKm[] $distances */
        $distances= $this->getDoctrine()
            ->getRepository('AppBundle:NdfDistanceIndemniteKm')
            ->getDistanceByTypeVehicule($typeVehicule);

        /** @var NdfPuissanceFiscal[] $puissances */
        $puissances = $this->getDoctrine()
            ->getRepository('AppBundle:NdfPuissanceFiscal')
            ->getPuissanceByTypeVehicule($typeVehicule);

        /** @var NdfIndemniteKm[] $indemniteKms */
        $indemniteKms = $this->getDoctrine()
            ->getRepository('AppBundle:NdfIndemniteKm')
            ->getIkByTypeVehiculeExercice($typeVehicule, $annee);


        return $this->render('ParametreBundle:IndemniteKm:indemniteKmTable.html.twig', [
            'distances' => $distances,
            'puissances' => $puissances,
            'iks' => $indemniteKms
            ]);
    }

}