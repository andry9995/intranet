<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 11:03
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class DeviseRepository extends EntityRepository
{
    public function getDeviseByNom($nom){
        $devises = $this->createQueryBuilder('d')
            ->where('d.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getResult();

        if(count($devises) > 0)
            return $devises[0];

        return null;
    }
}