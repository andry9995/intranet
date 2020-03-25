<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 29/11/2018
 * Time: 09:29
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class SaisiseControleRepository extends EntityRepository
{

    public function getListSaisieControleByList($imageList, $banqueCompte){

        return $this->createQueryBuilder('d')
            ->where('d.image IN (:image)')
            ->andWhere('d.banqueCompte = :banqueCompte')
            ->setParameter('image', $imageList)
            ->setParameter('banqueCompte', $banqueCompte)
//            ->orderBy('d.periodeD1')
            ->getQuery()
            ->getResult();

    }

}