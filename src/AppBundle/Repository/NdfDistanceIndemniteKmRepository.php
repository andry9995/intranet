<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 07/08/2019
 * Time: 09:41
 */

namespace AppBundle\Repository;


use AppBundle\Entity\NdfTypeVehicule;
use Doctrine\ORM\EntityRepository;

class NdfDistanceIndemniteKmRepository extends EntityRepository
{
    public function getDistanceByTypeVehicule(NdfTypeVehicule $typeVehicule){
        return $this->createQueryBuilder('d')
            ->where('d.ndfTypeVehicule = :typevehicule')
            ->orderBy('d.min')
            ->setParameter('typevehicule', $typeVehicule)
            ->getQuery()
            ->getResult();
    }

}