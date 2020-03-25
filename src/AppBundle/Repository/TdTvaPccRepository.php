<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 18/07/2018
 * Time: 10:28
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\TvaTaux;
use AppBundle\Entity\TvaTauxDossier;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;


class TdTvaPccRepository extends EntityRepository
{

    /**
     * @param Pcc $pcc
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTdByPcc(Pcc $pcc){
        return $this->createQueryBuilder('t')
            ->where('t.pcc = :pcc')
            ->setParameter('pcc', $pcc)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isInTd(Dossier $dossier, TvaTaux $taux){

        if($taux !== null){
            $tds = $this->createQueryBuilder('t')
                ->innerJoin('t.pcc', 'p')
                ->where('p.dossier = :dossier')
                ->andWhere('t.tvaTaux = :taux')
                ->setParameter('dossier', $dossier)
                ->setParameter('taux', $taux)
                ->getQuery()
                ->getResult();

            if(count($tds) > 0){
                return true;
            }
        }

        return false;

    }


    public function getTdByDossierTauxType(Dossier $dossier, TvaTaux $taux, $typecaisse){
        try {
            return $this->createQueryBuilder('t')
                ->innerJoin('t.pcc', 'pcc')
                ->where('t.typeCaisse = :typecaisse')
                ->andWhere('t.tvaTaux = :taux')
                ->andWhere('pcc.dossier = :dossier')
                ->setParameter('typecaisse', $typecaisse)
                ->setParameter('taux', $taux)
                ->setParameter('dossier', $dossier)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

}