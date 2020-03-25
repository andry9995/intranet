<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 07/08/2019
 * Time: 09:43
 */

namespace AppBundle\Repository;


use AppBundle\Entity\NdfTypeVehicule;
use Doctrine\ORM\EntityRepository;

class NdfPuissanceFiscalRepository extends EntityRepository
{
    public function getPuissanceByTypeVehicule(NdfTypeVehicule $typeVehicule){
        return $this->createQueryBuilder('p')
            ->where('p.ndfTypeVehicule = :typevehicule')
            ->setParameter('typevehicule', $typeVehicule)
            ->getQuery()
            ->getResult();
    }
}