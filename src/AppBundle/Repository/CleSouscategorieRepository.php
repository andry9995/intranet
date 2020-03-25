<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 28/01/2019
 * Time: 16:30
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Banque;
use AppBundle\Entity\CleSouscategorie;
use AppBundle\Entity\Souscategorie;
use Doctrine\ORM\EntityRepository;

class CleSouscategorieRepository extends EntityRepository
{
    /**
     * @param Banque $banque
     * @param Souscategorie $souscategorie
     * @return array
     */
    public function getListCleByBanqueSoucategorie(Banque $banque, Souscategorie $souscategorie){
        return $this->createQueryBuilder('c')
            ->where('c.banque = :banque')
            ->andWhere('c.souscategorie = :souscategorie')
            ->setParameter('banque', $banque)
            ->setParameter('souscategorie', $souscategorie)
            ->orderBy('c.cle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Banque $banque
     * @param Souscategorie $souscategorie
     * @return CleSouscategorie[]|array
     */
    public function getAllCleBySoucategorie(Banque $banque, Souscategorie $souscategorie){
        /** @var CleSouscategorie[] $clesBanques */
        $clesBanques = $this->createQueryBuilder('c')
            ->where('c.banque = :banque')
            ->andWhere('c.souscategorie = :souscategorie')
            ->setParameter('banque', $banque)
            ->setParameter('souscategorie', $souscategorie)
            ->getQuery()
            ->getResult();

        /** @var CleSouscategorie[] $clesAutres */
        $clesAutres = $this->createQueryBuilder('c')
            ->innerJoin('c.banque', 'b')
            ->where('c.banque != :banque')
            ->andWhere('c.souscategorie = :souscategorie')
            ->setParameter('banque', $banque)
            ->setParameter('souscategorie', $souscategorie)
            ->distinct('c.cle')
            ->orderBy('b.nom', 'ASC')
            ->select('c')
            ->getQuery()
            ->getResult();

        $cles = $clesBanques;

        foreach ($clesAutres as $clesAutre) {
            $cles [] = $clesAutre;
        }

        return $cles;
    }

    /**
     * @param $ids
     * @return array
     */
    public function getListCleById($ids){

        $qb =  $this->createQueryBuilder('c')
            ->where('c.id in (:ids)')
            ->setParameter('ids', explode(',', $ids))
            ->getQuery();


        return $qb->getResult();
    }


}