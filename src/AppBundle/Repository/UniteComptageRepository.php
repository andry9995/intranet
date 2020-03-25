<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UniteComptageRepository extends EntityRepository
{
    /**
     * Listes de toutes les unités de comptage
     *
     * @return array
     */
    public function getAllUnite()
    {
        $unites = $this->getEntityManager()->getRepository('AppBundle:UniteComptage')
            ->createQueryBuilder('u')
            ->orderBy('u.unite', 'ASC')
            ->getQuery()
            ->getResult();

        return $unites;
    }
}