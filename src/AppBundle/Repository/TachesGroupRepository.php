<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 16/11/2018
 * Time: 10:32
 */

namespace AppBundle\Repository;

use AppBundle\Entity\TachesGroup;
use Doctrine\ORM\EntityRepository;

class TachesGroupRepository extends EntityRepository
{
    /**
     * @return TachesGroup[]
     */
    public function getListe()
    {
        return $this->createQueryBuilder('tg')
            ->orderBy('tg.nom')
            ->getQuery()
            ->getResult();
    }
}