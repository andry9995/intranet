<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 02/07/2019
 * Time: 11:11
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Categorie;
use Doctrine\ORM\EntityRepository;

class OrganismeRepository extends EntityRepository
{
    public function getOrganismeByCategorie(Categorie $categorie){
        return $this->createQueryBuilder('o')
            ->where('o.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('o.libelle')
            ->getQuery()
            ->getResult();
    }
}