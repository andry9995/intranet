<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 10/01/2019
 * Time: 09:23
 */

namespace RevisionBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\GoogleCalendarConfig;
use AppBundle\Entity\Tache;
use AppBundle\Entity\TachePrioriteDossier;
use AppBundle\Entity\TachesPrioriteDossier;
use AppBundle\Entity\TachesSynchro;
use AppBundle\Entity\TachesSynchroMoov;
use AppBundle\Functions\GoogleCalendar;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Agenda3Controller extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var Client[] $clients */
        $clients = $this->getDoctrine()->getRepository('AppBundle:Client')->getAllClient();

        $tacheColorDefault = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->defaultTacheColor();
        return $this->render('@Revision/Agenda3/index.html.twig',[
            'clients'=>$clients,
            'tacheColorDefault' => $tacheColorDefault
        ]);
    }

    /**
     * @param Request $request
     * @param $periode
     * @return JsonResponse|Response
     */
    public function tachesCalendarAction(Request $request,$periode)
    {
        $clientsIds = $request->query->get('clients');
        /** @var Client[] $clients */
        $clients = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids',$clientsIds)
            ->orderBy('c.nom')
            ->getQuery()->getResult();
        $periode = \DateTime::createFromFormat('Y-m-d',$periode);

        $isLegale = (intval($request->query->get('legale')) == 1);
        $isLibre = (intval($request->query->get('libre')) == 1);
        $isFaite = (intval($request->query->get('faite')) == 1);

        $isScriptura = (intval($request->query->get('scriptura')) == 1);
        $isEc = (intval($request->query->get('ec')) == 1);
        $isCf = (intval($request->query->get('cf')) == 1);

        $events = $this->getDoctrine()->getRepository('AppBundle:Calendar')
            ->taches3EventsNoUpdates($clients,$periode,
                $isLegale,$isLibre,$isFaite,
                $isScriptura,$isEc,$isCf);

        /*$events = $this->getDoctrine()->getRepository('AppBundle:Calendar')
            ->taches3Events($clients,$periode);*/
        /*return $this->render('@Tache/TacheAdmin/test.html.twig',['test'=>$events]);*/

        return new JsonResponse($events);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tacheMoovAction(Request $request)
    {
        $googleId = $request->request->get('google_id');
        $googleIdSave = $googleId;
        $resteId = $request->request->get('reste_id');

        /** @var TachesSynchro $tachesSynchro */
        $tachesSynchro = $this->getDoctrine()->getRepository('AppBundle:TachesSynchro')
            ->find($request->request->get('taches_synchro_id'));

        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));

        $newDate = \DateTime::createFromFormat('Y-m-d',$request->request->get('new_date'));
        $newDate->setTime(0,0,0);
        $dateNow = new \DateTime();
        $dateNow->setTime(0,0,0);

        $em = $this->getDoctrine()->getManager();
        /** @var TachesSynchroMoov $tachesSynchroMoov */
        $tachesSynchroMoov = null;
        if ($tachesSynchro)
        {
            if ($tachesSynchro->getDate()->format('Ymd') == $newDate->format('Ymd'))
            {
                $this->getDoctrine()->getRepository('AppBundle:TachesSynchroMoov')
                    ->removeAllItem($tachesSynchro);
            }
            else
            {
                $tachesSynchroMoov = new TachesSynchroMoov();
                $tachesSynchroMoov
                    ->setDate($newDate)
                    ->setOperateur($this->getUser())
                    ->setTachesSynchro($tachesSynchro);
                $em->persist($tachesSynchroMoov);
            }
        }
        $em->flush();

        /** @var \Google_Service_Calendar_Event $eventUpdated */
        $eventUpdated = null;
        if ($googleId != 'NONE')
        {
            /** @var GoogleCalendarConfig $config */
            $config = $this->getDoctrine()
                ->getRepository('AppBundle:GoogleCalendarConfig')
                ->getConfig($dossier->getSite()->getClient());

            if ($config)
            {
                if ($tachesSynchro && $config->isSendToGoogle() || !$tachesSynchro)
                {
                    $calendar = new GoogleCalendar();
                    $calendar->setConfig($config);
                    $eventUpdated = $calendar->updateDateEvent($googleId,$newDate);
                    if ($tachesSynchro) $tachesSynchro->setIdGoogle($eventUpdated->getId());
                    $googleId = $eventUpdated->getId();
                }
            }
        }

        $em->flush();

        $tachePrioriteDossier = $this->getDoctrine()->getRepository('AppBundle:TachesPrioriteDossier')
            ->getPrioriteDossier($dossier);

        $em = $this->getDoctrine()->getManager();
        $update = false;
        if ($tachePrioriteDossier)
        {
            if ($tachesSynchro)
            {
                if ($tachePrioriteDossier->getTachesSynchro())
                {
                    if ($tachesSynchro->getId() == $tachePrioriteDossier->getTachesSynchro()->getId()) $update = true;
                    else
                    {
                        if ($newDate < $tachePrioriteDossier->getDate() &&
                            $tachePrioriteDossier->getDate() >= $dateNow)
                        {
                            $tachePrioriteDossier
                                ->setDateCalcul($dateNow)
                                ->setDate($newDate)
                                ->setTachesSynchro($tachesSynchro)
                                ->setGoogleId(null);
                        }
                    }
                }
            }
            else
            {
                if ($googleIdSave == $tachePrioriteDossier->getGoogleId()) $update = true;
                else
                {
                    if ($newDate < $tachePrioriteDossier->getDate() &&
                        $tachePrioriteDossier->getDate() >= $dateNow)
                    {
                        $tachePrioriteDossier
                            ->setDateCalcul($dateNow)
                            ->setDate($newDate)
                            ->setTachesSynchro(null)
                            ->setGoogleId($googleId);
                    }
                }
            }
        }
        else
        {
            if ($newDate >= $dateNow)
            {
                $tachePrioriteDossier = new TachesPrioriteDossier();
                $tachePrioriteDossier
                    ->setDateCalcul($dateNow)
                    ->setDate($newDate)
                    ->setTachesSynchro($tachesSynchro)
                    ->setGoogleId($tachesSynchro ? null : $googleId);
                $em->persist($tachePrioriteDossier);
            }
        }
        if ($update) $this->getDoctrine()->getRepository('AppBundle:TachesPrioriteDossier')
            ->updatePriotiteDossier($dossier);
        $em->flush();

        return new Response(($eventUpdated) ? $eventUpdated->getId() : ('NONE'.$resteId));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function eventByDateAction(Request $request)
    {
        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));

        /** @var TachesSynchro $tachesSynchro */
        $tachesSynchro = $this->getDoctrine()->getRepository('AppBundle:TachesSynchro')
            ->find($request->request->get('taches_syncho_id'));
        $googleId = $request->request->get('google_id');

        $nomTache = '';
        $date = \DateTime::createFromFormat('Y-m-d',$request->request->get('date'));
        /** @var Tache $taches */
        $tache = null;
        $isDepasser = false;
        if ($tachesSynchro)
        {
            if ($tachesSynchro->getTachesDate())
            {
                $nomTache = $tachesSynchro->getTachesDate()->getTachesAction()->getTacheListeAction()->getNom();
                $nomTache .= ' - ' . $tachesSynchro->getTachesDate()->getTachesAction()->getTachesItem()->getTaches()->getNom();
            }
            else
            {
                $tachesLibre = $tachesSynchro->getTachesLibreDate()->getTachesLibre();
                if ($tachesLibre->getTachesLibre()) $tachesLibre = $tachesLibre->getTachesLibre();
                $nomTache = $tachesLibre->getTache()->getNom();
            }
            $fait = ($tachesSynchro->getStatus() == 1);
            $dateFait = $tachesSynchro->getDatefait();
            if($dateFait != null){
                $df = explode("-", $dateFait->format('Y-m-d')); 
                $dateFait = $df[0].$df[1].$df[2]; 
                $de = explode("-", $date->format('Y-m-d')); 
                $dateEch = $de[0].$de[1].$de[2]; 
                if($dateFait > $dateEch)
                    $isDepasser = true;
            }
        }
        else
        {
            $titleSpliters = explode('+',$request->request->get('title'));
            /** @var Tache $tache */
            $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')
                ->createQueryBuilder('t')
                ->where('t.nom = :nom')
                ->setParameter('nom',$titleSpliters[0])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $fait = false;
            if (count($titleSpliters) > 2)
                $fait = substr(strtoupper(trim($titleSpliters[2])),0,4) == 'FAIT';

            if ($tache) $nomTache = $tache->getNom();
        }
        $detailTacheData = $this->getDetailTache($dossier, $date);
        $colorRb = '';
        $colorOb = '';
        $colorRappro = '';
        foreach ($detailTacheData as $key => $value) {
            if ($value['rb2'] == 'Imp.') {
                if ($value['ecart'] == 0) {
                    $colorRb = '#008000';
                }else{
                    $colorRb = '#ffd700';
                }
                if ($value['acontroler'] > 0 || $value['m'] == 'Inc.') {
                    $colorRb = '#e95443';
                }
            }else{
                $colorRb = '#e95443';
            }
            $detailTacheData[$key]['colorRb'] = $colorRb;
            if($value['ob'] == 'PB'){
                $colorOb = '#e95443';
            }else{
                $colorOb = '#008000';
            }
            $detailTacheData[$key]['colorOb'] = $colorOb;
            if($value['nbr_rapproche'] == 100){
                $colorRappro = '#008000';
            }
            if($value['m'] == 'Inc.' || $value['ob'] == 'PB'){
                $colorRappro = '#e95443';
            }else{
                $colorRappro = '#ffd700';
            }
            $detailTacheData[$key]['colorRappro'] = $colorRappro;
        }

        return $this->render('@Revision/Agenda3/taches-synchro.html.twig',[
            'nomTache' => $nomTache,
            'dossier' => $dossier,
            'date' => $date,
            'tachesSynchro' => $tachesSynchro,
            'googleId' => $googleId,
            'fait' => $fait,
            'detailsTaches' => $detailTacheData,
            'isDepasser' => $isDepasser
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function eventsDayAction(Request $request)
    {
        /** @var Client[] $clients */
        $clients = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids',json_decode($request->request->get('clients')))
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
        $events = json_decode($request->request->get('events'));
        $date = \DateTime::createFromFormat('Y-m-d',$request->request->get('date'));

        $tachesInDays = [];
        foreach ($events as $event)
        {
            /** @var TachesSynchro $tachesSynchro */
            $tachesSynchro = $this->getDoctrine()->getRepository('AppBundle:TachesSynchro')
                ->find($event->taches_syncho_id);
            $google_id = $event->google_id;
            /** @var Dossier $dossier */
            $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
                ->find($event->dossier);

            $nomTache = '';
            $tache = null;
            if ($tachesSynchro)
            {
                if ($tachesSynchro->getTachesDate())
                {
                    $nomTache = $tachesSynchro->getTachesDate()->getTachesAction()->getTacheListeAction()->getNom();
                    $nomTache .= ' - ' . $tachesSynchro->getTachesDate()->getTachesAction()->getTachesItem()->getTaches()->getNom();
                }
                else
                {
                    $tache = $tachesSynchro->getTachesLibreDate()->getTachesLibre()->getTache();
                    $tachesLibre = $tachesSynchro->getTachesLibreDate()->getTachesLibre();
                    if ($tachesLibre->getTachesLibre()) $tachesLibre = $tachesLibre->getTachesLibre();
                    $nomTache = $tachesLibre->getTache()->getNom();
                }

                $fait = ($tachesSynchro->getStatus() == 1);
            }
            else
            {
                $titleSpliters = explode(':',$event->title);
                /** @var Tache $tache */
                $tache = $this->getDoctrine()->getRepository('AppBundle:Tache')
                    ->createQueryBuilder('t')
                    ->where('t.nom = :nom')
                    ->setParameter('nom',$titleSpliters[1])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($tache) $nomTache = $tache->getNom();

                $fait = false;
                if (count($titleSpliters) > 2)
                    $fait = substr(strtoupper(trim($titleSpliters[2])),0,4) == 'FAIT';
            }

            $tachesInDays[] = (object)
            [
                'tachesSynchro' => $tachesSynchro,
                'google_id' => $google_id,
                'dossier' => $dossier,
                'date' => \DateTime::createFromFormat('Y-m-d',$event->date),
                'title' => $event->title,
                'nomTache' => $nomTache,
                'fait' => $fait,
                'tache' => $tache
            ];
        }

        $tacheLibres = $this->getDoctrine()->getRepository('AppBundle:Tache')
            ->getAllTache();

        return $this->render('@Revision/Agenda3/taches-in-day.html.twig',[
            'tachesInDays' => $tachesInDays,
            'clients' => $clients,
            'tacheLibres' => $tacheLibres,
            'date' => $date
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function dossiersAction(Request $request)
    {
        /** @var Client $client */
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->find($request->request->get('client'));

        /** @var Dossier[] $dossiers */
        $dossiers = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->getDossierByClient($client);

        return $this->render('@Revision/Agenda3/dossiers.html.twig',['dossiers'=>$dossiers]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function marquerFaitAction(Request $request)
    {
        $events = json_decode($request->request->get('events'));
        foreach ($events as $event)
        {
            /** @var TachesSynchro $tachesSynchro */
            $tachesSynchro = $this->getDoctrine()->getRepository('AppBundle:TachesSynchro')
                ->find($event->tachesSynchro);
            $googleId = $event->google_id;
            /** @var Dossier $dossier */
            $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
                ->find($event->dossier);
            $nomTache = trim($event->nom_tache);
            $status = intval($event->status);

            /** @var \DateTime $date */
            $date = null;
            /** @var \DateTime $dateFait */
            $dateFait = null;
            if($event->dateFait != null){
                $datFait = explode('/', $event->dateFait);
                $datFait = $datFait[2].'-'.$datFait[1].'-'.$datFait[0];
                $dateFait = \DateTime::createFromFormat('Y-m-d',$datFait);
            }
            if (intval($googleId) == -1) $date = \DateTime::createFromFormat('Y-m-d',$event->date);

            $this->getDoctrine()->getRepository('AppBundle:TachesSynchro')
                ->marquerFait($dossier,$tachesSynchro,$googleId,$nomTache,$status,$date,$dateFait);
        }
        return new Response(1);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function clientColorAction(Request $request)
    {
        $color = $request->request->get('new_bg');
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->find($request->request->get('client'));

        $client->setTacheColor($color);
        $this->getDoctrine()->getManager()->flush();
        return new Response(1);
    }

    public function getDetailTache($dossier , $date){
        $param = [];
        $param['client'] = 0;
        $param['dossier'] = $dossier->getId();
        $param['exercice'] = $date->format('Y');

        $data = $this->getDoctrine()->getRepository('AppBundle:Image')
            ->getListeImpute($param);

        $dateEnvoi = '';
        $derniereDemande = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getDerniereDemandeDrt($param['dossier'], $param['exercice']);
        if(count($derniereDemande) > 0){
            $dateEnvoi = $derniereDemande[0]->date_envoi;
        }
        if(count($data['imputees'] > 0)){
            $tab_imputees = array();
            $tab_key_mois = array();
            $tab_exist_comptes = array();
            $last_key = 0;
            $exercice = $param['exercice'];
            $dossier = $param['dossier'];
            $client = $param['client'];
            $betweens = array();
            foreach ($data['imputees'] as $key => $value) {
               if (!empty($value->mois)) {
                    $tab_mois_manquant = explode(',', $value->mois);
                    $mois_manquant = str_replace(' ', '', $tab_mois_manquant);
                    //fin mois cloture
                    if ($value->cloture < 9) {
                        $debut_mois = ($exercice - 1) . '-0' . ($value->cloture + 1) . '-01';
                    } else if ($value->cloture >= 9 and $value->cloture < 12) {
                        $debut_mois = ($exercice - 1) . '-' . ($value->cloture + 1) . '-01';
                    } else {
                        $debut_mois = ($exercice) . '-01-01';
                    }
                    //debut mois cloture
                    if ($value->cloture < 10) {
                        $fin_mois = ($exercice) . '-0' . ($value->cloture) . '-01';
                    } else {
                        $fin_mois = ($exercice) . '-' . ($value->cloture) . '-01';
                    }

                    /*$tab_mois_cloture = $this->getBetweenDate($debut_mois, $fin_mois);*/

                    $k = array_key_exists($debut_mois . '-' . $fin_mois, $betweens);
                    if ($k) {
                        $tab_mois_cloture = $betweens[$debut_mois . '-' . $fin_mois];
                    } else{
                        $tab_mois_cloture = $this->getBetweenDate($debut_mois, $fin_mois);

                        $betweens[$debut_mois . '-' . $fin_mois] = $tab_mois_cloture;

                    }

                    $nb_m_mois_exist = false;
                    switch (count($mois_manquant)) {
                        case 0:
                            $nb_m_mois_exist = true;
                            $tab_imputees[$key]['m'] = 'M-1';
                            break;
                        case 1:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 2:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 3:
                            $tab_key_mois[$key] = array_intersect($tab_mois_cloture, $mois_manquant);
                            break;
                        case 12:
                            $nb_m_mois_exist = true;
                            //jerena aloha raha mis relevé ihany le banque amin'ny alalan'ny dossier
                            $resReleves = $this->getDoctrine()
                                               ->getRepository('AppBundle:Image')
                                               ->getInfoReleveByDossier($value->banque_compte_id, $exercice);
                            $tab_imputees[$key]['m'] = (count($resReleves) > 0) ? 'Inc.' : 'Auc.';
                            break;
                        default:
                            $nb_m_mois_exist = true;
                            $tab_imputees[$key]['m'] = 'Inc.';
                            break;
                    }

                    if (!$nb_m_mois_exist) {
                        $min = 13;
                        $now = new \DateTime();
                        foreach ($tab_key_mois[$key] as $key_m => $key_mois_m) {
                            if ($key_m < $min) {
                                $min = $key_m;
                            }
                        }
                        //Jerena aloha raha misy tsy ampy eo ampovoany
                        $continue = true;
                        $lastIndex = -1;
                        foreach ($tab_key_mois[$key] as $k => $v){
                            if($lastIndex === -1){
                                $lastIndex = $k;
                                continue;
                            }
                            if($lastIndex+1 !== $k){
                                $continue = false;
                                break;
                            }
                            else{
                                $lastIndex = $k;
                            }
                        }

                        if($continue) {
                            if (intval($exercice) < $now->format('Y')) {
                                switch ($min) {
                                    case 11:
                                        $tab_imputees[$key]['m'] = 'M-1';
                                        break;
                                    case 10:
                                        $tab_imputees[$key]['m'] = 'M-1';
                                        break;
                                    case 9:
                                        $tab_imputees[$key]['m'] = 'M-2';
                                        break;
                                    default:
                                        $tab_imputees[$key]['m'] = 'Inc.';
                                        break;
                                }
                            } else {
                                if (array_key_exists($min, $tab_key_mois[$key])){
                                    $now = new \DateTime();
                                    $yearNow = $now->format('Y');
                                    $monthNow = $now->format('m');
                                    $dateNow = intval($now->format('d'));
                                    $datetime = \DateTime::createFromFormat('Y-m-d', $tab_key_mois[$key][$min] . "-01");
                                    $interval = $now->diff($datetime);
                                    $diff = $interval->m + 1;
                                    if($dateNow <= 6 ){
                                        $diff = $interval->m;
                                    }

                                    if ($diff === 0) {
                                        $tab_imputees[$key]['m'] = 'M-1';
                                    } else if ($diff > 0) {
                                        $tab_imputees[$key]['m'] = 'M-' . $diff;
                                    } else {
                                        $tab_imputees[$key]['m'] = 'Inc.';
                                    }
                                }else{
                                    $tab_imputees[$key]['m'] = 'Inc.';
                                }
                            }
                        }
                        else{
                            $tab_imputees[$key]['m'] = 'Inc.';
                        }
                    }
                }
                else {
                    $tab_imputees[$key]['m'] = 'M-1';
                }

                $nbr_rapproche = ($value->nb_r != 0) ? (($value->nb_lettre + $value->nb_clef + $value->nb_ecriture_change) * 100) / $value->nb_r : 0;
                $nbr_pc_manquant = ($value->nb_r != 0) ? ($value->nb_r - ($value->nb_lettre + $value->nb_clef)) : 0;
                $tab_imputees[$key]['nb_pc_manquant'] = $this->ifNull($nbr_pc_manquant);
                $tab_imputees[$key]['chq_inconnu'] = $this->ifNull($value->chq_inconnu);
                $tab_imputees[$key]['nbr_rapproche'] = $this->ifNull($nbr_rapproche);
                $tab_imputees[$key]['alettrer'] = ($value->a_lettrer) ? $value->a_lettrer : 0;
                $tab_imputees[$key]['date_envoi'] = $dateEnvoi;

                $remise = 0;
                $frbanc = 0;
                $lcr = 0;
                $vrmt = 0;
                $cartCredRel = 0;
                $cartDebRel = 0;
                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 8, -1, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $frbanc += 1;
                    }
                }
                $dataObMq[0]['nb'] = $frbanc;
                $dataObMq[0]['libelle'] = 'Frais Bancaire';

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 5, -1, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $lcr += 1;
                    }
                }
                $dataObMq[1]['nb'] = $lcr;
                $dataObMq[1]['libelle'] = 'Relevé  LCR';

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 7, -1, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $remise += 1;
                    }
                }
                $dataObMq[2]['nb'] = $remise;
                $dataObMq[2]['libelle'] = 'Remise en banque';

                $imageOb = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 153, 1905, 1, -1);

                $imageObChq = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 6, -1, 1, -1);
                $imageOb = array_merge($imageOb, $imageObChq);
                foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $vrmt += 1;
                    }
                }

                $dataObMq[3]['nb'] = $vrmt;
                $dataObMq[3]['libelle'] = 'VRT/CHQ EMIS';

                $imageOb = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 1, 1901, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $cartCredRel += 1;
                    }
                }
                $dataObMq[4]['nb'] = $cartCredRel;
                $dataObMq[4]['libelle'] = 'Cartes de crédit relevé';

                $imageOb = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getListImageBanqueGestionTache($value->dossier_id, $exercice, 0, 1, 2791, 1, -1);
                 foreach ($imageOb as $im) {
                    if($im->ctrl_saisie > 2 && $im->valider != 100){
                        $cartDebRel += 1;
                    }
                }
                $dataObMq[5]['nb'] = $cartDebRel;
                $dataObMq[5]['libelle'] = 'Cartes Débits tickets';

                $isOb = false;
                foreach ($dataObMq as $dataOb) {
                    if($dataOb['nb'] > 0){
                        $isOb = true;
                    }
                }
                $tab_imputees[$key]['ob'] = ($isOb) ? 'PB' : 'OK';
                $tab_imputees[$key]['data_ob_m'] = json_encode($dataObMq, true);
                $param['periode'] = 4;
                $dataRb1AC = $this->getDoctrine()
                                  ->getRepository('AppBundle:Image')
                                  ->getRb1AControler($param);
                $tab_imputees[$key]['acontroler'] = $dataRb1AC['imgSaisieKo'];
                $banqueCompte = $this->getDoctrine()->getRepository('AppBundle:BanqueCompte')->find($value->banque_compte_id);
                $soldeDebut = $this->getDoctrine()->getRepository('AppBundle:Image')->getSoldes($banqueCompte,$exercice);
                $soldeFin = $this->getDoctrine()->getRepository('AppBundle:Image')->getSoldes($banqueCompte,$exercice,false);

                $mouvements = $this->getDoctrine()
                                    ->getRepository('AppBundle:Image')
                                    ->getMouvement($exercice, $value->banquecompte_id);

                $ecart = (float)($soldeFin - $soldeDebut - $mouvements);

                $tab_imputees[$key]['ecart'] = round($ecart);
                $tab_imputees[$key]['rb2'] = 'Imp.';
            }
        }

        return $tab_imputees;
    }

    public function getBetweenDate($start, $end)
    {
        $time1 = strtotime($start);
        $time2 = strtotime($end);
        $my = date('mY', $time2);
        $months = array(date('Y-m', $time1));
        while ($time1 < $time2) {
            $time1 = strtotime(date('Y-m', $time1) . ' +1 month');
            if (date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('Y-m', $time1);
        }
        $months[] = date('Y-m', $time2);
        return $months;
    }

    public function ifNull($value,$null = 0)
    {
        $value = ($value) ? $value :  $null;

        return $value;
    }

    public function configTacheLibreAction(Request $request)
    {
        $clients = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids',json_decode($request->request->get('clients')))
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
        $date = \DateTime::createFromFormat('Y-m-d',$request->request->get('date'));
        $title = $request->request->get('title');
        $googleId = $request->request->get('google_id');
        $tacheLibres = $this->getDoctrine()->getRepository('AppBundle:Tache')
            ->getAllTache();

        return $this->render('@Revision/Agenda3/config-tache-libre.html.twig',[
            'title' => $title,
            'clients' => $clients,
            'tacheLibres' => $tacheLibres,
            'date' => $date,
            'googleId' => $googleId
        ]);
    }

    public function tacheMajAction(Request $request,$periode)
    {
        $clientsIds = $request->query->get('clients');
        /** @var Client[] $clients */
        $clients = $this->getDoctrine()->getRepository('AppBundle:Client')
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids',$clientsIds)
            ->orderBy('c.nom')
            ->getQuery()->getResult();
        $periode = \DateTime::createFromFormat('Y-m-d',$periode);

        $this->getDoctrine()->getRepository('AppBundle:Calendar')
                            ->taches3Events($clients,$periode);

        $isLegale = (intval($request->query->get('legale')) == 1);
        $isLibre = (intval($request->query->get('libre')) == 1);
        $isFaite = (intval($request->query->get('faite')) == 1);

        $isScriptura = (intval($request->query->get('scriptura')) == 1);
        $isEc = (intval($request->query->get('ec')) == 1);
        $isCf = (intval($request->query->get('cf')) == 1);

        $events = $this->getDoctrine()->getRepository('AppBundle:Calendar')
            ->taches3EventsNoUpdates($clients,$periode,
                $isLegale,$isLibre,$isFaite,
                $isScriptura,$isEc,$isCf);

        return new JsonResponse($events);
    }
}