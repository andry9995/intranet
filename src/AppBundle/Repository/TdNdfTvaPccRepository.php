<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 31/08/2018
 * Time: 13:40
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\Sousnature;
use Doctrine\ORM\EntityRepository;

class TdNdfTvaPccRepository extends EntityRepository
{
    public function getTdNdfTvaBySousnature(Dossier $dossier, Sousnature $sousnature){
        $td = null;

        $tds = $this->createQueryBuilder('td')
            ->innerJoin('td.pcc', 'pcc')
            ->where('pcc.dossier = :dossier')
            ->andWhere('td.sousnature = :sousnature')
            ->setParameter('dossier', $dossier)
            ->setParameter('sousnature', $sousnature)
            ->getQuery()
            ->getResult();

        if(count($tds) > 0){
            $td = $tds[0];
        }

        return $td;
    }

}