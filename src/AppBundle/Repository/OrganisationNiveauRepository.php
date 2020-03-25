<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 29/01/2018
 * Time: 15:47
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class OrganisationNiveauRepository extends EntityRepository
{
    public function getAll()
    {
        $titres = $this->getEntityManager()
            ->getRepository('AppBundle:OrganisationNiveau')
            ->createQueryBuilder('titre')
            ->select('titre')
            ->orderBy('titre.rang', 'ASC')
            ->getQuery()
            ->getResult();
        return $titres;
    }
}