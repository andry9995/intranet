<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ServiceRepository extends EntityRepository
{
    /**
     * Liste des Services
     *
     * @return array
     */
    public function getAllService()
    {
        $services = $this->getEntityManager()->getRepository('AppBundle:Service')
            ->createQueryBuilder('s')
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $services;
    }

    /**
     * Liste des Services d'un département
     * 
     * @param $depId
     * @return array
     */
    public function getServiceDepartement($depId)
    {
        $services = $this->getEntityManager()
            ->getRepository('AppBundle:Service')
            ->createQueryBuilder('s')
            ->leftJoin('s.departement', 'd')
            ->where('s.supprimer != :supprimer')
            ->andWhere('d.id = :id')
            ->setParameter('supprimer', 1)
            ->setParameter('id', $depId)
            ->getQuery()
            ->getResult();

        return $services;
    }
}