<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 08/11/2016
 * Time: 17:30
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CategorieFiscaleSocialeRepository extends EntityRepository
{
    public function getAllOrderByName()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:CategorieFiscaleSociale')
            ->createQueryBuilder('cfs')
            ->select('cfs')
            ->orderBy('cfs.nom')
            ->getQuery()
            ->getResult();
    }
    public function getAllOrderByType()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:CategorieFiscaleSociale')
            ->createQueryBuilder('cfs')
            ->select('cfs')
            ->orderBy('cfs.type')
            ->addOrderBy('cfs.nom')
            ->getQuery()
            ->getResult();
    }
    public function getAllFiscale()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:CategorieFiscaleSociale')
            ->createQueryBuilder('cfs')
            ->select('cfs')
            ->where('cfs.type = :type')
            ->setParameter('type', 1)
            ->orderBy('cfs.nom')
            ->getQuery()
            ->getResult();
    }
    public function getAllSociale()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:CategorieFiscaleSociale')
            ->createQueryBuilder('cfs')
            ->select('cfs')
            ->where('cfs.type = :type')
            ->setParameter('type', 2)
            ->orderBy('cfs.nom')
            ->getQuery()
            ->getResult();
    }
}