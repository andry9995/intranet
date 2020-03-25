<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 31/07/2019
 * Time: 16:57
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class VehiculeRepository extends EntityRepository
{
    public function getVehiculeByDossier(Dossier $dossier){
        return $this->createQueryBuilder('v')
            ->where('v.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->orderBy('v.vehiculeMarque')
            ->getQuery()
            ->getResult();
    }
}