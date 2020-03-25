<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 09/12/2019
 * Time: 10:40
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class TypePieceRepository extends EntityRepository
{

    public function getTypePieceByLibelle($libelle){
        $typePieces = $this->createQueryBuilder('tp')
            ->where('tp.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($typePieces) > 0)
            return $typePieces[0];

        return null;
    }
}