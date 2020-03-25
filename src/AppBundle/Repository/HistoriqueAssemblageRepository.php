<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 08/03/2019
 * Time: 09:47
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;

class HistoriqueAssemblageRepository extends EntiteRepository
{
    public function getListAssembleByDossierExerice(Dossier $dossier, $exercice){
        return $this->createQueryBuilder('h')
            ->innerJoin('h.imageFinale','if')
            ->innerJoin('if.lot','lot')
            ->innerJoin('lot.dossier','dossier')
            ->where('dossier = :dossier')
            ->andWhere('if.exercice = :exercice')
            ->andWhere('h.desassemblageOperateur IS NULL')
            ->setParameter('dossier', $dossier)
            ->setParameter('exercice', $exercice)
            ->select('h')
            ->getQuery()
            ->getResult();
    }

    public function getListAssembleByIds($ids){
        return $this->createQueryBuilder('h')
            ->where('h.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}