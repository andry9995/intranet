<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 10:57
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class ModeReglementRepository extends EntityRepository
{
    public function getModeReglementByLibelle($libelle){
        $modeReglements = $this->createQueryBuilder('mr')
            ->where('mr.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($modeReglements) > 0)
            return $modeReglements[0];

        return null;
    }

}