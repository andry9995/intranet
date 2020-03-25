<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 21/01/2020
 * Time: 11:08
 */

namespace ReceptionBundle\Controller;


use AppBundle\Entity\Image;
use AppBundle\Entity\LogImageSuppr;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageSupprController extends Controller
{
    public function indexAction(){
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();

        return $this->render('@Reception/ImageSuppr/index.html.twig',
            ['clients' => $clients]
        );
    }

    public function dossierAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $clientid = $post->get('clientid');

        $client = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->find($clientid);

        $dossiers = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->getAllDossierObject($client);

        return $this->render('@Tenue/SaisieUniverselle/optionDossier.html.twig', ['dossiers' => $dossiers]);
    }

    public function exerciceAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $dossierid = $request->query->get('dossierid');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        if($dossier === null)
            throw new NotFoundHttpException('Dossier introuvable');


        $exercices = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getExercicesByDossier($dossier);

        return $this->render('TenueBundle:SaisieUniverselle:optionExercice.html.twig', ['exercices' => $exercices]);
    }

    public function dateScanAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossierid = $get->get('dossierid');
        $exercice = $get->get('exercice');

        $dateScans = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getDateScanImageSuppr($dossierid, $exercice);

        return $this->render('@Tenue/SaisieUniverselle/optionDateScan.html.twig', ['datescans' => $dateScans]);
    }

    public function lotAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossierid = $get->get('dossierid');
        $exercice = $get->get('exercice');
        $dateScan = $get->get('datescan');
//        $dateScan = \DateTime::createFromFormat('Y-m-d', $dateScan);

        $lots = $this->getDoctrine()
            ->getRepository('AppBundle:Lot')
            ->getLotImageSuppr($dossierid, $exercice, $dateScan)
        ;

        return $this->render('@Reception/ImageSuppr/optionLot.html.twig', ['lots' => $lots]);
    }

    public function listImageAction(Request $request){
        $post = $request->request;

        $dossierid = $post->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $dateScan = $post->get('datescan');
        $dateScan = \DateTime::createFromFormat('Y-m-d', $dateScan)
            ->setTime(0,0,0);

        $lot = $post->get('lot');
        $exercice = $post->get('exercice');

        $images = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getImageSuppr($dossier, $exercice, $dateScan, $lot);

        $rows = [];
        /** @var Image $image */
        foreach ($images as $image){
            $rows[]=[
                'id' => $image->getId(),
                'cell' => [
                    'is_nom' => $image->getNom(),
                    'is_dossier' => $image->getLot()->getDossier()->getNom(),
                    'is_exercice' => $image->getExercice()
                ]
            ];
        }
        return new JsonResponse(['rows' => $rows]);
    }


    public function deleteAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $ids = $post->get('ids');

        $em = $this->getDoctrine()
            ->getManager();

        foreach ($ids as $id){
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($id);

            $image->setSupprimer(1);

            $log = new LogImageSuppr();
            $log->setImage($image)
                ->setOperateur($this->getUser())
                ->setDate(new \DateTime('now'))
            ;
            $em->persist($log);
        }
        $em->flush();
        return new JsonResponse(['type' => 'success', 'message' => 'suppression effectuée avec succès']);
    }
}