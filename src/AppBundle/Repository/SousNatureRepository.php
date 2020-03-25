<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 06/06/2019
 * Time: 10:49
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SousNatureRepository extends EntityRepository
{
    public function getSousNaturesByCategorie($categorie)
    {
        return $this->createQueryBuilder('sn')
            ->innerJoin('sn.nature', 'n')
            ->where('n.categorie = :categorie')
            ->andWhere('n.actif = 1')
            ->andWhere('sn.actif = 1')
            ->setParameter('categorie', $categorie)
            ->orderBy('sn.libelle', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function getSousNatureByLibelle($libelle){
        $sousNatures = $this->createQueryBuilder('sn')
            ->where('sn.libelle = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($sousNatures) > 0)
            return $sousNatures[0];

        return null;
    }

}