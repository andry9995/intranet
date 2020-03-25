<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 08/08/2019
 * Time: 09:49
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Sousnature;
use AppBundle\Entity\SoussouscategorieCompte;
use Doctrine\ORM\EntityRepository;

class SoussouscategorieCompteRepository extends EntityRepository
{
    public function getPcgsBySousnature(Sousnature $sousnature, $typeCompte){
        /** @var SoussouscategorieCompte[] $soussouscategorieComptes */
        $soussouscategorieComptes =  $this->createQueryBuilder('p')
            ->innerJoin('p.soussouscategorie', 'ssc')
            ->where('ssc.sousnature = :sousnature')
            ->andWhere('p.typeCompte = :typecompte')
            ->setParameter('typecompte', $typeCompte)
            ->setParameter('sousnature', $sousnature)
            ->getQuery()
            ->getResult();

        $pcgs = [];
        foreach ($soussouscategorieComptes as $soussouscategorieCompte){
            $pcgs[] = $soussouscategorieCompte->getPcg();
        }
        return $pcgs;
    }
}