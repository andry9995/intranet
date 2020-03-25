<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 02/05/2018
 * Time: 09:14
 */

namespace AppBundle\Repository;

use AppBundle\Entity\TacheStatus;
use AppBundle\Functions\GoogleCalendar;
use Doctrine\ORM\EntityRepository;

class TacheStatusRepository extends EntityRepository
{
    /**
     * @param $params array (id|dossier_id|tache_dossier_id|tache_legale_id|tache_legale_action_id|date|report_date)
     * @return \AppBundle\Entity\TacheStatus|null
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getByParam(array $params)
    {
        $em = $this->getEntityManager();
        $tacheStatus = null;
        $dossier = null;
        $tache_dossier = null;
        $tache_legale = null;
        $tache_legale_action = null;
        $date = null;
        $report_date = null;
        if (isset($params['id']) && !empty($params['id'])) {
            $tacheStatus = $this->getEntityManager()
                ->getRepository('AppBundle:TacheStatus')
                ->find($params['id']);
        }
        if (isset($params['dossier_id']) && !empty($params['dossier_id'])) {
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($params['dossier_id']);
        }
        if (isset($params['tache_dossier_id']) && !empty($params['tache_dossier_id'])) {
            $tache_dossier = $this->getEntityManager()
                ->getRepository('AppBundle:TacheDossier')
                ->find($params['tache_dossier_id']);
        }
        if (isset($params['tache_legale_id']) && !empty($params['tache_legale_id'])) {
            $tache_legale = $this->getEntityManager()
                ->getRepository('AppBundle:TacheLegale')
                ->find($params['tache_legale_id']);
        }
        if (isset($params['tache_legale_action_id']) && !empty($params['tache_legale_action_id'])) {
            $tache_legale_action = $this->getEntityManager()
                ->getRepository('AppBundle:TacheLegaleAction')
                ->find($params['tache_legale_action_id']);
        }
        if (isset($params['date']) && !empty($params['date'])) {
            $date = new \DateTime($params['date'] . ' 00:00:00');
        }
        if (isset($params['report_date']) && !empty($params['report_date'])) {
            $report_date = new \DateTime($params['report_date'] . ' 00:00:00');
        }

        if ($tacheStatus) {
            return $tacheStatus;
        } else {
            if (!$dossier || !$date) {
                return null;
            }

            /** TACHE LIBRE */
            if ($tache_dossier) {
                if ($report_date) {
                    $tacheStatus = $this->getEntityManager()
                        ->getRepository('AppBundle:TacheStatus')
                        ->findBy(array(
                            'dossier' => $dossier,
                            'tacheDossier' => $tache_dossier,
                            'date' => $report_date,
                        ));
                } else {
                    $tacheStatus = $this->getEntityManager()
                        ->getRepository('AppBundle:TacheStatus')
                        ->findBy(array(
                            'dossier' => $dossier,
                            'tacheDossier' => $tache_dossier,
                            'date' => $date,
                        ));
                }
                if (count($tacheStatus) > 0) {
                    $res = $tacheStatus[0];
                    if (count($tacheStatus) > 1) {
                        for ($i = 1; $i < count($tacheStatus); $i++) {
                            $em->remove($tacheStatus[$i]);
                        }
                        $em->flush();
                    }
                    return $res;
                }
                return null;
            } /** TACHE LEGALE */
            elseif ($tache_legale && $tache_legale_action) {
                if ($report_date) {
                    $tacheStatus = $this->getEntityManager()
                        ->getRepository('AppBundle:TacheStatus')
                        ->findBy(array(
                            'dossier' => $dossier,
                            'tacheLegale' => $tache_legale,
                            'tacheLegaleAction' => $tache_legale_action,
                            'date' => $report_date,
                        ));
                } else {
                    $tacheStatus = $this->getEntityManager()
                        ->getRepository('AppBundle:TacheStatus')
                        ->findBy(array(
                            'dossier' => $dossier,
                            'tacheLegale' => $tache_legale,
                            'tacheLegaleAction' => $tache_legale_action,
                            'date' => $date,
                        ));
                }
                if (count($tacheStatus) > 0) {
                    $res = $tacheStatus[0];
                    if (count($tacheStatus) > 1) {
                        for ($i = 1; $i < count($tacheStatus); $i++) {
                            $em->remove($tacheStatus[$i]);
                        }
                        $em->flush();
                    }
                    return $res;
                }
                return null;
            }
        }
        return null;
    }

    /**
     * @param $params array (id|dossier_id|tache_dossier_id|tache_legale_id|tache_legale_action_id|date|report_date|new_report_date)
     * @param $status
     * @return \AppBundle\Entity\TacheStatus
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function updateByParam(array $params, $status)
    {
        $em = $this->getEntityManager();
        $tacheStatus = null;
        $dossier = null;
        $tache_dossier = null;
        $tache_legale = null;
        $tache_legale_action = null;
        $date = null;
        $report_date = null;
        $new_report_date = null;
        if (isset($params['id']) && !empty($params['id'])) {
            $tacheStatus = $this->getEntityManager()
                ->getRepository('AppBundle:TacheStatus')
                ->find($params['id']);
        }
        if (isset($params['dossier_id']) && !empty($params['dossier_id'])) {
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($params['dossier_id']);
        }
        if (isset($params['tache_dossier_id']) && !empty($params['tache_dossier_id'])) {
            $tache_dossier = $this->getEntityManager()
                ->getRepository('AppBundle:TacheDossier')
                ->find($params['tache_dossier_id']);
        }
        if (isset($params['tache_legale_id']) && !empty($params['tache_legale_id'])) {
            $tache_legale = $this->getEntityManager()
                ->getRepository('AppBundle:TacheLegale')
                ->find($params['tache_legale_id']);
        }
        if (isset($params['tache_legale_action_id']) && !empty($params['tache_legale_action_id'])) {
            $tache_legale_action = $this->getEntityManager()
                ->getRepository('AppBundle:TacheLegaleAction')
                ->find($params['tache_legale_action_id']);
        }
        if (isset($params['date']) && !empty($params['date'])) {
            $date = new \DateTime($params['date'] . ' 00:00:00');

        }
        if (isset($params['report_date']) && !empty($params['report_date'])) {
            $report_date = new \DateTime($params['report_date'] . ' 00:00:00');
        }
        if (isset($params['new_report_date']) && !empty($params['new_report_date'])) {
            $new_report_date = new \DateTime($params['new_report_date'] . ' 00:00:00');
        }
        if (!$dossier || !$date) {
            throw new \InvalidArgumentException("Le dossier et la date de la tâche est obligatoire");
        }

        if (!$tacheStatus) {
            /** TACHE LIBRE */
            $res = null;
            if ($tache_dossier) {
                $tacheStatus = $this->getEntityManager()
                    ->getRepository('AppBundle:TacheStatus')
                    ->findBy(array(
                        'dossier' => $dossier,
                        'tacheDossier' => $tache_dossier,
                        'reportDate' => $date,
                    ));
                if (empty($tacheStatus)) {
                    $tacheStatus = $this->getEntityManager()
                        ->getRepository('AppBundle:TacheStatus')
                        ->findBy(array(
                            'dossier' => $dossier,
                            'tacheDossier' => $tache_dossier,
                            'date' => $date,
                        ));
                }
                if (count($tacheStatus) > 0) {
                    $res = $tacheStatus[0];
                    if (count($tacheStatus) > 1) {
                        for ($i = 1; $i < count($tacheStatus); $i++) {
                            $em->remove($tacheStatus[$i]);
                        }
                        $em->flush();
                    }
                }
            } /** TACHE LEGALE */
            elseif ($tache_legale && $tache_legale_action) {
                $tacheStatus = $this->getEntityManager()
                    ->getRepository('AppBundle:TacheStatus')
                    ->findBy(array(
                        'dossier' => $dossier,
                        'tacheLegale' => $tache_legale,
                        'tacheLegaleAction' => $tache_legale_action,
                        'reportDate' => $date,
                    ));
                if (empty($tacheStatus)) {
                    $tacheStatus = $this->getEntityManager()
                        ->getRepository('AppBundle:TacheStatus')
                        ->findBy(array(
                            'dossier' => $dossier,
                            'tacheLegale' => $tache_legale,
                            'tacheLegaleAction' => $tache_legale_action,
                            'date' => $date,
                        ));
                }
                if (count($tacheStatus) > 0) {
                    $res = $tacheStatus[0];
                    if (count($tacheStatus) > 1) {
                        for ($i = 1; $i < count($tacheStatus); $i++) {
                            $em->remove($tacheStatus[$i]);
                        }
                        $em->flush();
                    }
                }
            }
            if (!$res) {
                $tacheStatus = new TacheStatus();
            } else {
                $tacheStatus = $res;
            }
        }

        if ($date->format('w') == 6) {
            $date->add(new \DateInterval('P2D'));
        } elseif ($date->format('w') == 0) {
            $date->add(new \DateInterval('P1D'));
        }
        if ($new_report_date) {
            if ($new_report_date->format('w') == 6) {
                $new_report_date->add(new \DateInterval('P2D'));
            } elseif ($new_report_date->format('w') == 0) {
                $new_report_date->add(new \DateInterval('P1D'));
            }
        }

        $tacheStatus->setDossier($dossier)
            ->setTacheDossier($tache_dossier)
            ->setTacheLegale($tache_legale)
            ->setTacheLegaleAction($tache_legale_action)
            ->setDate($date)
            ->setReportDate($new_report_date)
            ->setStatus($status);
        $em->persist($tacheStatus);
        $em->flush();

        /* Tester si Google Calendar Event déjà créé */
        $original_title = null;
        if ($tacheStatus->getTacheDossier()) {
            $original_title = $tacheStatus->getTacheDossier()->getTache()->getNom();
        } else {
            if ($tacheStatus->getTacheLegale()) {
                $original_title = $tacheStatus->getTacheLegale()->getNom();
            }
        }
        if ($original_title) {
            $client = $tacheStatus->getDossier()->getSite()->getClient();

            $gcal_synchros = $this->getEntityManager()
                ->getRepository('AppBundle:GoogleCalendarSynchro')
                ->findBy(array(
                    'client' => $client,
                    'start' => $date,
                    'originalTitle' => $original_title,
                ));

            $config = $this->getEntityManager()
                ->getRepository('AppBundle:GoogleCalendarConfig')
                ->findOneBy(array(
                    'client' => $client,
                ));
            if (!empty($gcal_synchros)) {
                foreach ($gcal_synchros as $gcal_synchro) {
                    $em->remove($gcal_synchro);
                    if ($config && $config->getIdentifiant() && trim($config->getIdentifiant()) != "") {
                        $calendar = new GoogleCalendar();
                        $calendar->setConfig($config);
                        $calendar->setFromScriptura(true);
                        $calendar->removeEvent($gcal_synchro->getIdentifiant());
                    }
                }
                $em->flush();
            }

            $gcal_synchros = $this->getEntityManager()
                ->getRepository('AppBundle:GoogleCalendarSynchro')
                ->findBy(array(
                    'client' => $client,
                    'start' => $report_date,
                    'originalTitle' => $original_title,
                ));
            if (!empty($gcal_synchros)) {
                foreach ($gcal_synchros as $gcal_synchro) {
                    $em->remove($gcal_synchro);
                    if ($config && $config->getIdentifiant() && trim($config->getIdentifiant()) != "") {
                        $calendar = new GoogleCalendar();
                        $calendar->setConfig($config);
                        $calendar->setFromScriptura(true);
                        $calendar->removeEvent($gcal_synchro->getIdentifiant());
                    }
                }
                $em->flush();
            }
        }

        return $tacheStatus;
    }
}