<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 18/07/2018
 * Time: 11:56
 */

namespace AppBundle\Repository;


use AppBundle\Entity\TacheListeAction;
use Doctrine\ORM\EntityRepository;

class TacheListeActionRepository extends EntityRepository
{
    /**
     * @return TacheListeAction[]
     */
    public function getAllAction()
    {
        $actions = $this->getEntityManager()
            ->getRepository('AppBundle:TacheListeAction')
            ->createQueryBuilder('t')
            ->select('t')
            ->orderBy('t.nom')
            ->getQuery()
            ->getResult();
        return $actions;
    }
}