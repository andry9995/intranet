<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 10/11/2016
 * Time: 15:03
 */

namespace AppBundle\Repository;
use AppBundle\Entity\RegimeFiscal;
use Doctrine\ORM\EntityRepository;

class RegimeFiscalRepository extends EntityRepository
{
    /**
     * @return RegimeFiscal[]
     */
    public function getAll()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:RegimeFiscal')
            ->createQueryBuilder('rf')
            ->select('rf')
            ->orderBy('rf.libelle')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RegimeFiscal[]
     */
    public function getAllForTacheLegale()
    {
        return $this->getEntityManager()
            ->getRepository('AppBundle:RegimeFiscal')
            ->createQueryBuilder('rf')
            ->where('rf.status = :status')
            ->setParameter('status', 1)
            ->orderBy('rf.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RegimeFiscal[]
     */
    public function getForAllTache()
    {
        $codes = ['CODE_BNC','CODE_BIC_IS','CODE_BIC_IR','CODE_BA'];
        return $this->createQueryBuilder('rf')
            ->where('rf.code IN (:codes)')
            ->setParameter('codes',$codes)
            ->getQuery()
            ->getResult();
    }
}