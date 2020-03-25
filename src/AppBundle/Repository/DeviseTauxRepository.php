<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 15/07/2019
 * Time: 13:53
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Devise;
use Doctrine\ORM\EntityRepository;

class DeviseTauxRepository extends EntityRepository
{
    public function getMostRecentDevise(Devise $devise){
        $res =  $this->createQueryBuilder('dt')
            ->where('dt.devise = :devise')
            ->orderBy('dt.dateDevise', 'DESC')
            ->setParameter('devise', $devise)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if(count($res) > 0)
            return $res[0];

        return null;
    }

}