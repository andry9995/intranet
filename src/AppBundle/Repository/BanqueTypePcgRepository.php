<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 12/04/2019
 * Time: 13:57
 */

namespace AppBundle\Repository;


use AppBundle\Entity\BanqueType;
use Doctrine\ORM\EntityRepository;

class BanqueTypePcgRepository extends EntityRepository
{
    public function getBanqueTypePcgByTypes(BanqueType $banqueType,array $types){
        return $this->createQueryBuilder('bt')
            ->where('bt.type in (:types)')
            ->andWhere('bt.banqueType = :banquetype')
            ->setParameter('banquetype', $banqueType)
            ->setParameter('types', implode(',', $types))
            ->getQuery()
            ->getResult();
    }
}