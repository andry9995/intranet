<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 10:44
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TypeAchatVenteRepository extends EntityRepository
{
    public function getTypeAchatVenteByLibelle($libelle){
        $typeAchatVentes = $this->createQueryBuilder('tav')
            ->where('tav.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($typeAchatVentes) > 0)
            return $typeAchatVentes[0];

        return null;
    }

}