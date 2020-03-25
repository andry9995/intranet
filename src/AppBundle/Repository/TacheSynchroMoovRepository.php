<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 27/09/2018
 * Time: 17:14
 */

namespace AppBundle\Repository;

use AppBundle\Entity\TachesSynchroMoov;
use AppBundle\Entity\TacheSynchro;
use AppBundle\Entity\TacheSynchroMoov;
use Doctrine\ORM\EntityRepository;

class TacheSynchroMoovRepository extends EntityRepository
{
    /**
     * @param TacheSynchro $tacheSynchro
     * @return TachesSynchroMoov
     */
    public function getLastMoov(TacheSynchro $tacheSynchro)
    {
        /** @var TacheSynchroMoov $result */
        return $this->createQueryBuilder('tsm')
            ->where('tsm.tacheSynchro = :tacheSynchro')
            ->setParameter('tacheSynchro',$tacheSynchro)
            ->orderBy('tsm.id','DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param TacheSynchro $tacheSynchro
     */
    public function removeAllItems(TacheSynchro $tacheSynchro)
    {
        $tacheSynchroMoovs = $this->createQueryBuilder('tsm')
            ->where('tsm.tacheSynchro = :tacheSynchro')
            ->setParameter('tacheSynchro',$tacheSynchro)
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();
        foreach ($tacheSynchroMoovs as $tacheSynchroMoov)
            $em->remove($tacheSynchroMoov);

        $em->flush();
    }
}