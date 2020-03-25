<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 01/08/2019
 * Time: 10:59
 */

namespace AppBundle\Repository;

use AppBundle\Entity\RegimeImposition;
use Doctrine\ORM\EntityRepository;

class RegimeImpositionRepository extends EntityRepository
{
    /**
     * @return RegimeImposition[]
     */
    public function getAlls()
    {
        return $this->createQueryBuilder('ri')
            ->orderBy('ri.libelle')
            ->getQuery()
            ->getResult();
    }
}