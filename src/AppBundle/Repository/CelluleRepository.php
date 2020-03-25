<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CelluleRepository extends EntityRepository
{
    /**
     * Listes de toutes les cellules
     *
     * @return array
     */
    public function getAllCellule()
    {
        $cellules = $this->getEntityManager()->getRepository('AppBundle:Cellule')
            ->createQueryBuilder('c')
            ->where('c.supprimer != :supprimer')
            ->setParameter('supprimer', 1)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $cellules;
    }

    /**
     * Listes des cellules d'un service
     *
     * @param $serviceId
     * @return array
     */
    public function getCelluleService($serviceId)
    {
        $cellules = $this->getEntityManager()
            ->getRepository('AppBundle:Cellule')
            ->createQueryBuilder('c')
            ->leftJoin('c.service', 's')
            ->where('c.supprimer != :supprimer')
            ->andWhere('s.id = :id')
            ->setParameter('supprimer', 1)
            ->setParameter('id', $serviceId)
            ->getQuery()
            ->getResult();

        return $cellules;
    }
}