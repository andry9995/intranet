<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 18/07/2018
 * Time: 10:36
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class TvaTauxDossierRepository extends EntityRepository
{
    /**
     * @param Dossier $dossier
     * @return array
     */
    public function getTvaTauxDossier(Dossier $dossier){
        return $this->createQueryBuilder('t')
            ->where('t.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->getQuery()
            ->getResult();
    }

}