<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 18/07/2019
 * Time: 16:19
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class CodeAnalytiqueRepository extends EntityRepository
{
    /**
     * @param Dossier $dossier
     * @return array
     */
    public function getCodeAnalytiques(Dossier $dossier)
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.dossier = :dossier')
            ->andWhere('ca.supprimer = 0')
            ->setParameter('dossier', $dossier)
            ->orderBy('ca.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}