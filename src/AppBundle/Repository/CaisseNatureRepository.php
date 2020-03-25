<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 18/07/2018
 * Time: 10:52
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class CaisseNatureRepository extends EntityRepository
{
    /**
     * @param int $type
     * @return array
     */
    public function getCaisseNature($type=0){

        // 0: entrée, 1:sortie
        if($type < 2) {
            $qb = $this->createQueryBuilder('n')
                ->where('n.type = :type')
                ->orWhere('n.type = 2')
                ->setParameter('type', $type)
                ->orderBy('n.type')
                ->addOrderBy('n.libelle');
        }
        else{
            $qb = $this->createQueryBuilder('n')
                ->orderBy('n.type')
                ->addOrderBy('n.libelle');
        }

        return $qb->getQuery()
            ->getResult();
    }

}