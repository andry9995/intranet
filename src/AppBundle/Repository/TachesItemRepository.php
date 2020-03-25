<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/11/2018
 * Time: 17:13
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\RegimeFiscal;
use AppBundle\Entity\RegimeImposition;
use AppBundle\Entity\RegimeTva;
use AppBundle\Entity\Taches;
use AppBundle\Entity\TachesItem;
use Doctrine\ORM\EntityRepository;
use SensioLabs\Security\Result;

class TachesItemRepository extends EntityRepository
{
    /**
     * @param Taches $taches
     * @param TachesItem|null $tachesItem
     * @return RegimeTva[]
     */
    public function getResteRegimeTva(Taches $taches, TachesItem $tachesItem = null)
    {
        $regimeTvas = $this->getEntityManager()->getRepository('AppBundle:RegimeTva')
            ->getAlls();

        /** @var TachesItem[] $regimeTvaExistants */
        $regimeTvaExistants = $this->createQueryBuilder('ti')
            ->where('ti.taches = :taches')
            ->setParameter('taches',$taches)
            ->andWhere('ti.regimeTva IS NOT NULL')
            ->getQuery()
            ->getResult();

        $regimeTvaExistantsIds = [];
        foreach ($regimeTvaExistants as $regimeTvaExistant)
            $regimeTvaExistantsIds[] = $regimeTvaExistant->getRegimeTva()->getId();

        $results = [];
        foreach ($regimeTvas as $regimeTva)
        {
            if (!in_array($regimeTva->getId(),$regimeTvaExistantsIds) ||
                ($tachesItem && $tachesItem->getRegimeTva() && $regimeTva->getId() == $tachesItem->getRegimeTva()->getId()))
                $results[] = $regimeTva;
        }

        return $results;
    }

    /**
     * @param Taches $taches
     * @param TachesItem|null $tachesItem
     * @return RegimeImposition[]
     */
    public function getResteRegimeImposition(Taches $taches,TachesItem $tachesItem = null)
    {
        $regimeImpostions = $this->getEntityManager()->getRepository('AppBundle:RegimeImposition')
            ->getAlls();

        /** @var TachesItem[] $regimeImpostionExistants */
        $regimeImpostionExistants = $this->createQueryBuilder('ti')
            ->where('ti.taches = :taches')
            ->setParameter('taches',$taches)
            ->andWhere('ti.regimeImposition IS NOT NULL')
            ->getQuery()
            ->getResult();

        $regimeImpostionExistantsIds = [];
        foreach ($regimeImpostionExistants as $regimeImpostionExistant)
            $regimeImpostionExistantsIds[] = $regimeImpostionExistant->getRegimeImposition()->getId();

        $results = [];
        foreach ($regimeImpostions as $regimeImposition)
        {
            if (!in_array($regimeImposition->getId(),$regimeImpostionExistantsIds) ||
                ($tachesItem && $tachesItem->getRegimeImposition() && $regimeImposition->getId() == $tachesItem->getRegimeImposition()->getId()))
                $results[] = $regimeImposition;
        }

        return $results;
    }

    /**
     * @param Taches $taches
     * @return TachesItem[]
     */
    public function getTachesItems(Taches $taches)
    {
        return $this->createQueryBuilder('ti')
            ->where('ti.taches = :taches')
            ->setParameter('taches',$taches)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param RegimeFiscal[] $regimeFiscals
     * @param RegimeImposition[] $regimeImpositions
     * @param RegimeTva[] $regimesTvas
     * @return Taches[]
     */
    public function getTachesByRegimes($regimeFiscals = [], $regimeImpositions = [], $regimesTvas = [])
    {
        $tachesItems = $this->createQueryBuilder('ti')
            ->join('ti.taches','t')
            ->addSelect('t');

        $andWhere = false;
        if (count($regimeFiscals) > 0)
        {
            $tachesItems = $tachesItems
                ->where('(t.regimeFiscal IN (:regimeFiscals) OR t.regimeFiscal IS NULL)')
                ->setParameter('regimeFiscals',$regimeFiscals);

            $andWhere = true;
        }

        if (count($regimeImpositions) > 0 && count($regimesTvas) > 0)
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('
                        (ti.regimeImposition IN (:regimeImpositions) OR 
                        (ti.regimeTva IN (:regimeTvas)) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL))
                    ');
            else
                $tachesItems = $tachesItems
                    ->where('
                        (ti.regimeImposition IN (:regimeImpositions) OR 
                        (ti.regimeTva IN (:regimeTvas)) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL))                
                    ');

            $tachesItems = $tachesItems
                ->setParameter('regimeImpositions',$regimeImpositions)
                ->setParameter('regimeTvas', $regimesTvas);
        }
        elseif (count($regimeImpositions) > 0)
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('
                        (ti.regimeImposition IN (:regimeImpositions) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL))
                    ');
            else
                $tachesItems = $tachesItems
                    ->where('
                        (ti.regimeImposition IN (:regimeImpositions) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL))                
                    ');

            $tachesItems = $tachesItems
                ->setParameter('regimeImpositions',$regimeImpositions);
        }
        elseif (count($regimesTvas) > 0)
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('
                        (ti.regimeTva IN (:regimeTvas)) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL))    
                    ');
            else
                $tachesItems = $tachesItems
                    ->where('
                        (ti.regimeTva IN (:regimeTvas)) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL))                      
                    ');

            $tachesItems = $tachesItems
                ->setParameter('regimeTvas', $regimesTvas);
        }

        /** @var TachesItem[] $tachesItems */
        $tachesItems = $tachesItems
            ->getQuery()
            ->getResult();

        $taches = [];
        foreach ($tachesItems as $tachesItem)
        {
            $key = $tachesItem->getTaches()->getId();
            if (!array_key_exists($key,$taches)) $taches[$key] = $tachesItem->getTaches();
        }

        return $taches;
    }

    /**
     * @param Taches $taches
     * @param Dossier $dossier
     * @return TachesItem[]
     */
    public function getByDossier(Taches $taches,Dossier $dossier)
    {
        $tachesItems = $this->createQueryBuilder('ti')
            ->where('ti.taches = :taches')
            ->setParameter('taches',$taches);

        if ($dossier->getRegimeImposition() && $dossier->getRegimeTva())
        {
            $tachesItems = $tachesItems
                ->andWhere('
                    (ti.regimeImposition = :regimeImposition) OR 
                    (ti.regimeTva = :regimeTva) OR 
                    (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                ')
                ->setParameter('regimeImposition',$dossier->getRegimeImposition())
                ->setParameter('regimeTva', $dossier->getRegimeTva());
        }
        elseif ($dossier->getRegimeImposition())
        {
            $tachesItems = $tachesItems
                ->andWhere('
                    (ti.regimeImposition = :regimeImposition) OR 
                    (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                ')
                ->setParameter('regimeImposition',$dossier->getRegimeImposition());
        }
        elseif ($dossier->getRegimeTva())
        {
            $tachesItems = $tachesItems
                ->andWhere('
                    (ti.regimeTva = :regimeTva) OR 
                    (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                ')
                ->setParameter('regimeTva', $dossier->getRegimeTva());
        }

        /** @var TachesItem[] $tachesItems */
        $tachesItems = $tachesItems->getQuery()->getResult();

        /** @var TachesItem[] $results */
        $results = [];
        foreach ($tachesItems as $tachesItem)
        {
            $isValidToDossier = $this->getEntityManager()->getRepository('AppBundle:TachesDate')
                ->tachesItemIsToDossier($tachesItem,$dossier);

            if ($isValidToDossier) $results[] = $tachesItem;
        }
        return $results;
    }

    /**
     * @param Dossier $dossier
     * @param Taches|null $taches
     * @return TachesItem[]
     */
    public function getForDossier(Dossier $dossier, Taches $taches = null)
    {
        $tachesItems = $this->createQueryBuilder('ti')
            ->join('ti.taches','t');

        $andWhere = false;
        if ($taches)
        {
            $tachesItems = $tachesItems
                ->where('ti.taches = :taches')
                ->setParameter('taches',$taches);
            $andWhere = true;
        }

        if ($dossier->getRegimeFiscal())
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('(t.regimeFiscal = :regimeFiscal OR t.regimeFiscal IS NULL)');
            else
                $tachesItems = $tachesItems
                    ->where('(t.regimeFiscal = :regimeFiscal OR t.regimeFiscal IS NULL)');
            $tachesItems = $tachesItems
                ->setParameter('regimeFiscal', $dossier->getRegimeFiscal());

            $andWhere = true;
        }

        if ($dossier->getRegimeImposition() && $dossier->getRegimeTva())
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('
                        (ti.regimeImposition = :regimeImposition) OR 
                        (ti.regimeTva = :regimeTva) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                    ');
            else
                $tachesItems = $tachesItems
                    ->where('
                        (ti.regimeImposition = :regimeImposition) OR 
                        (ti.regimeTva = :regimeTva) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                    ');

            $tachesItems = $tachesItems
                ->setParameter('regimeImposition',$dossier->getRegimeImposition())
                ->setParameter('regimeTva',$dossier->getRegimeTva());
        }
        elseif ($dossier->getRegimeImposition())
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('
                        (ti.regimeImposition = :regimeImposition) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                    ');
            else
                $tachesItems = $tachesItems
                    ->where('
                        (ti.regimeImposition = :regimeImposition) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                    ');

            $tachesItems = $tachesItems
                ->setParameter('regimeImposition',$dossier->getRegimeImposition());
        }
        elseif ($dossier->getRegimeTva())
        {
            if ($andWhere)
                $tachesItems = $tachesItems
                    ->andWhere('
                        (ti.regimeTva = :regimeTva) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                    ');
            else
                $tachesItems = $tachesItems
                    ->where('
                        (ti.regimeTva = :regimeTva) OR 
                        (ti.regimeImposition IS NULL AND ti.regimeTva IS NULL)
                    ');

            $tachesItems = $tachesItems
                ->setParameter('regimeTva',$dossier->getRegimeTva());
        }

        /** @var TachesItem[] $tachesItems */
        $tachesItems = $tachesItems->getQuery()->getResult();

        /** @var TachesItem[] $results */
        $results = [];

        if ($dossier->getRegimeFiscal())
        {
            foreach ($tachesItems as $tachesItem)
            {
                if (!$tachesItem->getTaches()->getRegimeFiscal())
                {
                    $regimesFiscals = $this->getEntityManager()->getRepository('AppBundle:TachesGroupRegimeFiscal')
                        ->getRegimeFiscals($tachesItem->getTaches()->getTachesGroup());

                    if (count($regimesFiscals) > 0 && !array_key_exists($dossier->getRegimeFiscal()->getId(),$regimesFiscals)) continue;
                }

                $results[] = $tachesItem;
            }
        }
        else $results = $tachesItems;

        return $results;
    }
}