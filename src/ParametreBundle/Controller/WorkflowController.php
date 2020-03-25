<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 31/07/2018
 * Time: 13:57
 */

namespace ParametreBundle\Controller;


use AppBundle\Entity\Categorie;
use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\ProcessClientCategorie;
use AppBundle\Entity\ProcessClientCategorieEtape;
use AppBundle\Entity\ProcessDossierCategorie;
use AppBundle\Entity\ProcessDossierCategorieEtape;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkflowController extends Controller
{
    public function indexAction()
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();


        $curYear = date("Y");
        $exercices = array();
        for ($i = intval($curYear) - 2; $i <= intval($curYear) + 1; $i++) {
            $exercices[] = $i;
        }
        return $this->render('@Parametre/Workflow/index.html.twig', array(
            'clients' => $clients,
            'exercices' => $exercices,
            'currentYear' => intval($curYear),
        ));
    }

    public function categorieAction(Request $request)
    {
        $idclient = $request->request->get('clients');
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($idclient);
        $iddossier = $request->request->get('dossiers');
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($iddossier);
        $exo = $request->request->get('exercice');
        $isParClient = $request->request->get('isParClient');

        /*
         * Récupérer liste catégorie
         */
        /** @var Categorie[] $categories */
        $categories = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->createQueryBuilder('e')
            ->select('e')
            ->where('e.actif>=:actif')
            ->orderBy('e.libelleNew')
            ->setParameter('actif',1)
            ->getQuery()
            ->getResult();

        /*
         * Récupérer liste étape
         */
        /** @var EtapeTraitement[] $etape */
        $etape = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->createQueryBuilder('e')
            ->select('e')
            ->where('e.process>=:process')
            ->orderBy('e.id')
            ->setParameter('process', 1)
            ->getQuery()
            ->getResult();
        $entete = array();
        $model = array();
        $model[] = (object) ['name'=>'colCateg', 'index'=>'colCateg'];
        $entete[] = "Catégorie";
        $etapeOblig = ['DEC_NIV_1', 'DEC_NIV_2'];
        foreach ($etape as $item)
        {
            $entete[] = $item->getCode();
            $model[] = (object) ['name'=>'col-'.$item->getId(), 'index'=>'col-'.$item->getId(), 'formatter'=>'checkbox',
            'editable'=> (!in_array($item->getCode() ,$etapeOblig)), 'align'=>'center', 'edittype'=>'checkbox',
            'editoptions'=> (object)['value'=> "1:0"],
            'formatoptions'=>(object)['disabled'=>(in_array($item->getCode() ,$etapeOblig))]];
        }

        $contentTableau = array();
        //Récupérer catégorie

        foreach( $categories as $item)
        {
            //Récupérer info par catégorie par client
            if ($isParClient) {
                /** @var ProcessClientCategorieEtape[] $etapeTrouvee */
                $etapeTrouvee = $this->getDoctrine()
                    ->getRepository('AppBundle:ProcessClientCategorieEtape')
                    ->createQueryBuilder('p')
                    ->leftJoin('p.processClientCateg', 'e')
                    ->select('p')
                    ->where('e.client>=:clientId')
                    ->andWhere('e.categorie=:categorieId')
                    ->andWhere('e.actif=:actif')
                    ->andWhere('e.exercice=:exercice')
                    ->setParameters(['clientId' => $client, 'categorieId' => $item, 'exercice' => $exo, 'actif' => 1])
                    ->getQuery()
                    ->getResult();
            }
            else
            {
                /** @var ProcessDossierCategorieEtape[] $etapeTrouvee */
                $etapeTrouvee = $this->getDoctrine()
                    ->getRepository('AppBundle:ProcessDossierCategorieEtape')
                    ->createQueryBuilder('p')
                    ->leftJoin('p.processDossierCateg', 'e')
                    ->select('p')
                    ->where('e.dossier>=:dossierId')
                    ->andWhere('e.categorie=:categorieId')
                    ->andWhere('e.actif=:actif')
                    ->andWhere('e.exercice=:exercice')
                    ->setParameters(['dossierId' => $dossier, 'categorieId' => $item, 'exercice' => $exo, 'actif' => 1])
                    ->getQuery()
                    ->getResult();
            }
            $etapeIdTrouvee = array();
            foreach ($etapeTrouvee as $itemId)
            {
                $etapeIdTrouvee[] = $itemId->getEtapeTraitement()->getId();
            }
            $contentEtape = array();
            $contentEtape['colCateg'] = '<span class="categorie-name" data-id="'. $item->getId() .'">'. $item->getLibelleNew() . '</span>';
            foreach ($etape as $itemEtape)
            {
                $contentEtape['col-'.$itemEtape->getId()] = (in_array($itemEtape->getId(), $etapeIdTrouvee) || in_array($itemEtape->getCode(), $etapeOblig))? 1:0;
            }
            $contentTableau[] = $contentEtape;
        }

        $donnee = (object)['entete'=>$entete, 'model'=>$model, 'data'=>$contentTableau];
        return new JsonResponse($donnee);
    }

    public function etapeChoisieAction(Request $request)
    {
        $client = $request->request->get('idClient');
        $idClient = $this->getDoctrine()->getRepository('AppBundle:Client')->find($client);
        $dossier = $request->request->get('idDossier');
        $idDossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($dossier);
        $categorie = $request->request->get('idCateg');
        $idCateg = $this->getDoctrine()->getRepository('AppBundle:Categorie')->find($categorie);
        $exo = $request->request->get('exercice');
        /** @var ProcessClientCategorieEtape[] $etapeChoisie */
        $etapeChoisie = $this->getDoctrine()
            ->getRepository('AppBundle:ProcessClientCategorieEtape')
            ->createQueryBuilder('pe')
            ->leftJoin('pe.processClientCateg', 'pcce' )
            ->where('pcce.client=:idClient' )
            ->andWhere('pcce.categorie=:idCateg')
            ->andWhere('pcce.exercice=:exo')
            ->setParameters([
                'idClient'=>$idClient,
                'idCateg'=>$idCateg,
                'exo'=>$exo
            ])
            ->getQuery()
            ->getResult();
        if (count($etapeChoisie) > 0)
            return $this->render('@Parametre/Workflow/workflow_etapechoisie.html.twig', array(
                'etapeChoisie' => $etapeChoisie));
        else
            return $this->render('@Parametre/Workflow/workflow_etapechoisie.html.twig', array(
                'etapeChoisie' => null));
    }


    public function etapeDisponibleAction(Request $request)
    {
        $listEtapeId = json_decode($request->request->get('listeIdEtape'));
        if (count($listEtapeId) > 0) {
            /** @var EtapeTraitement[] $etape */
            $etape = $this->getDoctrine()
                ->getRepository('AppBundle:EtapeTraitement')
                ->createQueryBuilder('e')
                ->select('e')
                ->where('e.process>=:process')
                ->andWhere('e.id not in (:listeEtapeId)')
                ->orderBy('e.id')
                ->setParameter('process', 1)
                ->setParameter('listeEtapeId', $listEtapeId)
                ->getQuery()
                ->getResult();
        }
        else
        {
            /** @var EtapeTraitement[] $etape */
            $etape = $this->getDoctrine()
                ->getRepository('AppBundle:EtapeTraitement')
                ->createQueryBuilder('e')
                ->select('e')
                ->where('e.process>=:process')
                ->orderBy('e.id')
                ->setParameter('process', 1)
                ->getQuery()
                ->getResult();
        }
        return $this->render('@Parametre/Workflow/workflow_etapeDisponible.html.twig', array(
           'etapeDisponible'=> $etape  ));
    }

    public function editUrlWorkflowAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id', '');
            $remise_code = $request->request->get('remise-code');
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function saveWorkflowAction(Request $request)
    {

        $resultats = json_decode($request->request->get('resultats'));
        $isParClient = $request->request->get('isParClient');
        $clientId = $request->request->get('idClient');
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($clientId);
        $dossierId = $request->request->get('idDossier');
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($dossierId);
        //return new Response(var_dump(json_decode($request->request->get('idDossier'))));
        /** @var Operateur $user */
        $userId = $this->getUser();
        $exercice = $request->request->get('exercice');

        if ($isParClient == 1) {

            foreach ($resultats as $itemRes)
            {
                $idCateg = $itemRes->idCateg;
                if (count($itemRes->idEtape) > 0) {
                    $categorie = $this->getDoctrine()->getRepository('AppBundle:Categorie')->find($idCateg);
                    //Vérifier si la ligne y est déjà
                    $insert = $this->getDoctrine()
                        ->getRepository('AppBundle:ProcessClientCategorie')
                        ->createQueryBuilder('e')
                        ->select('e')
                        ->where('e.client=:clientId')
                        ->andWhere('e.categorie=:categorieId')
                        ->andWhere('e.exercice=:exercice')
                        ->setParameters(array(
                            'clientId' => $client,
                            'categorieId' => $categorie,
                            'exercice' => $exercice,
                        ))
                        ->getQuery()
                        ->getOneOrNullResult();
                    $em = $this->getDoctrine()->getManager();
                    if (is_null($insert)) {
                        $insert = new ProcessClientCategorie();
                        $insert->setClient($client);
                        $insert->setCategorie($categorie);
                        $insert->setOperateur($userId);
                        $insert->setDateCreation(new \DateTime());
                        $insert->setActif(1);
                        $insert->setExercice($exercice);
                        $em->persist($insert);
                    } else {
                        $insert->setOperateur($userId);
                        $insert->setDateCreation(new \DateTime());
                    }
                    $em->flush();

                    //Supprimer d'abord les étapes déjà dans la base
                    $con = new CustomPdoConnection();
                    $pdo = $con->connect();
                    $query = "DELETE FROM  process_client_categorie_etape WHERE process_client_categ_id=:process_id";
                    $prep = $pdo->prepare($query);
                    $prep->execute(array(
                            $insert->getId()
                        )
                    );

                    //Ajouter liste étape traitement
                    $listEtapeId = $itemRes->idEtape;
                    foreach ($listEtapeId as $value) {
                        $etape = $this->getDoctrine()->getRepository('AppBundle:EtapeTraitement')->find($value);
                        $processEtape = new ProcessClientCategorieEtape();
                        $processEtape->setEtapeTraitement($etape)
                            ->setProcessClientCateg($insert);
                        $em->persist($processEtape);
                    }
                    $em->flush();
                }
            }

        } //Paramètre par dossier
        else
        {
            foreach ($resultats as $itemRes) {
                $idCateg = $itemRes->idCateg;
                if (count($itemRes->idEtape) > 0) {
                    $categorie = $this->getDoctrine()->getRepository('AppBundle:Categorie')->find($idCateg);
                    //Vérifier si la ligne y est déjà
                    $insert = $this->getDoctrine()
                            ->getRepository('AppBundle:ProcessDossierCategorie')
                            ->createQueryBuilder('e')
                            ->select('e')
                            ->where('e.dossier=:dossierId')
                            ->andWhere('e.categorie=:categorieId')
                            ->andWhere('e.exercice=:exercice')
                            ->setParameters(array(
                                'dossierId' => $dossier,
                                'categorieId' => $categorie,
                                'exercice' => $exercice,
                            ))
                            ->getQuery()
                            ->getOneOrNullResult();


                    $em = $this->getDoctrine()->getManager();
                    if (is_null($insert)) {
                        $insert = new ProcessDossierCategorie();
                        $insert->setDossier($dossier);
                        $insert->setCategorie($categorie);
                        $insert->setOperateur($userId);
                        $insert->setDateCreation(new \DateTime());
                        $insert->setActif(1);
                        $insert->setExercice($exercice);
                        $em->persist($insert);
                    } else {
                        $insert->setOperateur($userId);
                        $insert->setDateCreation(new \DateTime());
                    }
                    $em->flush();

                    //Supprimer d'abord les étapes déjà dans la base

                    $con = new CustomPdoConnection();
                    $pdo = $con->connect();
                    $query = "DELETE FROM  process_dossier_categorie_etape WHERE process_dossier_categ_id=:process_id";
                    $prep = $pdo->prepare($query);
                    $prep->execute(array(
                            $insert->getId()
                        )
                    );

                    //Ajouter liste étape traitement
                    $listEtapeId = $itemRes->idEtape;
                    foreach ($listEtapeId as $value) {
                        $etape = $this->getDoctrine()->getRepository('AppBundle:EtapeTraitement')->find($value);
                        $processEtape = new ProcessDossierCategorieEtape();
                        $processEtape->setEtapeTraitement($etape)
                            ->setProcessDossierCateg($insert);
                        $em->persist($processEtape);
                    }
                    $em->flush();
                }
            }
        }

        return new Response('Success');
    }

    public function categorieNatureAction(){
        $operateurId = $this->getUser()->getId();
        $listesBanque = $this->getDoctrine()
                             ->getRepository('AppBundle:UserApplication')
                             ->lancerCategorieNature($operateurId);
       return $this->render('@Parametre/Workflow/categorie-nature.html.twig', array(
           'etat'=> $listesBanque  ));
    }
}