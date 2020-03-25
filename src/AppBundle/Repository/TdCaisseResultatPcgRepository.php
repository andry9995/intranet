<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 26/07/2018
 * Time: 10:46
 */

namespace AppBundle\Repository;


use AppBundle\Entity\CaisseNature;
use Doctrine\ORM\EntityRepository;

class TdCaisseResultatPcgRepository extends EntityRepository
{
    public function getTdCaisseResultatPcgByNature(CaisseNature $caisseNature){
        $qb = $this->createQueryBuilder('td')
            ->where('td.caisseNature = :caissenature')
            ->setParameter('caissenature', $caisseNature);

        return $qb->getQuery()->getResult();

    }

}