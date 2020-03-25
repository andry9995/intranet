<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 10/11/2016
 * Time: 14:36
 */

namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;

class TypeActiviteRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:TypeActivite')
            ->createQueryBuilder('ta')
            ->select('ta')
            ->groupBy('ta.libelle')
            ->getQuery()
            ->getResult();
    }
}