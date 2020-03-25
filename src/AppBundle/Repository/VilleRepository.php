<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 30/08/2018
 * Time: 10:08
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class VilleRepository extends EntityRepository
{
    public function getVilleByCodePostal($codepostal){

        $villes = $this->createQueryBuilder('v')
            ->where('v.codePostal like :codepostal')
            ->setParameter('codepostal', '%'.$codepostal.'%')
            ->getQuery()
            ->getResult();
        $ville = null;
        if(count($villes) > 0){
            $ville = $villes[0];
        }

        return $ville;
    }
}