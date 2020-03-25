<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DepartementRepository extends EntityRepository
{
    /**
     * Liste des départements
     *
     * @return array
     */
    public function getAllDepartement()
    {
        $departements = $this->getEntityManager()
            ->getRepository('AppBundle:Departement')
            ->createQueryBuilder('d')
            ->where('d.supprimer != :supprimer')
            ->setParameter('supprimer', 1)
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $departements;
    }

    /**
     * Liste des départements d'une entité
     * 
     * @param $entiteId
     * @return array
     */
    public function getDepartementEntite($entiteId)
    {
        $departements = $this->getEntityManager()
            ->getRepository('AppBundle:Departement')
            ->createQueryBuilder('d')
            ->leftJoin('d.entite', 'e')
            ->where('d.supprimer != :supprimer')
            ->andWhere('e.id = :id')
            ->setParameter('supprimer', 1)
            ->setParameter('id', $entiteId)
            ->getQuery()
            ->getResult();

        return $departements;
    }
}