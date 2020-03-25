<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 06/05/2016
 * Time: 14:57
 */

namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;

class AccesOperateurRepository extends EntityRepository
{
    /**
     * Liste de rôles des opérateurs
     *
     * @return array
     */
    public function getAllAcessOperateur() {
        $roles = $this->getEntityManager()->getRepository('AppBundle:AccesOperateur')
            ->createQueryBuilder('a')
            ->orderBy('a.libelle', 'ASC')
            ->getQuery()
            ->getResult();

        return $roles;
    }
}