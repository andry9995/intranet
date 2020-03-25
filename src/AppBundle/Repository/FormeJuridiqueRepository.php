<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 31/08/2017
 * Time: 13:56
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FormeJuridiqueRepository extends EntityRepository
{

    public function getAll()
    {
        $formes = $this->getEntityManager()
            ->getRepository('AppBundle:FormeJuridique')
            ->createQueryBuilder('forme_juridique')
            ->select('forme_juridique')
            ->orderBy('forme_juridique.libelle')
            ->getQuery()
            ->getResult();
        return $formes;
    }
}