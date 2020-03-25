<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 19/03/2019
 * Time: 14:11
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TypeTiersRepository extends EntityRepository
{
    public function getTypeTiersBanques(){
        return $this->createQueryBuilder('t')
            ->where('t.saisieBanque = 1')
            ->orderBy('t.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTypeTiersByLibelle($libelle){
        $libelle = strtolower($libelle);

        $typeTiers = $this->createQueryBuilder('t')
            ->where('t.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($typeTiers) > 0){
            return $typeTiers[0];
        }

        return null;
    }


}