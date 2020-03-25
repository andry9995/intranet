<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 08/11/2018
 * Time: 14:52
 */

namespace AppBundle\Repository;

use AppBundle\Entity\TachesAction;
use AppBundle\Entity\TachesItem;
use Doctrine\ORM\EntityRepository;

class TachesActionRepository extends EntityRepository
{
    public function getResteTacheListeAction(TachesItem $tachesItem, TachesAction $tachesAction = null)
    {
        $tacheListeActions = $this->getEntityManager()->getRepository('AppBundle:TacheListeAction')
            ->getAllAction();

        /** @var TachesAction[] $tachesActionExistants */
        $tachesActionExistants = $this->createQueryBuilder('ta')
            ->where('ta.tachesItem = :tachesItem')
            ->setParameter('tachesItem',$tachesItem)
            ->getQuery()
            ->getResult();

        $tachesActionExistantsIds = [];
        foreach ($tachesActionExistants as $tachesActionExistant)
            $tachesActionExistantsIds[] = $tachesActionExistant->getTacheListeAction()->getId();

        $results = [];
        foreach ($tacheListeActions as $tacheListeAction)
        {
            if (!in_array($tacheListeAction->getId(),$tachesActionExistantsIds) ||
                ($tachesAction && $tacheListeAction->getId() == $tachesAction->getTacheListeAction()->getId()))
                $results[] = $tacheListeAction;
        }

        return $results;
    }
}