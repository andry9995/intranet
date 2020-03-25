<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 13/09/2018
 * Time: 11:36
 */

namespace RevisionBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\GoogleCalendarConfig;
use AppBundle\Entity\TachePrioriteDossier;
use AppBundle\Entity\TacheSynchro;
use AppBundle\Entity\TacheSynchroMoov;
use AppBundle\Functions\GoogleCalendar;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Agenda2Controller extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $clients = $this->getDoctrine()->getRepository('AppBundle:Client')->getAllClient();
        return $this->render('@Revision/Agenda2/index.html.twig',['clients'=>$clients]);
    }


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
        $events = $this->getDoctrine()->getRepository('AppBundle:Calendar')
            ->getTachesClientsNoUpdate($clients,$periode);

        /*$events = $this->getDoctrine()->getRepository('AppBundle:Calendar')
            ->getTachesClients($clients,$periode);*/

        //return $this->render('@Tache/TacheAdmin/test.html.twig',['test'=>$events]);

        return new JsonResponse($events);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function tacheMoovAction(Request $request)
    {
        $googleId = $request->request->get('google_id');
        $googleIdSave = $googleId;
        /** @var TacheSynchro $tacheSynchro */
        $tacheSynchro = $this->getDoctrine()->getRepository('AppBundle:TacheSynchro')
            ->find($request->request->get('tache_synchro_id'));
        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')
            ->find($request->request->get('dossier'));
        $newDate = \DateTime::createFromFormat('Y-m-d',$request->request->get('new_date'));
        $newDate->setTime(0,0,0);
        $dateNow = new \DateTime();
        $dateNow->setTime(0,0,0);

        $em = $this->getDoctrine()->getManager();
        if ($tacheSynchro)
        {
            if ($tacheSynchro->getDate()->format('Ymd') == $newDate->format('Ymd'))
            {
                $this->getDoctrine()->getRepository('AppBundle:TacheSynchroMoov')->removeAllItems($tacheSynchro);
            }
            else
            {
                $tacheSynchroMoov = new TacheSynchroMoov();
                $tacheSynchroMoov
                    ->setTacheSynchro($tacheSynchro)
                    ->setDate($newDate)
                    ->setOperateur($this->getUser());
                $em->persist($tacheSynchroMoov);
            }
        }

        /** @var \Google_Service_Calendar_Event $eventUpdated */
        $eventUpdated = null;
        if ($googleId != 'NONE')
        {
            $config = $this->getDoctrine()
                ->getRepository('AppBundle:GoogleCalendarConfig')
                ->getConfig($dossier->getSite()->getClient());

            if ($config)
            {
                $calendar = new GoogleCalendar();
                $calendar->setConfig($config);
                $eventUpdated = $calendar->updateDateEvent($googleId,$newDate);
                if ($tacheSynchro) $tacheSynchro->setIdGoogle($eventUpdated->getId());
                $googleId = $eventUpdated->getId();
            }
        }

        /** @var TachePrioriteDossier $tachePriorityDossier */
        $tachePriorityDossier = $this->getDoctrine()->getRepository('AppBundle:TachePrioriteDossier')
            ->findOneBy(['dossier'=>$dossier]);

        $recalculer = false;
        if ($tachePriorityDossier)
        {
            $datePriority = clone $tachePriorityDossier->getDate();
            $datePriority->setTime(0,0,0);
            if ($datePriority > $newDate && $newDate >= $dateNow)
            {
                $tachePriorityDossier
                    ->setDate($newDate)
                    ->setDateCalcul($dateNow);
                if ($tacheSynchro) $tachePriorityDossier->setTacheSynchro($tacheSynchro);
                elseif ($googleId != 'NONE') $tachePriorityDossier->setGoogleId($googleId);
            }
            elseif ($tachePriorityDossier->getGoogleId() == $googleIdSave ||
                ($tachePriorityDossier->getTacheSynchro() && $tacheSynchro && $tachePriorityDossier->getTacheSynchro()->getId() == $tacheSynchro->getId()))
            {
                $recalculer = true;
            }

            if ($eventUpdated) $tachePriorityDossier->setGoogleId($googleId);
        }
        else
        {
            if ($newDate > $dateNow)
            {
                $tachePriorityDossier = new TachePrioriteDossier();
                $tachePriorityDossier
                    ->setDateCalcul($dateNow)
                    ->setDate($newDate)
                    ->setDossier($dossier);

                if ($tacheSynchro)
                    $tachePriorityDossier->setTacheSynchro($tacheSynchro);
                elseif ($googleId != 'NONE')
                    $tachePriorityDossier->setGoogleId($googleId);

                $em->persist($tachePriorityDossier);
            }
        }

        $em->flush();
        if ($recalculer) $this->getDoctrine()->getRepository('AppBundle:Calendar')
            ->getTachesClients([$dossier->getSite()->getClient()],$newDate,$dossier);

        return new Response(($eventUpdated) ? $eventUpdated->getId() : 'NONE');
    }
}