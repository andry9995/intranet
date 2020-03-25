<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 12/11/2018
 * Time: 09:21
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\TachesAction;
use AppBundle\Entity\TachesDate;
use AppBundle\Entity\TachesItem;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TachesDateRepository extends EntityRepository
{
    /**
     * @param TachesAction $tachesAction
     * @param TachesDate|null $tachesDate
     * @param Dossier|null $dossier
     * @return array
     */
    public function getMoisUsed(TachesAction $tachesAction, TachesDate $tachesDate = null, Dossier $dossier = null)
    {
        $moisUsed = [];
        $tachesDates = $this->createQueryBuilder('td')
            ->where('td.tachesAction = :tachesAction')
            ->setParameter('tachesAction',$tachesAction);

        if ($tachesDate)
            $tachesDates = $tachesDates
                ->andWhere('td.id <> :id')
                ->setParameter('id',$tachesDate->getId());

        if ($dossier)
        {
            $tachesDatesEnableds = $this->getEntityManager()->getRepository('AppBundle:TachesEntity')
                ->getTachesDateEnabledIds($tachesAction,$dossier);

            $tachesDates = $tachesDates
                ->andWhere('(td.dossier IS NULL AND td.id IN (:tdEnableds) OR (td.dossier = :dossier))')
                ->setParameter('tdEnableds',$tachesDatesEnableds)
                ->setParameter('dossier',$dossier);
        }
        else
        {
            $tachesDates = $tachesDates
                ->andWhere('td.dossier IS NULL');
        }

        /** @var TachesDate[] $tachesDates */
        $tachesDates = $tachesDates->getQuery()->getResult();

        foreach ($tachesDates as $date)
            $moisUsed = array_merge($moisUsed,json_decode($date->getClotures()));

        return $moisUsed;
    }

    /**
     * @param TachesItem $tachesItem
     * @param Dossier $dossier
     * @return int
     */
    public function getStatus(TachesItem $tachesItem,Dossier $dossier)
    {
        /** @var TachesDate[] $tachesDates */
        $tachesDates = $this->createQueryBuilder('td')
            ->join('td.tachesAction','ta')
            ->where('ta.tachesItem = :tachesItem')
            ->andWhere('(td.dossier IS NULL OR td.dossier = :dossier)')
            ->setParameters([
                'tachesItem' => $tachesItem,
                'dossier' => $dossier
            ])
            ->getQuery()
            ->getResult();

        $status = -1;
        foreach ($tachesDates as $tachesDate)
        {
            if ($tachesDate->getDossier()) $status = 1;
            else
            {
                $tachesEntity = $this->getEntityManager()->getRepository('AppBundle:TachesEntity')
                    ->getTachesEntity($tachesDate,$dossier);

                if ($tachesEntity)
                {
                    if ($status == -1 && $tachesEntity->getStatus() == 0) $status = 0;
                    elseif (($status == -1 || $status == 0) && $tachesEntity->getStatus() == 1) $status = 1;
                }
            }

            if ($status == 1) break;
        }

        return $status;
    }

    /**
     * @param TachesItem $tachesItem
     * @param Dossier $dossier
     * @return bool
     */
    public function tachesItemIsToDossier(TachesItem $tachesItem, Dossier $dossier)
    {
        /** @var TachesDate[] $results */
        $results = $this->createQueryBuilder('td')
            ->join('td.tachesAction','ta')
            ->where('ta.tachesItem = :tachesItem')
            ->setParameter('tachesItem',$tachesItem)
            ->getQuery()
            ->getResult();

        $clotures = [];
        foreach ($results as $result) $clotures = array_merge($clotures,json_decode($result->getClotures()));
        return in_array($dossier->getCloture(),$clotures);
    }

    public function tachesActionIsToDossier(TachesAction $tachesAction,Dossier $dossier)
    {
        /** @var TachesDate[] $results */
        $results = $this->createQueryBuilder('td')
            ->where('td.tachesAction = :tachesAction')
            ->setParameter('tachesAction',$tachesAction)
            ->getQuery()
            ->getResult();
        $clotures = [];
        foreach ($results as $result) $clotures = array_merge($clotures,json_decode($result->getClotures()));

        return in_array($dossier->getCloture(),$clotures);
    }

    /**
     * @param TachesDate $tachesDate
     * @param Dossier $dossier
     * @param \DateTime $periode
     * @return object
     */
    public function getDatesInYear(TachesDate $tachesDate,Dossier $dossier, $periode)
    {
        $cloture = $dossier->getCloture();
        if (!$cloture || $cloture == 0) $cloture = 12;

        $annee = intval($periode->format('Y'));
        $formule = $tachesDate->getFormule();
        $additiffs = [];
        /** @var \DateTime[] $bases */
        $bases = null;
        /** @var \DateTime[] $dates */
        $dates = [];

        if (intval($tachesDate->getInfoperdos()) == 1)
        {
            $declaration = preg_match("#D[eaé]claration#ui", $tachesDate->getTachesAction()->getTacheListeAction()->getNom());
            if ($declaration)
            {
                $jour_declaration = intval($dossier->getTvaDate());
                $periode_declaration = $dossier->getTvaMode();
                if ($jour_declaration && trim($jour_declaration) != '')
                {
                    // 5e jour de 5e mois
                    if ($jour_declaration == 55)
                    {
                        $mois = $cloture + 5;
                        if ($mois > 12) $mois -= 12;
                        $mois = str_pad($mois, 2, "0", STR_PAD_LEFT);
                        $value = \DateTime::createFromFormat("d/m/Y", "05/$mois/$annee");
                        $dates[] = $value->setTime(0,0,0);
                    }
                    else
                    {
                        //Mensuel
                        $jour = str_pad($jour_declaration, 2, "0", STR_PAD_LEFT);
                        $mois = str_pad(strval($cloture), 2, "0", STR_PAD_LEFT);
                        $anneeMoins = $annee - 1;
                        $value = \DateTime::createFromFormat("d/m/Y", "$jour/$mois/$anneeMoins");
                        $value->setTime(0,0,0);

                        $tvaMode = intval($dossier->getTvaMode());
                        //0:Accomptes semestriels;1:Accomptes trimestriels;2:Paiement mensuels;3:Paiement trimestriels

                        for ($i = 1; $i <= 24; $i++)
                        {
                            $v = clone  $value;
                            $v->setTime(0,0,0);

                            if (in_array($tvaMode,[2,3]))
                            {
                                $coeff = ($tvaMode == 2) ? 1 : 3;
                                $v->add(new \DateInterval('P'.($i*$coeff).'M'));
                                $dates[] = $v;
                            }
                        }
                    }
                }
            }
        }
        else
        {
            $formule = str_replace(' ','',$formule);
            $formule .= ' ';
            $baseDate = preg_match("#^\[[0-9]{2}/[0-9]{2}#", $formule);
            $baseCloture = preg_match("#^Cl#i", $formule);
            $baseDebutExercice = preg_match("#^De#i", $formule);

            preg_match("#[0-9|+|-]{1,}Jo#i", $formule, $adds);
            foreach ($adds as $add) if (trim($add) != '') $additiffs[] = $this->getAdditif($add, 0);
            preg_match("#[0-9|+|-]{1,}J[^o]#i", $formule, $adds);
            foreach ($adds as $add) if (trim($add) != '') $additiffs[] = $this->getAdditif($add, 1);
            preg_match("#[0-9|+|-]{1,}M#i", $formule, $adds);
            foreach ($adds as $add) if (trim($add) != '') $additiffs[] = $this->getAdditif($add, 2);
            /** @var \DateTime $base */
            $base = null;

            //$jour = intval($tachesDate->getJour());
            if ($baseDate)
            {
                preg_match("#^\[[0-9]{2}/[0-9]{2}]#", $formule, $baseStr);
                $baseStr = str_replace("[", '', $baseStr[0]);
                $baseStr = str_replace("]", '', $baseStr);
                $base = \DateTime::createFromFormat('d/m/Y',$baseStr.'/'.$annee);
            }
            else
            {
                $clotures = $this->getEntityManager()->getRepository('AppBundle:TbimagePeriode')
                    ->getAnneeMoisExercices($dossier,$annee);
                if ($baseCloture) $base = $clotures->c;
                elseif ($baseDebutExercice) $clotures->d;
            }

            $base = $base->setTime(0,0,0);
            $bases[] = $base;
            $date = clone $base;
            $date = $date->setTime(0,0,0);
            foreach ($additiffs as $additiff)
            {
                $i = 0;
                $code = $additiff->t == 2 ? 'P1M' : 'P1D';
                while ($i < $additiff->v)
                {
                    if ($additiff->s) $date->add(new \DateInterval($code));
                    else $date->add(new \DateInterval($code));

                    if ($additiff->t == 0 && ($date->format('w') == 6 && $date->format('w') == 0)) continue;
                    $i++;
                }
            }

            $dates[] = $date;
        }

        return (object)
        [
            'tachesDate' => $tachesDate,
            'date' => $dates
        ];
    }

    /**
     * @param string $formule
     * @param int $type
     * @return object
     */
    private function getAdditif($formule = '', $type = 0)
    {
        /**
         * $type 0:jo , 1:j , 2:mois
         */
        $iteration = intval($formule);
        if ($iteration == 0)
        {
            $iteration = 1;
            if (substr($formule,0,1) == '-') $iteration *= -1;
        }
        return (object)
        [
            's' => $iteration > 0,
            'v' => abs($iteration),
            't' => $type
        ];
    }

    /**
     * @return Client[]
     */
    public function getClientsInTaches()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $req = '
            SELECT DISTINCT s.client_id FROM taches_date td
            JOIN dossier d ON (d.id = td.dossier_id)
            JOIN site s ON (s.id = d.site_id)
            WHERE td.dossier_id IS NOT NULL 
        ';

        $prep = $pdo->prepare($req);
        $prep->execute();
        $res = $prep->fetchAll();

        /** @var int[] $ids */
        $ids = [];
        foreach ($res as $re)
        {
            $ids[] = $re->client_id;
        }

        return $this->getEntityManager()->getRepository('AppBundle:Client')
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids',$ids)
            ->getQuery()
            ->getResult();
    }
}