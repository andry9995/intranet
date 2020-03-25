<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 19/07/2017
 * Time: 11:56
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\Lot;
use Doctrine\ORM\EntityRepository;

class PrioriteLotRepository extends EntityRepository
{
    public function getPrioriteLot(Lot $lot)
    {
        /** @var \AppBundle\Entity\PrioriteLot[] $priorite */
        $priorite = $this->getEntityManager()
            ->getRepository('AppBundle:PrioriteLot')
            ->createQueryBuilder('priorite_lot')
            ->select('priorite_lot')
            ->where('priorite_lot.lot = :lot')
            ->setParameters(array(
                'lot' => $lot
            ))
            ->getQuery()
            ->getResult();
        if ($priorite && count($priorite) > 0) {
            return $priorite[0]->getDelai();
        }
        return null;
    }

    /**
     * @param Dossier $dossier
     * @return array
     * @throws \Exception
     */
    public function getPrioriteDossier(Dossier $dossier)
    {
        $min = new \DateTime();
        $min->sub(new \DateInterval('P1D'));
        $min->setTime(0,0);
        $tache_legales = $this->getEntityManager()
            ->getRepository('AppBundle:TacheLegale')
            ->getActions($dossier);
        $param_color = $this->getEntityManager()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'priorite_color'
            ));

        /** @var \DateTime $delai */
        $delai = null;
        $tache = '';
        if (count($tache_legales) > 0) {
            if ($tache_legales[0]['date'] && $tache_legales[0]['date'] >= $min) {
                $delai = $tache_legales[0]['date'];
                $tache = $tache_legales[0]['tache']->getNom() . ' - ' . $tache_legales[0]['action']->getNom();
            }
        }
        foreach ($tache_legales as $tache_legale) {
            if ($tache_legale['date'] && $tache_legale['date'] >= $min && ($tache_legale['date'] < $delai || $delai === null)) {
                $delai = $tache_legale['date'];
                $tache = $tache_legale['tache']->getNom() . ' - ' . $tache_legale['action']->getNom();
            }
        }
        $color_default = $this->getEntityManager()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'default_color'
            ));
        if ($color_default && isset($color_default->getParamValue()[0])) {
            $color = $color_default->getParamValue()[0];
        } else {
            $color = "#696dcb";
        }

        $now = new \DateTime();
        $now->setTime(0,0);
        $interval = 9000;

        $priorite_libre = $this->getEntityManager()
            ->getRepository('AppBundle:TacheDossier')
            ->getTachePlusProche($dossier);

        if ($priorite_libre['delai']) {
            if (($delai && $delai > $priorite_libre['delai']) || !$delai) {
                $delai = $priorite_libre['delai'];
                $tache = $priorite_libre['tache'];
            }
        }

        if ($delai) {
            $delai->setTime(0, 0);
            $delai = $this->checkWeekend(new \DateTime($delai->format('Y-m-d')));

            if ($delai < $now) {
                $interval = 0;
            } else {
                /**  Calculer NB Heure entre délai et date du jour */
                $interval = $this->nbHeureTravail(clone $now, clone $delai);
            }
            if ($interval < 0) {
                if (isset($param_color->getParamValue()[0])) {
                    $color = $param_color->getParamValue()[0]['color'];
                }
            } else {
                foreach ($param_color->getParamValue() as $param) {
                    if ($interval >= $param['min'] && $interval <= $param['max']) {
                        $color = $param['color'];
                    }
                }
            }
        }
        return ['delai' => $delai, 'tache' => $tache, 'color' => $color, 'order' => $interval];
    }

    public function getNbDayWeekend(\DateTime $start, \DateTime $end)
    {
        $inverted = false;
        $nb_weekend = 0;
        if ($start > $end) {
            $inverted = true;
            $tmp = clone $start;
            $start = clone $end;
            $end = clone $tmp;
            unset($tmp);
        }
        while ($start < $end) {
            $start->add(new \DateInterval('P1D'));
            if ($start->format('N') == 6 || $start->format('N') == 0) {
                $nb_weekend++;
            }
        }

        if ($inverted) {
            return -$nb_weekend;
        }
        return $nb_weekend;
    }

    public function checkWeekend(\DateTime $date)
    {
        $tmp = clone $date;
        if ($tmp->format('N') == 6) {
            $tmp->add(new \DateInterval('P2D'));
        }
        if ($tmp->format('N') == 0) {
            $tmp->add(new \DateInterval('P1D'));
        }
        return $tmp;
    }

    public function nbHeureTravail(\DateTime $start, \DateTime $end)
    {
        $debut = new \DateTime($start->format('Y-m-d'));
        $fin = new \DateTime($end->format('Y-m-d'));
        $nbHeure = 0;
        $hours = [];
        $param = $this->getEntityManager()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'priorite_jour'
            ));
//        {"weekday":1,"checked":true,"heure":8}
        if ($param) {
            foreach ($param->getParamValue() as $value) {
                $hours[$value['weekday']] = $value;
            }
        }

        while ($debut < $fin) {
            $weekday = $debut->format('N');
            if (isset($hours[$weekday]) && $hours[$weekday]['checked'] === true) {
                $nbHeure += intval($hours[$weekday]['heure']);
            }
            $debut->add(new \DateInterval('P1D'));
        }

        return $nbHeure;
    }
}