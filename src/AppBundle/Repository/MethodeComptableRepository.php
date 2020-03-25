<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 14/12/2018
 * Time: 14:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class MethodeComptableRepository extends EntityRepository
{
    public function getMethodeComptableByDossier(Dossier $dossier){
        $res = $this->createQueryBuilder('mc')
            ->where('mc.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if(count($res) > 0){
            return $res[0];
        }
        return null;
    }

}