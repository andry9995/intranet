<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 16/11/2018
 * Time: 13:53
 */

namespace AppBundle\Repository;


use AppBundle\Entity\RegimeFiscal;
use AppBundle\Entity\TachesGroup;
use AppBundle\Entity\TachesGroupRegimeFiscal;
use AppBundle\Entity\TachesItem;
use Doctrine\ORM\EntityRepository;

class TachesGroupRegimeFiscalRepository extends EntityRepository
{
    /**
     * @param TachesGroup $tachesGroup
     * @return TachesGroupRegimeFiscal[]
     */
    public function getTachesGroupRegimeFiscal(TachesGroup $tachesGroup)
    {
        return $this->createQueryBuilder('tg')
            ->where('tg.tachesGroup = :tachesGroup')
            ->setParameter('tachesGroup',$tachesGroup)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param TachesItem $tachesItem
     * @param RegimeFiscal[] $regimeFiscals
     * @return bool
     */
    public function isInRegimeFiscal(TachesItem $tachesItem, $regimeFiscals)
    {
        $regimeFiscalsIds = [];
        foreach ($regimeFiscals as $regimeFiscal) $regimeFiscalsIds[] = $regimeFiscal->getId();

        $tachesGroupRegimeFiscals = $this->getTachesGroupRegimeFiscal($tachesItem->getTaches()->getTachesGroup());

        $result = false;
        foreach ($tachesGroupRegimeFiscals as $tachesGroupRegimeFiscal)
        {
            if (in_array($tachesGroupRegimeFiscal->getRegimeFiscal()->getId(),$regimeFiscalsIds))
            {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @param TachesGroup $tachesGroup
     * @return RegimeFiscal[]
     */
    public function getRegimeFiscals(TachesGroup $tachesGroup)
    {
        $tachesGroupRegimeFiscals = $this->getTachesGroupRegimeFiscal($tachesGroup);
        $results = [];
        foreach ($tachesGroupRegimeFiscals as $tachesGroupRegimeFiscal)
            $results[$tachesGroupRegimeFiscal->getRegimeFiscal()->getId()] = $tachesGroupRegimeFiscal->getRegimeFiscal();

        return $results;
    }
}