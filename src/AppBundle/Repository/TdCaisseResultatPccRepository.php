<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 26/07/2018
 * Time: 11:09
 */

namespace AppBundle\Repository;


use AppBundle\Entity\CaisseNature;
use AppBundle\Entity\CaisseType;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Pcc;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class TdCaisseResultatPccRepository extends EntityRepository
{

    public function getTdCaisseResultatPccByDossierNature(Dossier $dossier, CaisseNature $nature, CaisseType $type = null)
    {
        $qb = $this->createQueryBuilder('td')
            ->innerJoin('td.pcc', 'pcc')
            ->where('td.caisseNature = :nature')
            ->andWhere('pcc.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->setParameter('nature', $nature);

        if($type !== null){
            $qb->andWhere('td.caisseType = :caissetype')
                ->setParameter('caissetype', $type);
        }

        $res = $qb
            ->getQuery()
            ->getResult();

        if(count($res) > 0){
            return $res[0];
        }

        return null;
    }

    public function getTdCaisseResultatPccByPcc(Pcc $pcc)
    {
        $qb = $this->createQueryBuilder('td')
            ->where('td.pcc = :pcc')
            ->setParameter('pcc', $pcc)
            ->getQuery();

        $res = $qb->getResult();

        if (count($res) > 0) {
            return $res[0];
        }
        return null;

    }


}