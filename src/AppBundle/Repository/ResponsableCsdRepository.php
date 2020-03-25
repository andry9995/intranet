<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 14/12/2018
 * Time: 14:34
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class ResponsableCsdRepository extends EntityRepository
{
    /**
     * @param Dossier $dossier
     * @return null
     */
    public function getMandataire(Dossier $dossier){
        $res = $this->createQueryBuilder('r')
            ->where('r.typeResponsable = 0')
            ->andWhere('r.dossier = :dossier')
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