<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TacheDomaineRepository extends EntityRepository
{
    /**
     * Listes de tous les domaines des taches
     *
     * @return array
     */
    public function getAllTacheDomaine()
    {
        $domaines = $this->getEntityManager()->getRepository('AppBundle:TacheDomaine')
            ->createQueryBuilder('d')
            ->orderBy('d.domaine', 'ASC')
            ->getQuery()
            ->getResult();

        return $domaines;
    }
}