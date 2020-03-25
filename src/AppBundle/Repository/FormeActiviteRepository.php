<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 31/08/2017
 * Time: 13:34
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FormeActiviteRepository extends EntityRepository
{
    public function getAll()
    {
        $activites = $this->getEntityManager()
            ->getRepository('AppBundle:FormeActivite')
            ->createQueryBuilder('forme_activite')
            ->select('forme_activite')
            ->orderBy('forme_activite.libelle')
            ->getQuery()
            ->getResult();
        return $activites;
    }
}