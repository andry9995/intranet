<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 31/08/2018
 * Time: 11:32
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ConditionDepense;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Sousnature;
use Doctrine\ORM\EntityRepository;

class TdNdfResultatPccRepository extends EntityRepository
{
    public function  getTdNdfResultatBySousnatureCondition(Dossier $dossier,Sousnature $sousnature = null, ConditionDepense $conditionDepense = null)
    {
        $td = null;

        $tds = $this->createQueryBuilder('td')
            ->innerJoin('td.pcc', 'pcc')
            ->where('td.sousNature = :sousnature')
            ->andWhere('td.conditionDepense = :conditiondepense')
            ->andWhere('pcc.dossier = :dossier')
            ->setParameter('sousnature', $sousnature)
            ->setParameter('conditiondepense', $conditionDepense)
            ->setParameter('dossier', $dossier)
            ->getQuery();

        $tds = $tds->getResult();

        if(count($tds) > 0){
            $td = $tds[0];
        }

        return $td;
    }
}