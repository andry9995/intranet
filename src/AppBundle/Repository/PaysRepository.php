<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 10:32
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Pays;
use Doctrine\ORM\EntityRepository;

class PaysRepository extends EntityRepository
{
    /**
     * @param $nom
     * @return Pays, null
     */
    public function getPaysByNom($nom){
        $pays = $this->createQueryBuilder('p')
            ->where('p.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getResult();

        if(count($pays) > 0)
            return $pays[0];

        return null;
    }
}