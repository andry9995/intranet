<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 04/09/2018
 * Time: 09:02
 */

namespace TacheBundle\Controller;


use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Tache;
use AppBundle\Entity\TacheEntity;
use AppBundle\Entity\TacheEntityLegaleAction;
use AppBundle\Entity\TacheEntityLibreAction;
use AppBundle\Entity\TacheLegale;
use AppBundle\Entity\TacheLegaleAction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TacheAdminController extends Controller
{
    /**
     * @return Response
     */
    public function indexClientAction()
    {
        return $this->index();
    }

    /**
     * @return Response
     */
    public function indexDossierAction()
    {
        return $this->index(1);
    }

    /**
     * @param int $entity
     * @return Response
     */
    public function index($entity = 0)
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Tache')
            ->listeClient();

        return $this->render('@Tache/TacheAdmin/index.html.twig', ['clients'=>$clients, 'entity'=>$entity]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function tachesAction(Request $request)
    {
        $type = intval($request->request->get('type'));
        /** @var Client[] $clients */
        $clients = [];
        /** @var Dossier[] $dossiers */
        $dossiers = [];

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
        /** @var TacheLegale[] $tachesLegales */
        $tachesLegales = $this->getDoctrine()->getRepository('AppBundle:TacheLegale')->getAllTacheWithChild();
        $headGroup[] = colModel::getGroupModel('t_lg_'.$tachesLegales[0]->getId(),count($tachesLegales),'Tache Légale');

        /** @var Tache[] $tacheLibres */
        $tacheLibres = $this->getDoctrine()->getRepository('AppBundle:Tache')->getAllTache();
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

                foreach ($tachesLegales as $tachesLegale)
                {
                    if ($isfirst)
                    {
                        $colNames[] = $tachesLegale->getNom();
                        $colModels[] = colModel::getModel('t_lg_'.$tachesLegale->getId(),true,$widthTache,'cl_t_cell legale','checkbox','center',false);
                    }

                    $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                        ->getByClientDossier($tachesLegale,null,$cl,null);
                    $data['t_lg_'.$tachesLegale->getId()] = (is_null($tacheEntity) ? 0 : 1);
                }
                foreach ($tacheLibres as $tacheLibre)
                {
                    if ($isfirst)
                    {
                        $colNames[] = $tacheLibre->getNom();
                        $colModels[] = colModel::getModel('t_lb_'.$tacheLibre->getId(),true,$widthTache,'cl_t_cell','checkbox','center',false);
                    }

                    $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                        ->getByClientDossier(null,$tacheLibre,$cl,null);
                    $data['t_lb_'.$tacheLibre->getId()] = (is_null($tacheEntity) ? 0 : 1);
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

                    $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                        ->getByClientDossier($tachesLegale,null,null,$dos);

                    $s = (is_null($tacheEntity) ? 0 : 1);

                    $cloturesValides = $tachesLegale->getDateCloture();
                    $regimesFiscalsValides = $tachesLegale->getRegimeFiscal();
                    $formeActivitesValides = $tachesLegale->getFormeActivite();
                    $formeJuridiquesValides = $tachesLegale->getFormeJuridique();
                    if (
                        count($cloturesValides) > 0 && !in_array($dos->getCloture(),$tachesLegale->getDateCloture()) ||
                        count($regimesFiscalsValides) > 0 && !is_null($dos->getRegimeFiscal()) && !in_array($dos->getRegimeFiscal()->getId(),$regimesFiscalsValides) ||
                        count($formeActivitesValides) > 0 && !is_null($dos->getFormeActivite()) && !in_array($dos->getFormeActivite()->getId(),$formeActivitesValides) ||
                        count($formeJuridiquesValides) > 0 && !is_null($dos->getFormeJuridique()) && !in_array($dos->getFormeJuridique()->getId(),$formeJuridiquesValides)
                    ) $s = -1;

                    if (is_null($tacheEntity) && $s != -1)
                    {
                        $tacheEntityClient = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                            ->getByClientDossier($tachesLegale,null,$dos->getSite()->getClient(),null);

                        if (!is_null($tacheEntityClient))
                        {
                            $em = $this->getDoctrine()->getManager();
                            $tacheEntity = new TacheEntity();
                            $tacheEntity
                                ->setTacheEntity($tacheEntityClient)
                                ->setTacheLegale($tachesLegale)
                                ->setDossier($dos);

                            $s = 1;
                            $em->persist($tacheEntity);
                            $em->flush();
                        }
                    }
                    if (!is_null($tacheEntity) && $tacheEntity->getDesactiver() == 1) $s = 0;

                    $data['t_lg_'.$tachesLegale->getId()] = $s;
                }
                foreach ($tacheLibres as $tacheLibre)
                {
                    if ($isfirst)
                    {
                        $colNames[] = $tacheLibre->getNom();
                        $colModels[] = colModel::getModel('t_lb_'.$tacheLibre->getId(),true,$widthTache,'cl_t_cell','checkbox','center',false);
                    }

                    $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                        ->getByClientDossier(null,$tacheLibre,null,$dos);

                    if (is_null($tacheEntity))
                    {
                        $tacheEntityClient = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                            ->getByClientDossier(null,$tacheLibre,$dos->getSite()->getClient(),null);

                        if (!is_null($tacheEntityClient))
                        {
                            $em = $this->getDoctrine()->getManager();
                            $tacheEntity = new TacheEntity();
                            $tacheEntity
                                ->setTacheEntity($tacheEntityClient)
                                ->setTache($tacheLibre)
                                ->setDossier($dos);

                            $em->persist($tacheEntity);
                            $em->flush();
                        }
                    }

                    $s = ((is_null($tacheEntity) || (!is_null($tacheEntity) && $tacheEntity->getDesactiver() == 1)) ? 0 : 1);
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

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showImputationTacheAction(Request $request)
    {
        $typeEntity = intval($request->request->get('type_entity'));
        $typeTache = intval($request->request->get('type_tache'));
        $entity = intval($request->request->get('entity'));
        /** @var Client $client */
        $client = null;
        /** @var Dossier $dossier */
        $dossier = null;

        if ($typeEntity == 0) $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($entity);
        else $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($entity);

        if ($typeTache == 0)
        {
            /** @var TacheLegale $tacheLegale */
            $tacheLegale = $this->getDoctrine()->getRepository('AppBundle:TacheLegale')->find($request->request->get('tache'));

            /** @var TacheEntity $tacheEntity */
            $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                ->getByClientDossier($tacheLegale,null,$client,$dossier);

            /** @var TacheLegaleAction[] $tacheLegaleActions */
            $tacheLegaleActions = $this->getDoctrine()->getRepository('AppBundle:TacheLegaleAction')->getByTache($tacheLegale);

            $tacheEntityLegaleActions = [];
            foreach ($tacheLegaleActions as $tacheLegaleAction)
            {
                $tacheEntityLegaleActions[] = (object)
                [
                    'tacheLegaleAction' => $tacheLegaleAction,
                    'tacheEntityLegaleAction' => $this->getDoctrine()->getRepository('AppBundle:TacheEntityLegaleAction')->getByTacheEntityTacheLegaleAction($tacheLegaleAction,$tacheEntity)
                ];
            }

            /** @var TacheEntity $tacheEntityClient */
            $tacheEntityClient = ($typeEntity == 1) ?
                $this->getDoctrine()->getRepository('AppBundle:TacheEntity')->getByClientDossier($tacheLegale,null,$dossier->getSite()->getClient(),null):
                null;

            return $this->render('@Tache/TacheAdmin/tache-legale-admin.html.twig',
                [
                    'tacheEntity' => $tacheEntity,
                    'tacheEntityLegaleActions'=>$tacheEntityLegaleActions,
                    'tacheLegale' => $tacheLegale,
                    'client' => $client,
                    'dossier' => $dossier,
                    'tacheEntityClient' => $tacheEntityClient
                ]);
        }
        elseif ($typeTache == 1)
        {
            /** @var Tache $tache */
            $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')->find($request->request->get('tache'));

            /** @var TacheEntity $tacheEntity */
            $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                ->getByClientDossier(null,$tache,$client,$dossier);

            /** @var TacheEntityLibreAction $tacheEntityLibreAction */
            $tacheEntityLibreAction = (is_null($tacheEntity)) ? null : $this->getDoctrine()->getRepository('AppBundle:TacheEntityLibreAction')
                ->getByTacheEntity($tacheEntity);

            /** @var TacheEntity $tacheEntityClient */
            $tacheEntityClient = ($typeEntity == 1) ?
                $this->getDoctrine()->getRepository('AppBundle:TacheEntity')->getByClientDossier(null,$tache,$dossier->getSite()->getClient(),null):
                null;

            return $this->render('@Tache/TacheAdmin/tache-libre-admin.html.twig',
                [
                    'tacheEntity' => $tacheEntity,
                    'tacheEntityLibreAction' => $tacheEntityLibreAction,
                    'tache' => $tache,
                    'client' => $client,
                    'dossier' => $dossier,
                    'tacheEntityClient' => $tacheEntityClient
                ]);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function saveTacheEntityLgAction(Request $request)
    {
        $tacheAParametre = json_decode($request->request->get('tache'));
        $tacheLegale = $this->getDoctrine()->getRepository('AppBundle:TacheLegale')->find($tacheAParametre->tache_legale);
        $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')->find($tacheAParametre->tache_legale_entity);
        $nbCocher = intval($tacheAParametre->nb_cocher);
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($tacheAParametre->client);
        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($tacheAParametre->dossier);
        $heriter = intval($tacheAParametre->heriter);
        $action = intval($tacheAParametre->action);

        $em = $this->getDoctrine()->getManager();
        /** @var int $status (0:non cocher, 1:cocher) */
        $status = 0;

        if (($nbCocher == 0 && $heriter == 0) || $action == 2)
        {
            if (!is_null($tacheEntity))
            {
                if (is_null($tacheEntity->getDossier())) $em->remove($tacheEntity);
                else
                {
                    $tacheEntityClient = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                        ->getByClientDossier($tacheLegale,null,$dossier->getSite()->getClient(),null);

                    if (is_null($tacheEntityClient)) $em->remove($tacheEntity);
                    else $tacheEntity->setDesactiver(1);
                }
            }
        }
        else
        {
            $status = 1;
            if (is_null($tacheEntity))
            {
                $tacheEntity = new TacheEntity();
                $tacheEntity
                    ->setDossier($dossier)
                    ->setClient($client)
                    ->setTacheLegale($tacheLegale);

                $em->persist($tacheEntity);
                $em->flush();
            }

            if ($heriter == 1)
            {
                $tacheEntityClient = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                    ->getByClientDossier($tacheLegale,null,$dossier->getSite()->getClient(),null);

                $tacheEntity
                    ->setTacheEntity($tacheEntityClient);
            }
            else $tacheEntity->setTacheEntity(null);

            $tacheEntity->setDesactiver(0);

            if ($heriter == 0)
            {
                foreach ($tacheAParametre->taches_action as $item)
                {
                    $tacheEntityLegaleAction = $this->getDoctrine()->getRepository('AppBundle:TacheEntityLegaleAction')->find($item->tache_action_old);
                    if (intval($item->cocher) == 1)
                    {
                        $add = false;
                        if (is_null($tacheEntityLegaleAction))
                        {
                            $tacheLegaleAction = $this->getDoctrine()->getRepository('AppBundle:TacheLegaleAction')->find($item->tache_action);
                            $tacheEntityLegaleAction = new TacheEntityLegaleAction();
                            $tacheEntityLegaleAction->setTacheLegaleAction($tacheLegaleAction);
                            $add = true;
                        }
                        $tacheEntityLegaleAction
                            ->setJourAdditif($item->jour_add)
                            ->setResponsable($item->responsable)
                            ->setTacheEntity($tacheEntity);

                        if ($add) $em->persist($tacheEntityLegaleAction);
                    }
                    elseif (!is_null($tacheEntityLegaleAction)) $em->remove($tacheEntityLegaleAction);
                }
            }
        }
        $em->flush();

        return new Response($status);
        //return $this->render('@Tache/TacheAdmin/test.html.twig',['test'=>$status]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function saveTacheEntityLbAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var TacheEntity $tacheEntity */
        $tacheEntity = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')->find($request->request->get('tache_entity'));
        $action = intval($request->request->get('action'));

        if ($action == 2 && !is_null($tacheEntity))
        {
            if (is_null($tacheEntity->getDossier())) $em->remove($tacheEntity);
            else
            {
                $tacheEntityClient = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                    ->getByClientDossier(null,$tacheEntity->getTache(),$tacheEntity->getDossier()->getSite()->getClient(),null);

                if (is_null($tacheEntityClient)) $em->remove($tacheEntity);
                else $tacheEntity->setDesactiver(1);
            }

            $em->flush();
            return new Response(0);
        }

        $heriter = intval($request->request->get('heriter'));

        $periode = intval($request->request->get('periode'));
        $demarage = null;
        $aPartirDe = intval($request->request->get('a_partir_de'));
        $dateCalcul = null;
        $jour = intval($request->request->get('jour'));
        $responsable = intval($request->request->get('responsable'));
        $jalon = intval($request->request->get('jalon'));

        //test Date format
        if ($periode != 0 && trim($request->request->get('demarage')) != '')
        {
            $demarage = \DateTime::createFromFormat('d/m/Y',trim($request->request->get('demarage')));
             if(!is_object($demarage))
                 return new Response(-1);
        }
        if ($periode == 0 || ($periode != 0 && $aPartirDe == 3))
        {
            $dateCalcul = \DateTime::createFromFormat('d/m/Y',$request->request->get('date_calcul'));
            if(!is_object($dateCalcul))
                return new Response(-2);
        }

        $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')->find($request->request->get('tache'));
        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->find($request->request->get('dossier'));
        if (is_null($tacheEntity))
        {
            $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($request->request->get('client'));
            $tacheEntity = new TacheEntity();
            $tacheEntity
                ->setTache($tache)
                ->setClient($client)
                ->setDossier($dossier);

            $em->persist($tacheEntity);
            $em->flush();
        }

        if ($heriter == 1)
        {
            $tacheEntityClient = $this->getDoctrine()->getRepository('AppBundle:TacheEntity')
                ->getByClientDossier(null,$tache,$dossier->getSite()->getClient(),null);

            $tacheEntity->setTacheEntity($tacheEntityClient);
        }
        else $tacheEntity->setTacheEntity(null);

        if ($heriter === 0)
        {
            $tacheEntityLibreAction = $this->getDoctrine()->getRepository('AppBundle:TacheEntityLibreAction')
                ->getByTacheEntity($tacheEntity);

            $add = false;
            if (is_null($tacheEntityLibreAction))
            {
                $tacheEntityLibreAction = new TacheEntityLibreAction();
                $add = true;
            }

            $tacheEntityLibreAction
                ->setPeriode($periode)
                ->setResponsable($responsable)
                ->setTacheEntity($tacheEntity)
                ->setJalon($jalon)
                ->setDemarrage($demarage)
                ->setCalculerAPartir($aPartirDe)
                ->setDateCalcul($dateCalcul)
                ->setJour($jour);

            if ($add) $em->persist($tacheEntityLibreAction);
        }

        $tacheEntity->setDesactiver(0);
        $em->flush();
        return new Response(1);
    }
}