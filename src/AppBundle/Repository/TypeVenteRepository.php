<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 11:08
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TypeVenteRepository extends EntityRepository
{
    public function getTypeVenteByLibelle($libelle){
        $typeVentes = $this->createQueryBuilder('tv')
            ->where('tv.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($typeVentes) > 0)
            return $typeVentes[0];

        return null;
    }
}