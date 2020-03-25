<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\RegimeFiscal;
use AppBundle\Entity\RegimeImposition;
use AppBundle\Entity\RegimeTva;
use AppBundle\Entity\TacheListeAction;
use AppBundle\Entity\Taches;
use AppBundle\Entity\TachesAction;
use AppBundle\Entity\TachesDate;
use AppBundle\Entity\TachesEntity;
use AppBundle\Entity\TachesGroup;
use AppBundle\Entity\TachesGroupRegimeFiscal;
use AppBundle\Entity\TachesItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Count;
use AppBundle\Functions\CustomPdoConnection;

class TachesController extends Controller
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

    public function tachesGroupsAction(Request $request)
    {
        $regimesFiscals = $this->getDoctrine()
            ->getRepository('AppBundle:RegimeFiscal')
            ->getForAllTache();

        $tachesGroups = $this->getDoctrine()->getRepository('AppBundle:TachesGroup')
            ->getListe();

        return $this->render('@Tache/Taches/regime-fiscal.html.twig',[
            'regimesFiscals' => $regimesFiscals,
            'tachesGroups' => $tachesGroups
        ]);
    }

    public function tachesGroupEditAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var int $action
         *  0: show edit
         *  1: edit
         *  2: delete
         */
        $action = intval($request->request->get('action'));

        /** @var TachesGroup $tacheGroup */
        $tacheGroup = $this->getDoctrine()->getRepository('AppBundle:TachesGroup')
            ->find($request->request->get('taches_group'));
        if ($action == 0)
        {
            $regimefiscals = $this->getDoctrine()->getRepository('AppBundle:RegimeFiscal')
                ->getForAllTache();

            $regimeFiscalIdsSelected = [];
            if ($tacheGroup)
            {
                $tachesGroupRegimes = $this->getDoctrine()->getRepository('AppBundle:TachesGroupRegimeFiscal')
                    ->getTachesGroupRegimeFiscal($tacheGroup);

                foreach ($tachesGroupRegimes as $tachesGroupRegimeFiscal)
                    $regimeFiscalIdsSelected[] = $tachesGroupRegimeFiscal->getRegimeFiscal()->getId();
            }

            return $this->render('@Tache/Taches/taches-group-edit.html.twig', [
                'tacheGroup' => $tacheGroup,
                'regimefiscals' => $regimefiscals,
                'regimeFiscalIdsSelected' => $regimeFiscalIdsSelected
            ]);
        }
        elseif ($action == 1)
        {
            $add = false;
            if (is_null($tacheGroup))
            {
                $tacheGroup = new TachesGroup();
                $add = true;
            }
            else
            {
                $tachesGroupRegimes = $this->getDoctrine()->getRepository('AppBundle:TachesGroupRegimeFiscal')
                    ->getTachesGroupRegimeFiscal($tacheGroup);

                foreach ($tachesGroupRegimes as $tachesGroupRegime) $em->remove($tachesGroupRegime);
                $em->flush();
            }

            $nom = $request->request->get('nom');

            $tacheGroup
                ->setNom($nom);

            if ($add) $em->persist($tacheGroup);
            $em->flush();

            /** @var RegimeFiscal[] $regimefiscals */
            $regimefiscals = $this->getDoctrine()->getRepository('AppBundle:RegimeFiscal')
                ->createQueryBuilder('rf')
                ->where('rf.id IN (:ids)')
                ->setParameter('ids',$request->request->get('regimes'))
                ->getQuery()
                ->getResult();

            foreach ($regimefiscals as $regimeFiscal)
            {
                $tachesGroupRegime = new TachesGroupRegimeFiscal();
                $tachesGroupRegime
                    ->setRegimeFiscal($regimeFiscal)
                    ->setTachesGroup($tacheGroup);

                $em->persist($tachesGroupRegime);
            }
        }
        elseif ($action == 2)
        {
            if ($tacheGroup) $em->remove($tacheGroup);
        }

        $em->flush();
        return new Response(1);
    }

    public function tachesAction(Request $request)
    {
        $regimeFiscal = null;
        $tachesGroup = null;
        $type = intval($request->request->get('type'));

        if ($type == 0) $regimeFiscal = $this->getDoctrine()->getRepository('AppBundle:RegimeFiscal')
            ->find($request->request->get('regime'));
        else $tachesGroup = $this->getDoctrine()->getRepository('AppBundle:TachesGroup')
            ->find($request->request->get('regime'));

        $taches = $this->getDoctrine()->getRepository('AppBundle:Taches')
            ->getListe($regimeFiscal, $tachesGroup);

        return $this->render('@Tache/Taches/taches.html.twig', [
            'regimeFiscal' => $regimeFiscal,
            'tachesGroup' => $tachesGroup,
            'taches' => $taches
        ]);
    }

    public function editTachesAction(Request $request)
    {
        /**
         * @var int $action
         *  0: show edit
         *  1: edit
         *  2: delete
         */
        $action = intval($request->request->get('action'));
        /** @var int $type */
        $type = intval($request->request->get('type'));
        /** @var RegimeFiscal $regimeFiscal */
        $regimeFiscal = null;
        /** @var TachesGroup $tachesGroup */
        $tachesGroup = null;
        if ($type == 0)
            $regimeFiscal = $this->getDoctrine()->getRepository('AppBundle:RegimeFiscal')
                ->find($request->request->get('regime'));
        else
            $tachesGroup = $this->getDoctrine()->getRepository('AppBundle:TachesGroup')
                ->find($request->request->get('regime'));

        /** @var Taches $taches */
        $taches = $this->getDoctrine()->getRepository('AppBundle:Taches')
            ->find($request->request->get('taches'));

        /** @var PrestationFiscaleTache $prestationFiscaleTache */
        $prestationFiscaleTache = $this->getDoctrine()
                                       ->getRepository('AppBundle:PrestationFiscaleTache')
                                       ->findBy(
                                            array(),
                                            array('libelle' => 'ASC')
                                        );

        $em = $this->getDoctrine()->getManager();

        if ($action == 0)
        {
            return $this->render('@Tache/Taches/taches-edit.html.twig', [
                'taches' => $taches,
                'regimeFiscal' => $regimeFiscal,
                'tachesGroup' => $tachesGroup,
                'prestationFiscaleTache' => $prestationFiscaleTache
            ]);
        }
        elseif ($action == 1)
        {
            $add = false;
            if (is_null($taches))
            {
                $taches = new Taches();
                if ($type == 0) $taches->setRegimeFiscal($regimeFiscal);
                else $taches->setTachesGroup($tachesGroup);
                $add = true;
            }

            $isTva = intval($request->request->get('is_tva'));
            $nom = $request->request->get('nom');
            $prestationFiscaleTache = $this->getDoctrine()
                        ->getRepository('AppBundle:PrestationFiscaleTache')
                        ->find($request->request->get('fiscale'));

            $taches
                ->setNom($nom)
                ->setPrestationFiscaleTache($prestationFiscaleTache);

            if ($add) $em->persist($taches);
        }
        elseif ($action == 2)
        {
            if ($taches) $em->remove($taches);
        }

        $em->flush();
        return new Response(1);
    }

    public function tachesItemsAction(Request $request)
    {
        $taches = $this->getDoctrine()->getRepository('AppBundle:Taches')
            ->find($request->request->get('taches'));

        $tachesItems = $this->getDoctrine()->getRepository('AppBundle:TachesItem')
            ->getTachesItems($taches);

        return $this->render('@Tache/Taches/taches-item.html.twig',[
            'taches' => $taches,
            'tachesItems' => $tachesItems
        ]);
    }

    public function editTachesItemAction(Request $request)
    {
        /**
         * @var int $action
         *  0: show edit
         *  1: edit
         *  2: delete
         */
        $action = intval($request->request->get('action'));
        /** @var Taches $taches */
        $taches = $this->getDoctrine()->getRepository('AppBundle:Taches')
            ->find($request->request->get('taches'));
        /** @var TachesItem $tachesItem */
        $tachesItem = $this->getDoctrine()->getRepository('AppBundle:TachesItem')
            ->find($request->request->get('taches_item'));

        /** @var RegimeImposition[] $regimeImpositions */
        $regimeImpositions = [];
        /** @var RegimeTva[] $regimeTvas */
        $regimeTvas = [];

        /** @var RegimeImposition $regimeImposition */
        $regimeImposition = null;
        /** @var RegimeTva $regimeTva */
        $regimeTva = null;
        $type = 0;

        if (trim(strtoupper($taches->getNom())) == 'TVA')
        {
            $regimeTvas = $this->getDoctrine()->getRepository('AppBundle:TachesItem')
                ->getResteRegimeTva($taches,$tachesItem);
            $regimeTva = $this->getDoctrine()->getRepository('AppBundle:RegimeTva')
                ->find($request->request->get('regime_imposition'));
            $type = 1;
        }
        else
        {
            $regimeImpositions = $this->getDoctrine()->getRepository('AppBundle:TachesItem')
                ->getResteRegimeImposition($taches,$tachesItem);
            $regimeImposition = $this->getDoctrine()->getRepository('AppBundle:RegimeImposition')
                ->find($request->request->get('regime_imposition'));
        }

        $em = $this->getDoctrine()->getManager();
        if ($action == 0)
        {
            return $this->render('@Tache/Taches/taches-item-edit.html.twig', [
                'taches' => $taches,
                'tachesItem' => $tachesItem,
                'regimeImpositions' => $regimeImpositions,
                'regimeTvas' => $regimeTvas,
                'type' => $type
            ]);
        }
        elseif ($action == 1)
        {
            $add = false;
            if (is_null($tachesItem))
            {
                $tachesItem = new TachesItem();
                $tachesItem->setTaches($taches);
                $add = true;
            }

            $tachesItem
                ->setRegimeImposition($regimeImposition)
                ->setRegimeTva($regimeTva);

            if ($add) $em->persist($tachesItem);
        }
        elseif ($action == 2)
        {
            if ($tachesItem) $em->remove($tachesItem);
        }

        $em->flush();
        return new Response(1);
    }

    public function tachesActsAction(TachesItem $tachesItem, Dossier $dossier = null)
    {
        /** @var TachesAction[] $tachesActions */
        $tachesActions = [];

        /** @var TachesAction[] $tachesActionsTemps */
        $tachesActionsTemps = $this->getDoctrine()->getRepository('AppBundle:TachesAction')
            ->createQueryBuilder('ta')
            ->where('ta.tachesItem = :tachesItem')
            ->setParameter('tachesItem',$tachesItem)
            ->getQuery()
            ->getResult();

        if ($dossier)
        {
            foreach ($tachesActionsTemps as $tachesActionsTemp)
            {
                $isValidToDossier = $this->getDoctrine()->getRepository('AppBundle:TachesDate')
                    ->tachesActionIsToDossier($tachesActionsTemp,$dossier);

                if ($isValidToDossier) $tachesActions[] = $tachesActionsTemp;
            }
        }
        else $tachesActions = $tachesActionsTemps;

        return $this->render('@Tache/Taches/taches-actions.html.twig',[
            'tachesActions' => $tachesActions,
            'tachesItem' => $tachesItem,
            'dossier' => $dossier
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function editTachesActAction(Request $request)
    {
        /**
         * @var int $action
         *  0: show edit
         *  1: edit
         *  2: delete
         */
        $action = intval($request->request->get('action'));
        /** @var TachesItem $tachesItem */
        $tachesItem = $this->getDoctrine()->getRepository('AppBundle:TachesItem')
            ->find($request->request->get('taches_item'));
        /** @var TachesAction $tachesAction */
        $tachesAction = $this->getDoctrine()->getRepository('AppBundle:TachesAction')
            ->find($request->request->get('tache_action'));
        /** @var TacheListeAction[] $tacheListeActions */
        $tacheListeActions = ($tachesItem) ? $this->getDoctrine()->getRepository('AppBundle:TachesAction')
            ->getResteTacheListeAction($tachesItem,$tachesAction) : [];
        /** @var TacheListeAction $tacheListeAction */
        $tacheListeAction = $this->getDoctrine()->getRepository('AppBundle:TacheListeAction')
            ->find($request->request->get('tache_liste_action'));
        $em = $this->getDoctrine()->getManager();

        if ($action == 0)
        {
            return $this->render('@Tache/Taches/taches-action-edit.html.twig', [
                'tachesItem' => $tachesItem,
                'tachesAction' => $tachesAction,
                'tacheListeActions' => $tacheListeActions
            ]);
        }
        elseif ($action == 1)
        {
            $add = false;
            if (is_null($tachesAction))
            {
                $tachesAction = new TachesAction();
                $tachesAction->setTachesItem($tachesItem);
                $add = true;
            }

            $tachesAction
                ->setTacheListeAction($tacheListeAction);

            if ($add) $em->persist($tachesAction);
        }
        elseif ($action == 2)
        {
            if ($tachesAction) $em->remove($tachesAction);
        }

        $em->flush();
        return new Response(1);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function tachesActCodeAction(Request $request)
    {
        $tachesAction = $this->getDoctrine()->getRepository('AppBundle:TachesAction')
            ->find($request->request->get('tache_action'));

        $tachesAction->setCode($request->request->get('code'));

        $this->getDoctrine()->getManager()->flush();

        return new Response(1);
    }

    public function tachesDatesAction(Request $request = null, TachesAction $tachesAction = null, Dossier $dossier = null, $isRequest = 1)
    {
        if (!$tachesAction)
            $tachesAction = $this->getDoctrine()->getRepository('AppBundle:TachesAction')
                ->find($request->request->get('taches_action'));

        if (!$dossier && $isRequest == 1)
            $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
                ->find($request->request->get('dossier'));

        $tachesDates = $this->getDoctrine()->getRepository('AppBundle:TachesDate')
            ->createQueryBuilder('td')
            ->where('td.tachesAction = :tachesAction')
            ->setParameter('tachesAction',$tachesAction);

        if ($dossier)
            $tachesDates = $tachesDates
                ->andWhere('(td.dossier IS NULL OR td.dossier = :dossier)')
                ->setParameter('dossier',$dossier);
        else
            $tachesDates = $tachesDates->andWhere('td.dossier IS NULL');

        /** @var TachesDate[] $tachesDatesTemps */
        $tachesDatesTemps = $tachesDates
            ->getQuery()
            ->getResult();

        /** @var TachesDate[] $tachesDates */
        $tachesDates = [];

        /** @var TachesEntity[] $tachesEntitys */
        $tachesEntitys = [];
        $champsFiscalInfoPerdos = null;
        $idFiscalInfoPerdos = -1;
        $valFiscalInfoPerdos = null;

        if ($dossier)
        {
            foreach ($tachesDatesTemps as $tachesDatesTemp)
            {
                $clotures = json_decode($tachesDatesTemp->getClotures());
                if (in_array($dossier->getCloture(),$clotures))
                    $tachesDates[] = $tachesDatesTemp;
            }

            foreach ($tachesDates as $tachesDate)
            {
                $tachesEntity = $this->getDoctrine()->getRepository('AppBundle:TachesEntity')
                    ->getTachesEntity($tachesDate,$dossier,false);

                $tachesEntitys[$tachesDate->getId()] = $tachesEntity;
            }

            if($tachesAction && $tachesAction->getTachesItem() && $tachesAction->getTachesItem()->getTaches()){
                $champsFiscalInfoPerdos = $tachesAction->getTachesItem()->getTaches()->getPrestationFiscaleTache()->getChamps();
                if($champsFiscalInfoPerdos){
                    $champsFiscalInfoPerdos = str_replace('prestation.', '', $champsFiscalInfoPerdos);
                    $query = "SELECT ".$champsFiscalInfoPerdos.", id FROM prestation_fiscale where dossier_id = ".$dossier->getId();
                    $prep  = $this->pdo->query($query);
                    $datas = $prep->fetchAll(\PDO::FETCH_ASSOC); 
                    if( count($datas) > 0 ){
                        $valFiscalInfoPerdos = $datas[0][$champsFiscalInfoPerdos];
                    }   $idFiscalInfoPerdos = $datas[0]['id'];
                }
            }
        }
        else $tachesDates = $tachesDatesTemps;

        return $this->render('@Tache/Taches/taches-dates.html.twig',[
            'tachesDates' => $tachesDates,
            'dossier' => $dossier,
            'tachesEntitys' => $tachesEntitys,
            'champsFiscalInfoPerdos' => $champsFiscalInfoPerdos,
            'valFiscalInfoPerdos' => $valFiscalInfoPerdos
        ]);
    }

    public function editTachesDateAction(Request $request)
    {
        /**
         * @var int $action
         *  0: show edit
         *  1: edit
         *  2: delete
         */
        $action = intval($request->request->get('action'));
        /** @var TachesAction $tachesAction */
        $tachesAction = $this->getDoctrine()->getRepository('AppBundle:TachesAction')
            ->find($request->request->get('taches_action'));
        /** @var TachesDate $tachesDate */
        $tachesDate = $this->getDoctrine()->getRepository('AppBundle:TachesDate')
            ->find($request->request->get('taches_date'));
        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));

        $em = $this->getDoctrine()->getManager();

        if ($action == 0)
        {
            $clotures = [];
            if (!is_null($tachesDate))
                $clotures = json_decode($tachesDate->getClotures());
            $mois = ['Jan','Fév','Mar','Avr','Mai','Jui','Jul','Aou','Sep','Oct','Nov','Dec'];
            $cloturesUseds = $this->getDoctrine()->getRepository('AppBundle:TachesDate')
                ->getMoisUsed($tachesAction,$tachesDate,$dossier);
            return $this->render('@Tache/Taches/taches-date-edit.html.twig', [
                'mois' => $mois,
                'tachesAction' => $tachesAction,
                'tachesDate' => $tachesDate,
                'clotures' => $clotures,
                'cloturesUseds' => $cloturesUseds,
                'dossier' => $dossier
            ]);
        }
        elseif ($action == 1)
        {
            $add = false;
            if (is_null($tachesDate))
            {
                $tachesDate = new TachesDate();
                $tachesDate
                    ->setTachesAction($tachesAction)
                    ->setDossier($dossier);
                $add = true;
            }

            $tachesDate
                ->setClotures($request->request->get('clotures'))
                ->setFormule($request->request->get('formule'))
                ->setInfoperdos(intval($request->request->get('is_infoperdos')));

            if ($add) $em->persist($tachesDate);
        }
        elseif ($action == 2)
        {
            if ($tachesDate) $em->remove($tachesDate);
        }

        $em->flush();
        $status = 0;
        if (($action === 1 || $action === 2) && $dossier)
        {
            $status = $this->getDoctrine()->getRepository('AppBundle:Taches')
                ->getStatusForDossier($tachesAction->getTachesItem()->getTaches(),$dossier);
        }
        return new Response($status);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function tachesActLibelleAction(Request $request)
    {
        $tachesAction = $this->getDoctrine()->getRepository('AppBundle:TachesAction')
            ->find($request->request->get('tache_action'));

        $tachesAction->setLibelle($request->request->get('libelle'));

        $this->getDoctrine()->getManager()->flush();

        return new Response(1);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function tachesActDescriptionAction(Request $request)
    {
        $tachesAction = $this->getDoctrine()->getRepository('AppBundle:TacheListeAction')
            ->find($request->request->get('tache_liste_action'));

        $tachesAction->setNom($request->request->get('libelle'));

        $this->getDoctrine()->getManager()->flush();

        return new Response(1);
    }
}
