<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 03/01/2019
 * Time: 17:16
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\TachesLibre;
use AppBundle\Entity\TachesLibreDate;
use Doctrine\ORM\EntityRepository;
use RevisionBundle\Controller\Functions;

class TachesLibreDateRepository extends EntityRepository
{
    /**
     * @param TachesLibre $tachesLibre
     * @return TachesLibreDate[]
     */
    public function tachesLibreDates(TachesLibre $tachesLibre)
    {
        return $this->createQueryBuilder('tld')
            ->where('tld.tachesLibre = :tachesLibre')
            ->setParameter('tachesLibre',$tachesLibre)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Client[] $clients
     * @return array
     */
    public function getTachesLibreDatesForClients($clients)
    {
        $tachesLibres = $this->getEntityManager()->getRepository('AppBundle:TachesLibre')
            ->getTachesLibreForClients($clients);

        $results = [];
        foreach ($tachesLibres as $tachesLibre)
        {
            $dossier = $tachesLibre->getDossier();
            $key = $dossier->getSite()->getClient()->getId();
            /** @var TachesLibre $tachesLibreParent */
            $tachesLibreParent = ($tachesLibre->getTachesLibre()) ? $tachesLibre->getTachesLibre() : $tachesLibre;
            $tachesLibreDates = $this->tachesLibreDates($tachesLibreParent);

            if (!array_key_exists($key,$results)) $results[$key] = [];
            $results[$key][] = (object)
            [
                'dossier' => $dossier,
                'tachesLibreDates' => $tachesLibreDates
            ];
        }

        return $results;
    }

    /**
     * @param TachesLibreDate $tachesLibreDate
     * @param Dossier $dossier
     * @param \DateTime $periodeDate
     * @return \DateTime[]
     */
    public function getDatesInYear(TachesLibreDate $tachesLibreDate,Dossier $dossier,\DateTime $periodeDate)
    {
        /** @var \DateTime[] $liste */
        $liste = [];
        $listeQ = [];
        $annee = intval($periodeDate->format('Y'));

        $periode = $tachesLibreDate->getPeriode();
        $periodeAnnees = [1,2,3,4,5,6,12];
        // 0=ponctuel,1=annuel,2=semestriel,3=quadrimestriel,4=trimestriel,6=bimensuel,12=mensuel,5:quotidien
        if ($periode == 0)
        {
            $liste[] = $tachesLibreDate->getDateCalcul()->setTime(0,0,0);
        }
        elseif (in_array($periode,$periodeAnnees))
        {
            //0:debut exercice; 1:demarrage;2:1er janvier;3:date_calcul
            $calculAPartirDe = $tachesLibreDate->getCalculerAPartir();
            $jour = intval($tachesLibreDate->getJour());
            if ($jour == 0) $jour = 1;
            if ($jour < 10) $jour = '0'.$jour;

            if ($calculAPartirDe == 0) $debut = $this->getEntityManager()->getRepository('AppBundle:Dossier')->getDateDebut($dossier,$annee);
            elseif ($calculAPartirDe == 1) $debut = $tachesLibreDate->getDemarrage();
            elseif ($calculAPartirDe == 3) $debut = $tachesLibreDate->getDateCalcul();
            else $debut = \DateTime::createFromFormat('d-m-Y','01-01-'.$annee);

            $end = \DateTime::createFromFormat('d-m-Y','31-12-'.($annee + 1));
            $start = \DateTime::createFromFormat('d-m-Y',$jour.$debut->format('-m-Y'));

            $moisAdditif = intval($tachesLibreDate->getMoisAdditif());
            if ($moisAdditif != 0)
            {
                if ($moisAdditif > 0)
                {
                    $start->add(new \DateInterval('P'.$moisAdditif.'M'));
                    $end->add(new \DateInterval('P'.$moisAdditif.'M'));
                }
                else
                {
                    $start->sub(new \DateInterval('P'.abs($moisAdditif).'M'));
                    $end->sub(new \DateInterval('P'.abs($moisAdditif).'M'));
                }
            }

            //1=annuel,2=semestriel,3=quadrimestriel,4=trimestriel,6=bimensuel,12=mensuel
            if ($periode == 1)
            {
                $iteration = 1;
                $interval = 'Y';
            }
            else if($periode == 5){
                $iteration = 1;
                $interval = 'D';
            }
            else
            {
                $interval = 'M';
                if ($periode == 2) $iteration = 6;
                elseif ($periode == 3) $iteration = 4;
                elseif ($periode == 4) $iteration = 3;
                elseif ($periode == 6) $iteration = 2;
                else $iteration = 1;
            }

            $liste = Functions::datesBetweenWithInterval($start,$end,$interval,$iteration);
            if($periode == 5){
                foreach ($liste as $key => $dateQ) {
                    if($dateQ->format('w') == 6 || $dateQ->format('w') == 0)
                        continue;
                    array_push($listeQ,$dateQ->setTime(0,0,0));
                }
                $liste = $listeQ;
            }
        }

        return $liste;
    }
}