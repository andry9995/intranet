<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 11:14
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class NatureRepository extends EntityRepository
{
    public function getNatureByLibelle($libelle){
        $natures = $this->createQueryBuilder('n')
            ->where('n.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($natures) > 0)
            return $natures[0];

        return null;
    }
}