<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 26/07/2018
 * Time: 13:08
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class TdCaisseBilanPccRepository extends EntityRepository
{

    public function getTdCaisseBilanPccByDossier(Dossier $dossier, $typecaisse){
        try {
            return $this->createQueryBuilder('td')
                ->innerJoin('td.pcc', 'pcc')
                ->where('td.dossier = :dossier')
                ->andWhere('td.typeCaisse = :typecaisse')
                ->setParameter('dossier', $dossier)
                ->setParameter('typecaisse', $typecaisse)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return  null;
        }

    }

}