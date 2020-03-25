<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 05/09/2017
 * Time: 11:40
 */

namespace AppBundle\Repository;

use AppBundle\Entity\TacheLegale;
use Doctrine\ORM\EntityRepository;

class TacheLegaleActionRepository extends EntityRepository
{
    public function getByTache(TacheLegale $tache)
    {
        $actions = $this->getEntityManager()
            ->getRepository('AppBundle:TacheLegaleAction')
            ->createQueryBuilder('tla')
            ->select('tla')
            ->innerJoin('tla.tacheLegale', 'tache_legale')
            ->addSelect('tache_legale')
            ->where('tache_legale = :tache_legale')
            ->setParameters(array(
                'tache_legale' => $tache,
            ))
            ->orderBy('tla.nom')
            ->getQuery()
            ->getResult();

        return $actions;
    }
}