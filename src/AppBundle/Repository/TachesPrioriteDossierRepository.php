<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 16/01/2019
 * Time: 12:01
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\GoogleCalendarConfig;
use AppBundle\Entity\TachePrioriteDossier;
use AppBundle\Entity\TachesPrioriteDossier;
use AppBundle\Entity\TachesSynchro;
use AppBundle\Functions\CustomPdoConnection;
use AppBundle\Functions\GoogleCalendar;
use Doctrine\ORM\EntityRepository;

class TachesPrioriteDossierRepository extends EntityRepository
{
    /**
     * @param Client[] $clients
     */
    public function deletePriorite($clients = [])
    {
        $dateNow = new \DateTime();
        $dateNow->setTime(0,0,0);
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $params = [];
        if  (count($clients) > 0)
        {
            $ids = '';
            foreach ($clients as $client) $ids .= $client->getId().',';
            $ids .= '0';
            $req = '
                DELETE tpd
                FROM taches_priorite_dossier as tpd
                JOIN dossier d ON (d.id = tpd.dossier_id)
                JOIN site s ON (s.id = d.site_id)
                WHERE s.client_id IN ('.$ids.')
            ';
        }
        else
        {
            $req = 'DELETE FROM taches_priorite_dossier WHERE date_calcul < :dateNow';
            $params['dateNow'] = $dateNow->format('Y-m-d');
        }
        $prep = $pdo->prepare($req);
        $prep->execute($params);
    }

    /**
     * @param array $clients
     * @return array
     */
    public function updatePriorites($clients = [])
    {
        $codeScriptura = '#S: ';
        $defaultIdGoogle = 'NONE';

        $dateNow = new \DateTime();
        $debut = clone  $dateNow;
        $debut->setTime(0, 0,0);
        $yearNext = intval($dateNow->format('Y')) + 1;
        $fin = new \DateTime($dateNow->format($yearNext.'-12-31'));
        $fin->setTime(23,59,59);

        $this->deletePriorite();
        $this->deletePriorite($clients);
        $prioriteDossiers = [];
        foreach ($clients as $client)
        {
            $prioriteDossiersTachesSynchros = $this->getEntityManager()->getRepository('AppBundle:TachesSynchro')
                ->getTachesSynchrosForPriorite($client);

            //return $prioriteDossiersTachesSynchros;
            $prioriteDossiers = $prioriteDossiers + $prioriteDossiersTachesSynchros;

            /** @var GoogleCalendarConfig $config */
            $config = $this->getEntityManager()->getRepository('AppBundle:GoogleCalendarConfig')
                ->getConfig($client);
            $calendar = null;
            if ($config)
            {
                $calendar = new GoogleCalendar();
                $calendar->setConfig($config);
                $calendar->setTimeMin($debut);
                $calendar->setTimeMax($fin);
                $googleClientTemps = $calendar->getCalendar();

                foreach ($googleClientTemps as &$googleClientTemp)
                {
                    if (substr($googleClientTemp['title'],0,strlen($codeScriptura)) != $codeScriptura)
                    {
                        $dossierTache = $this->getEntityManager()->getRepository('AppBundle:Calendar')->getDossierTacheByTitle($client,$googleClientTemp['title']);
                        $dossierTacheLibre = null;
                        if ($dossierTache) $dossierTacheLibre = $dossierTache->dossier;
                        $googleClientTemp['dossier'] = ($dossierTacheLibre) ? $dossierTacheLibre->getId() : 0;
                        $googleClients['gcal'][] = $googleClientTemp;

                        $dateT = \DateTime::createFromFormat('Y-m-d',$googleClientTemp['start']);
                        $dateT->setTime(0,0,0);
                        if ($dateT < (new \DateTime())->setTime(0,0,0)) continue;
                        if ($dossierTache)
                        {
                            //return $dossierTache;
                            $keyDossier = intval($dossierTache->dossier->getId());
                            if (array_key_exists($keyDossier,$prioriteDossiers))
                            {
                                if ($prioriteDossiers[$keyDossier]->d > $dateT)
                                {
                                    $prioriteDossiers[$keyDossier] = (object)
                                    [
                                        'd' => $dateT,
                                        't' => 1,
                                        'id' => $googleClientTemp['id'],
                                        'dossier' => $dossierTache->dossier
                                    ];
                                }
                            }
                            else
                            {
                                $prioriteDossiers[$keyDossier] = (object)
                                [
                                    'd' => $dateT,
                                    't' => 1,
                                    'id' => $googleClientTemp['id'],
                                    'dossier' => $dossierTache->dossier
                                ];
                            }
                        }
                    }
                }
            }
        }

        $em = $this->getEntityManager();
        foreach ($prioriteDossiers as $prioriteDossier)
        {
            /** @var \DateTime $date */
            $date = $prioriteDossier->d;
            /** @var TachesSynchro $tachesSynchro */
            $tachesSynchro = null;
            /** @var Dossier $dossier */
            $dossier = null;
            $googleId = null;
            if ($prioriteDossier->t == 0)
            {
                $tachesSynchro = $this->getEntityManager()->getRepository('AppBundle:TachesSynchro')
                    ->find($prioriteDossier->id);
                $dossier = $tachesSynchro->getDossier();
            }
            else
            {
                $googleId = $prioriteDossier->id;
                $dossier = $prioriteDossier->dossier;
            }

            $tachesPrioriteDossier = new TachesPrioriteDossier();
            $tachesPrioriteDossier
                ->setDate($date)
                ->setDossier($dossier)
                ->setDateCalcul(new \DateTime())
                ->setGoogleId($googleId)
                ->setTachesSynchro($tachesSynchro);

            $em->persist($tachesPrioriteDossier);
        }
        $em->flush();

        return $prioriteDossiers;
    }

    public function updatePriotiteDossier(Dossier $dossier)
    {
        $idGoogleDefault = 'NONE';
        $codeScriptura = '#S: ';

        $dateNow = new \DateTime();
        $dateNow->setTime(0,0,0);
        /** @var object $priorite */
        $priorite = null;
        $tachesSynchros = $this->getEntityManager()->getRepository('AppBundle:TachesSynchro')
            ->getAllForDossier($dossier);

        foreach ($tachesSynchros as $tachesSynchro)
        {
            $date = $tachesSynchro->getDate();
            $tachesSynchroMoov = $this->getEntityManager()->getRepository('AppBundle:TachesSynchroMoov')
                ->getLastMoov($tachesSynchro);

            if ($tachesSynchroMoov) $date = $tachesSynchroMoov->getDate();
            if ($date >= $dateNow)
            {
                if (!$priorite || $priorite->date > $date)
                {
                    $priorite = (object)
                    [
                        'date' => $date,
                        'tachesSynchro' => $tachesSynchro,
                        'googleId' => null
                    ];
                }
            }
        }

        if (!$priorite || $priorite->date->format('Ymd') != $dateNow->format('Ymd'))
        {
            /** @var GoogleCalendarConfig $config */
            $config = $this->getEntityManager()
                ->getRepository('AppBundle:GoogleCalendarConfig')
                ->getConfig($dossier->getSite()->getClient());

            $debut = new \DateTime();
            $debut->setTime(0,0,0);
            $fin = new \DateTime();
            $fin->add(new \DateInterval('P3Y'));
            $fin->setTime(23,59,59);

            if ($config)
            {
                $calendar = new GoogleCalendar();
                $calendar->setConfig($config);
                $calendar->setTimeMin($debut);
                $calendar->setTimeMax($fin);

                $googleClientTemps = $calendar->getCalendar();
                foreach ($googleClientTemps as &$googleClientTemp)
                {
                    if (substr($googleClientTemp['title'],0,strlen($codeScriptura)) != $codeScriptura)
                    {
                        $dossierTache = $this->getEntityManager()->getRepository('AppBundle:Calendar')
                            ->getDossierTacheByTitle($dossier->getSite()->getClient(),$googleClientTemp['title']);

                        /** @var Dossier $dossierTacheLibre */
                        $dossierTacheLibre = null;
                        if ($dossierTache) $dossierTacheLibre = $dossierTache->dossier;
                        $date = \DateTime::createFromFormat('Y-m-d',$googleClientTemp['start']);

                        if ($date >= $dateNow && $dossierTacheLibre)
                        {
                            if ($priorite && $priorite->date > $date || !$priorite)
                            {
                                $priorite = (object)
                                [
                                    'date' => $date,
                                    'tachesSynchro' => null,
                                    'googleId' => $googleClientTemp['id']
                                ];
                            }
                        }
                    }
                }
            }
        }

        if ($priorite)
        {
            $em = $this->getEntityManager();
            $tachesPrioriteDossier = $this->getPrioriteDossier($dossier);

            $add = false;
            if (!$tachesPrioriteDossier)
            {
                $tachesPrioriteDossier = new TachesPrioriteDossier();
                $add = true;
            }

            $tachesPrioriteDossier
                ->setGoogleId($priorite->googleId ? $priorite->googleId : null)
                ->setTachesSynchro($priorite->tachesSynchro ? $priorite->tachesSynchro : null)
                ->setDate($priorite->date)
                ->setDateCalcul($dateNow)
                ->setDossier($dossier);

            if ($add) $em->persist($tachesPrioriteDossier);
            $em->flush();
        }
    }

    /**
     * @param Dossier $dossier
     * @return TachesPrioriteDossier
     */
    public function getPrioriteDossier(Dossier $dossier)
    {
       return $this->getEntityManager()->getRepository('AppBundle:TachesPrioriteDossier')
            ->createQueryBuilder('tpd')
            ->where('tpd.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}