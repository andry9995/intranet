<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 06/09/2018
 * Time: 16:49
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Pcg;
use AppBundle\Entity\Sousnature;
use AppBundle\Entity\Soussouscategorie;
use Doctrine\ORM\EntityRepository;

class SoussouscategorieRepository extends EntityRepository
{
    public function getSoussouscategorieByPcg(Pcg $pcg = null){
        if($pcg === null)
            return null;

        $results = $this->createQueryBuilder('ssc')
            ->where('ssc.libelleNew like :pcg')
            ->setParameter('pcg', '%'.$pcg->getCompte().'%')
            ->getQuery()
            ->getResult();

        if(count($results) > 0){
            /** @var Soussouscategorie $result */
            foreach ($results as $result){
                if($result->getActif() === 1){
                    return $result;
                }
            }

            return $results[0];
        }
        return null;
    }


    public function getSoussouscategorieBySouscategories($souscategories){
        return $this->createQueryBuilder('ssc')
            ->where('ssc.souscategorie IN (:souscategories)')
            ->andWhere('ssc.actif = 1')
            ->setParameter('souscategories', array_values($souscategories))
            ->orderBy('ssc.libelleNew')
            ->getQuery()
            ->getResult();
    }

    public function getSoussouscategorieBySouscategorie($souscategorie){
        return $this->createQueryBuilder('ssc')
            ->where('ssc.souscategorie = :souscategorie')
            ->andWhere('ssc.actif = 1')
            ->setParameter('souscategorie', $souscategorie)
            ->orderBy('ssc.libelleNew')
            ->getQuery()
            ->getResult();
    }

    public function getSoussouscategorieBySousnature(Sousnature $sousnature = null){
        if($sousnature){
            $soussouscategories =  $this->createQueryBuilder('ssc')
                ->where('ssc.sousnature = :sousnature')
                ->setParameter('sousnature', $sousnature)
                ->getQuery()
                ->getResult();

            if(count($soussouscategories) > 0){
                return $soussouscategories[0];
            }
        }
        return null;
    }

    public function getSoussouscategorieByLibelle($libelle){
        $soussouscategories = $this->createQueryBuilder('ssc')
            ->where('ssc.libelleNew = :libelle')
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($soussouscategories) > 0)
            return $soussouscategories[0];

        return null;
    }

}