<?php

namespace PilotageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ReceptionController extends Controller
{
    public function indexAction()
    {
        return $this->render('PilotageBundle:Reception:index.html.twig');
    }

    public function receptionExerciceAction()
    {
        $image_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Image');
        $images = [];
        $now = new \DateTime();
        $year = $now->format('Y');
        $debut = intval($year) - 1;
        $fin = intval($year) + 1;

        while($debut <= $fin) {
            $images[] = [
                'title' => 'Objets reçus',
                'exercice' => $debut,
                'today' => $image_repo->imageRecuExercice($debut, 'TODAY'),
                'week' => $image_repo->imageRecuExercice($debut, 'WEEK'),
                'month' => $image_repo->imageRecuExercice($debut, 'MONTH'),
                'total' => $image_repo->imageRecuExercice($debut, 'TOTAL'),
            ];
            $debut++;
        }
        return $this->render('PilotageBundle:Reception:reception-exercice.html.twig', array(
            'images' => $images,
        ));
    }

    public function receptionJournalierAction()
    {
        $image_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Image');
        $images = [];
        $types = ["ALL" => "Objets sur Picdata", "DOWNLOADED" => "Objets descendus", "NOT_DOWNLOADED" => "Reste à descendre"];
        foreach ($types as $type => $libelle) {
            $images[] = [
                'title' => $libelle,
                'j' => $image_repo->imageRecuJour(0, $type),
                'j_1' => $image_repo->imageRecuJour(-1, $type),
                'j_2' => $image_repo->imageRecuJour(-2, $type),
                'j_3' => $image_repo->imageRecuJour(-3, $type),
                'j_4' => $image_repo->imageRecuJour(-4, $type),
            ];
        }
        return $this->render('PilotageBundle:Reception:reception-journalier.html.twig', array(
            'images' => $images,
        ));
    }
}
