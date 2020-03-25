<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 20/11/2018
 * Time: 09:26
 */

namespace TacheBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Tache;
use AppBundle\Entity\Taches;
use AppBundle\Entity\TachesLibre;
use AppBundle\Entity\TachesLibreDate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Functions\CustomPdoConnection;

class TachesEntityController extends Controller
{
    public $pdo;

    //initisalisation pdo

    /**
     * TachesController constructor.
     */
    public function __construct()
    {
        $con = new CustomPdoConnection();
        $this->pdo = $con->connect();
    }

    /**
     * @param $entity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($entity)
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Tache')
            ->listeClient();

        return $this->render('@Tache/TachesEntity/index.html.twig',[
            'entity' => $entity,
            'clients' => $clients
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function chargerTableAction(Request $request)
    {
        $type = intval($request->request->get('type'));
        /** @var Client[] $clients */
        $clients = [];
        /** @var Dossier[] $dossiers */
        $dossiers = [];
        /** @var Dossier $dossier */
        $dossier = null;

        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($request->request->get('client'));
        if ($type == 0)
        {
            if (is_null($client)) $clients = $this->getDoctrine()->getRepository('AppBundle:Client')->getAllClient();
            else $clients[] = $client;
        }
        else
        {
            $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($request->request->get('dossier'));
            if (is_null($dossier)) $dossiers = $this->getDoctrine()->getRepository('AppBundle:Dossier')->getAllDossierObject($client);
            else $dossiers[] = $dossier;
        }

        $datas = [];
        $colNames = [($type == 1) ? 'Dossier' : 'Client'];
        $colModels = [];
        $colModels[] = colModel::getModel('t_entity',false,600,'','','left',true);
        $headGroup = [];
        $headGroup[] = colModel::getGroupModel('t_entity');

        $widthTache = 200;
        /** @var Taches[] $tachesLegales */
        $tachesLegales = [];
        if ($type == 1)
        {
            $tachesLegales = $this->getDoctrine()->getRepository('AppBundle:Taches')
                ->getListeForClient($client,$dossier);
        }
        if (count($tachesLegales) > 0)
        {
            foreach ($tachesLegales as $tachesLegale)
            {
                $headGroup[] = colModel::getGroupModel('t_lg_'.$tachesLegale->getId(),count($tachesLegales),'Tache Légale');
                break;
            }
        }

        /** @var Tache[] $tacheLibres */
        $tacheLibres = $this->getDoctrine()->getRepository('AppBundle:Tache')->getAllTache();
        if (count($tacheLibres) > 0)
            $headGroup[] = colModel::getGroupModel('t_lb_'.$tacheLibres[0]->getId(),count($tacheLibres),'Tache Libre');

        /**
         * Datas
         */
        if ($type == 0)
        {
            $isfirst = true;
            foreach ($clients as $cl)
            {
                $data =
                    [
                        'id' => $cl->getId(),
                        't_entity' => $cl->getNom()
                    ];

                foreach ($tacheLibres as $tacheLibre)
                {
                    if ($isfirst)
                    {
                        $colNames[] = $tacheLibre->getNom();
                        $colModels[] = colModel::getModel('t_lb_'.$tacheLibre->getId(),true,$widthTache,'cl_t_cell','checkbox','center',false);
                    }

                    $tachesLibre = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
                        ->getTachesLibre($tacheLibre,$cl,null);

                    $s = $tachesLibre ? 1 : 0;

                    $data['t_lb_'.$tacheLibre->getId()] = $s;
                }
                $isfirst = false;

                $datas[] = (object)$data;
            }
        }
        else
        {
            $isfirst = true;
            foreach ($dossiers as $dos)
            {
                $data =
                    [
                        'id' => $dos->getId(),
                        't_entity' => $dos->getNom()
                    ];

                foreach ($tachesLegales as $tachesLegale)
                {
                    if ($isfirst)
                    {
                        $colNames[] = $tachesLegale->getNom();
                        $colModels[] = colModel::getModel('t_lg_'.$tachesLegale->getId(),true,$widthTache,'cl_t_cell legale','checkbox','center',false);
                    }

                    $s = $this->getDoctrine()->getRepository('AppBundle:Taches')
                        ->getStatusForDossier($tachesLegale,$dos);

                    $data['t_lg_'.$tachesLegale->getId()] = $s;
                }
                foreach ($tacheLibres as $tacheLibre)
                {
                    if ($isfirst)
                    {
                        $colNames[] = $tacheLibre->getNom();
                        $colModels[] = colModel::getModel('t_lb_'.$tacheLibre->getId(),true,$widthTache,'cl_t_cell','checkbox','center',false);
                    }

                    $tachesLibre = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
                        ->getTachesLibre($tacheLibre,null,$dos);

                    $s = $tachesLibre ? 1 : 0;
                    $data['t_lb_'.$tacheLibre->getId()] = $s;
                }
                $isfirst = false;

                $datas[] = (object)$data;
            }
        }

        $result = (object)
        [
            'colNames' => $colNames,
            'colModels' => $colModels,
            'datas' => $datas,
            'headGroups' => $headGroup
        ];

        return new JsonResponse($result);
    }

    public function imputationTachesAction(Request $request)
    {
        $idIndex = intval($request->request->get('id_index'));
        /** @var Client $client */
        $client = null;
        /** @var Dossier $dossier */
        $dossier = null;
        if (intval($request->request->get('type_entity')) == 0)
            $client = $this->getDoctrine()->getRepository('AppBundle:Client')
                ->find($request->request->get('entity'));
        else
            $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
                ->find($request->request->get('entity'));

        /** @var Taches $taches */
        $taches = null;
        /** @var Tache $tache */
        $tache = null;
        if (intval($request->request->get('type_tache')) == 0)
            $taches = $this->getDoctrine()->getRepository('AppBundle:Taches')
                ->find($request->request->get('tache'));
        else
            $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')
                ->find($request->request->get('tache'));

        if ($taches && $dossier)
        {
            $tachesItems = $this->getDoctrine()->getRepository('AppBundle:TachesItem')
                ->getByDossier($taches,$dossier);
            return $this->render('@Tache/TachesEntity/taches-item.html.twig',['taches'=>$taches,'tachesItems'=>$tachesItems,'dossier'=>$dossier]);
        }
        elseif ($tache)
        {
            /** @var TachesLibre $tachesLibre */
            $tachesLibre = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
                ->getTachesLibre($tache,$client,$dossier);

            /** @var TachesLibre $tachesLibreParent */
            $tachesLibreParent = null;

            if ($dossier)
            {
                if ($tachesLibre && $tachesLibre->getTachesLibre())
                    $tachesLibreParent = $tachesLibre->getTachesLibre();
                else
                    $tachesLibreParent = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
                        ->getTachesLibreParent($tache,$dossier);
            }

            /** @var TachesLibreDate[] $tachesLibreDates */
            $tachesLibreDates = [];
            if ($tachesLibre && $tachesLibre->getStatus() == 1)
                $tachesLibreDates = $this->getDoctrine()->getRepository('AppBundle:TachesLibreDate')
                    ->tachesLibreDates($tachesLibre);

            return $this->render('@Tache/TachesEntity/taches-libre.html.twig',
                [
                    'tache' => $tache,
                    'tachesLibre' => $tachesLibre,
                    'tachedLibreParent' => $tachesLibreParent,
                    'tachesLibreDates' => $tachesLibreDates,
                    'index' => $idIndex,
                    'client' => $client,
                    'dossier' => $dossier
                ]);
        }

        return $this->render('@Tache/TacheAdmin/test.html.twig',['test'=>[$taches,$tache]]);
    }

    public function saveEntityAction(Request $request)
    {
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));
        $tds = $request->request->get('taches_dates');

        $tachesEntitys = [];
        foreach ($tds as $td)
        {
            $tachesDate = $this->getDoctrine()->getRepository('AppBundle:TachesDate')
                ->find($td['id']);

            $responsable = intval($td['r']);
            $jourAdd = intval($td['j']);
            $status = intval($td['s']);
            $tachesEntity = $this->getDoctrine()->getRepository('AppBundle:TachesEntity')
                ->getTachesEntity($tachesDate,$dossier,true,$status,$responsable,$jourAdd);


            $tachesEntitys[] = $tachesEntity;
        }

        $prestationFiscale = $this->getDoctrine()->getRepository('AppBundle:PrestationFiscale')
                ->findByDossier($request->request->get('dossier'));

        if(count($prestationFiscale>0)){
            $prestationFiscale = $prestationFiscale[0];
            $q = 'UPDATE prestation_fiscale SET '.$td['chmp'].'='.$td['r'];
            $this->pdo->exec($q);
        }

        $taches = $this->getDoctrine()->getRepository('AppBundle:Taches')
            ->find($request->request->get('taches'));
        $s = $this->getDoctrine()->getRepository('AppBundle:Taches')
            ->getStatusForDossier($taches,$dossier);

        return new Response($s);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function editTachesLibreDateAction(Request $request)
    {
        $action = intval($request->request->get('action'));
        $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')
            ->find($request->request->get('tache'));
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->find($request->request->get('client'));
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));

        /** @var TachesLibreDate $tachesLibreDate */
        $tachesLibreDate = $this->getDoctrine()->getRepository('AppBundle:TachesLibreDate')
            ->find($request->request->get('taches_libre_date'));

        $em = $this->getDoctrine()->getManager();

        if ($action == 0)
        {
            return $this->render('@Tache/TachesEntity/tache-libre-date.html.twig',
                [
                    'tache' => $tache,
                    'tachesLibreDate' => $tachesLibreDate
                ]);
        }
        elseif ($action === 1)
        {
            $demarage = null;
            if (trim($request->request->get('demarage')) != '')
                $demarage = \DateTime::createFromFormat('d/m/Y',trim($request->request->get('demarage')));

            $dateCalcul = null;
            if (trim($request->request->get('date_calcul')) != '')
                $dateCalcul = \DateTime::createFromFormat('d/m/Y',trim($request->request->get('date_calcul')));

            $periode = intval($request->request->get('periode'));
            $jour = intval($request->request->get('jour'));
            $responsable = intval($request->request->get('responsable'));
            $aPartirDe = intval($request->request->get('a_partir_de'));
            $jalon = intval($request->request->get('jalon'));
            $moisAdditif = intval($request->request->get('mois_additif'));

            $tachesLibre = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
                ->getTachesLibre($tache,$client,$dossier,$responsable);

            $add = false;
            if (!$tachesLibreDate)
            {
                $add = true;
                $tachesLibreDate = new TachesLibreDate();
                $tachesLibreDate
                    ->setTachesLibre($tachesLibre);
            }

            $tachesLibreDate
                ->setDateCalcul($dateCalcul)
                ->setJour($jour)
                ->setCalculerAPartir($aPartirDe)
                ->setDemarrage($demarage)
                ->setJalon($jalon)
                ->setPeriode($periode)
                ->setMoisAdditif($moisAdditif);

            if ($add) $em->persist($tachesLibreDate);
            $em->flush();

            return $this->render('@Tache/TachesEntity/tache-libre-date_tr.html.twig',[
                'tachesLibreDate' => $tachesLibreDate
            ]);
        }
        elseif ($action === 2)
        {
            if ($tachesLibreDate)
            {
                $tachesLibre = $tachesLibreDate->getTachesLibre();
                $tachesLibreDates = $this->getDoctrine()->getRepository('AppBundle:TachesLibreDate')
                    ->tachesLibreDates($tachesLibre);

                if (count($tachesLibreDates) == 1) $em->remove($tachesLibre);
                else $em->remove($tachesLibreDate);

                $em->flush();
            }
        }

        return new Response(1);
    }

    public function heriterChangeAction(Request $request)
    {
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->find($request->request->get('client'));
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));
        $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')
            ->find($request->request->get('tache'));

        $responsable = intval($request->request->get('responsable'));
        $heriter = (intval($request->request->get('heriter')) == 1);
        $activer = 1;

        if ($heriter) $activer = intval($request->request->get('activer'));

        /** @var TachesLibre $tachesLibreParent */
        $tachesLibreParent = null;
        if ($heriter && $dossier)
        {
            $tachesLibreParent = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
                ->getTachesLibreParent($tache,$dossier);
        }

        /** @var TachesLibre $tachesLibre */
        $tachesLibre = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
            ->getTachesLibre($tache,$client,$dossier,$responsable,$tachesLibreParent,$activer);

        $status = $this->getDoctrine()->getRepository('AppBundle:TachesLibre')
            ->isActive($tachesLibre) ? 1 :0;
        return new Response($status);
    }
}