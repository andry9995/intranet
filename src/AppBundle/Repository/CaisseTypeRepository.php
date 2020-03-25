<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 20/07/2018
 * Time: 09:46
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class CaisseTypeRepository extends EntityRepository
{
    public function getCaisseTypeByDossier(Dossier $dossier)
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->orderBy('n.libelle');

        return $qb->getQuery()
            ->getResult();
    }
}