<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/09/2018
 * Time: 11:32
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheEntity;
use AppBundle\Entity\TacheEntityLibreAction;
use Doctrine\ORM\EntityRepository;
use RevisionBundle\Controller\Functions;

class TacheEntityLibreActionRepository extends EntityRepository
{
    /**
     * @param TacheEntity $tacheEntity
     * @return mixed
     */
    public function getByTacheEntity(TacheEntity $tacheEntity)
    {
        return $this->createQueryBuilder('tela')
            ->where('tela.tacheEntity = :tacheEntity')
            ->setParameter('tacheEntity',$tacheEntity)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function getTacheEntityLibreActions(TacheEntity $tacheEntity,$annee)
    {
        $dossier = $tacheEntity->getDossier();
        $tacheEntity = (!is_null($tacheEntity->getTacheEntity())) ? $tacheEntity->getTacheEntity() : $tacheEntity;

        $tacheEntityLibreActions = $this->createQueryBuilder('tela')
            ->where('tela.tacheEntity = :tacheEntity')
            ->setParameter('tacheEntity',$tacheEntity)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($tacheEntityLibreActions as $tacheEntityLibreAction)
        {
            $results[] = $this->getDatesInYearCurrent($tacheEntityLibreAction,$dossier,$annee);
        }

        return $results;
    }

    public function getDatesInYearCurrent(TacheEntityLibreAction $tacheEntityLibreAction, Dossier $dossier = null, $annee = null)
    {
        $cloture = $dossier->getCloture();
        $liste = [];
        $endStartYear = Functions::getStartEndInAnnee($annee);

        $periode = $tacheEntityLibreAction->getPeriode();
        $periodeAnnees = [1,2,3,4,5,6,12];
        // 0=ponctuel,1=annuel,2=semestriel,3=quadrimestriel,4=trimestriel,6=bimensuel,12=mensuel
        if ($periode == 0)
        {
            $liste[] = $tacheEntityLibreAction->getDateCalcul()->setTime(0,0,0);
        }
        elseif (in_array($periode,$periodeAnnees))
        {
            //0:debut exercice; 1:demarrage;2:1er janvier;3:date_calcul
            $calculAPartirDe = $tacheEntityLibreAction->getCalculerAPartir();
            $jour = intval($tacheEntityLibreAction->getJour());
            if ($jour == 0) $jour = 1;
            if ($jour < 10) $jour = '0'.$jour;

            if ($calculAPartirDe == 0) $debut = $this->getEntityManager()->getRepository('AppBundle:Dossier')->getDateDebut($dossier,$annee);
            elseif ($calculAPartirDe == 1) $debut = $tacheEntityLibreAction->getDemarrage();
            elseif ($calculAPartirDe == 3) $debut = $tacheEntityLibreAction->getDateCalcul();
            else $debut = \DateTime::createFromFormat('d-m-Y','01-01-'.$annee);
            $end = \DateTime::createFromFormat('d-m-Y','31-12-'.($annee + 1));
            $start = \DateTime::createFromFormat('d-m-Y',$jour.$debut->format('-m-Y'));
            //1=annuel,2=semestriel,3=quadrimestriel,4=trimestriel,6=bimensuel,12=mensuel
            $iteration = 1;
            $interval = 'M';
            if ($periode == 1)
            {
                $iteration = 1;
                $interval = 'Y';
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
        }

        return (object)
        [
            'dossier' => $dossier,
            'tacheEntityLibreAction' => $tacheEntityLibreAction,
            'liste' => $liste
        ];
    }
}