<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 25/01/2019
 * Time: 08:32
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Categorie;
use Doctrine\ORM\EntityRepository;

class SouscategorieRepository extends EntityRepository
{
    public function getObSouscategories(){

        $scids = [5,6,7,8,153, 937,939,941];

        return $this->createQueryBuilder('sc')
            ->where('sc.id in (:scids)')
            ->setParameter('scids', $scids)
            ->getQuery()
            ->getResult();

    }

    public function getSouscategoriesByCategorie(Categorie $cat){
        if($cat === null)
            return [];
        return $this->createQueryBuilder('sc')
            ->where('sc.categorie = :categorie')
            ->andWhere('sc.actif = 1')
            ->setParameter('categorie', $cat)
            ->orderBy('sc.libelleNew')
            ->getQuery()
            ->getResult();
    }

    public function getSouscategorieByLibelle($libelle){
        $souscategories = $this->createQueryBuilder('sc')
            ->where('sc.libelleNew = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($souscategories) > 0)
            return $souscategories[0];

        return null;
    }
}