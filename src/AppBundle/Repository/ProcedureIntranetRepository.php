<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 17/05/2016
 * Time: 09:50
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProcedureIntranetRepository extends EntityRepository
{
    /**
     * Listes de toutes les procedures
     *
     * @return array
     */
    public function getAllProcedure()
    {
        $procedures = $this->getEntityManager()->getRepository('AppBundle:ProcedureIntranet')
            ->createQueryBuilder('p')
            ->orderBy('p.numero', 'ASC')
            ->getQuery()
            ->getResult();

        return $procedures;
    }
}