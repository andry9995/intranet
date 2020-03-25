<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/11/2018
 * Time: 16:37
 */

namespace AppBundle\Repository;

use AppBundle\Entity\RegimeTva;
use Doctrine\ORM\EntityRepository;

class RegimeTvaRepository extends EntityRepository
{
    /**
     * @return RegimeTva[]
     */
    public function getAlls()
    {
        return $this->createQueryBuilder('rt')
            ->orderBy('rt.libelle')
            ->getQuery()
            ->getResult();
    }
}