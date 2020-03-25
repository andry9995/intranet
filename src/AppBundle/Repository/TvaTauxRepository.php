<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 18/07/2018
 * Time: 10:40
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\TvaTaux;
use Doctrine\ORM\EntityRepository;

class TvaTauxRepository extends EntityRepository
{
    /**
     * @param bool $status
     * @return array
     */
    public function getTvaTaux($status = true){
        return $this->createQueryBuilder('t')
            ->where('t.actif = :status')
            ->setParameter('status', $status)
            ->orderBy('t.taux')
            ->getQuery()
            ->getResult();
    }

    public function getTvaTauxByTaux($taux){
        $tvas = $this->createQueryBuilder('tt')
            ->where('tt.taux = :taux')
            ->andWhere('tt.actif = 1')
            ->setParameter('taux', $taux)
            ->getQuery()
            ->getResult();

        if(count($tvas) > 0)
            return $tvas[0];

        return null;
    }
}