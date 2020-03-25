<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 07/08/2019
 * Time: 09:47
 */

namespace AppBundle\Repository;


use AppBundle\Entity\NdfTypeVehicule;
use Doctrine\ORM\EntityRepository;

class NdfIndemniteKmRepository extends EntityRepository
{
    public function getIkByTypeVehiculeExercice(NdfTypeVehicule $typeVehicule, $exercice){
        return $this->createQueryBuilder('ik')
            ->join('ik.ndfDistanceIndemniteKm','distance')
            ->join('ik.ndfPuissanceFiscal', 'puissance')
            ->where('ik.exercice = :exercice')
            ->andWhere('distance.ndfTypeVehicule = :typevehicule')
            ->andWhere('puissance.ndfTypeVehicule = :typevehicule')
            ->setParameter('exercice', $exercice)
            ->setParameter('typevehicule', $typeVehicule)
            ->getQuery()
            ->getResult();
    }

}