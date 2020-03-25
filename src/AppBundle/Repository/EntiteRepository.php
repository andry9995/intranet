<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 13:56
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Entite;

class EntiteRepository extends EntityRepository
{
    public function getAllEntite()
    {
        $entites = $this->getEntityManager()->getRepository('AppBundle:Entite')
            ->createQueryBuilder('e')
            ->where('e.supprimer != :supprimer')
            ->setParameter('supprimer', 1)
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $entites;
    }
}