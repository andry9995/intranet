<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 26/07/2018
 * Time: 14:56
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TdTvaPcgRepository extends EntityRepository
{
    public function  getTdTvaPcgByTypeCaisse($typecaisse = 0){
        return $this->createQueryBuilder('td')
            ->where('td.typeCaisse = :typecaisse')
            ->setParameter('typecaisse', $typecaisse)
            ->orderBy('td.compte')
            ->getQuery()
            ->getResult();

    }

}