<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 01/08/2019
 * Time: 16:37
 */

namespace AppBundle\Repository;


use AppBundle\Entity\NdfFraisKilometrique;
use AppBundle\Entity\Vehicule;
use Doctrine\ORM\EntityRepository;

class NdfFraisKilometriqueRepository extends EntityRepository
{
    public function calculTtcByVehiculeTrajet(Vehicule $vehicule, $trajet, $annee){

        $ndfTypeVehicule = null;

        $res = 0;

        if($vehicule->getNdfTypeVehicule() !== null) {
            $ndfTypeVehicule = $vehicule->getNdfTypeVehicule()->getId();
            $fk = null;

            $qb = $this->createQueryBuilder('fk')
                ->where('fk.ndfTypeVehicule = :ndfTypeVehicule')
                ->andWhere('fk.annee = :annee')
                ->setParameter('ndfTypeVehicule', $ndfTypeVehicule)
                ->setParameter('annee', $annee);

            // 3:velo 9:velomoteur
            if($ndfTypeVehicule !== 3 && $ndfTypeVehicule !== 9){
                $qb->andWhere('fk.puissanceMax >= :puissance')
                    ->andWhere('fk.puissanceMin <= :puissance')
                    ->setParameter('puissance', $vehicule->getNbCv());
            }
            /** @var NdfFraisKilometrique[] $fks */
            $fks = $qb->getQuery()->getResult();

            if(count($fks) > 0){
                $fk = $fks[0];

                $fois1 = ($fk->getFois1() === null) ? 0 : $fk->getFois1();
                $fois2 = ($fk->getFois2() === null) ? 0 : $fk->getFois2();
                $fois3 = ($fk->getFois3() === null) ? 0 : $fk->getFois3();

                $plus1 = ($fk->getPlus1() === null) ? 0 : $fk->getPlus1();
                $plus2 = ($fk->getPlus2() === null) ? 0 : $fk->getPlus2();
                $plus3 = ($fk->getPlus3() === null) ? 0 : $fk->getPlus3();

                $fois = 0;
                $plus = 0;
                if($trajet > 0 && $trajet <= 5000){
                    $fois = $fois1;
                    $plus = $plus1;
                }
                elseif($trajet > 5000 && $trajet <= 20000){
                    $fois = $fois2;
                    $plus = $plus2;
                }
                elseif($trajet > 20000 && $trajet ){
                    $fois = $fois3;
                    $plus = $plus3;
                }

                $res = round((($trajet * $fois) + $plus), 2);
            }
        }
        return $res;
    }


    function getFraisKmByTypeVehiculeAnnee($annee, $typeVehicule){
        return $this
            ->getEntityManager()
            ->getRepository('AppBundle:NdfFraisKilometrique')
            ->createQueryBuilder('fk')
            ->where('fk.annee = :annee')
            ->andWhere('fk.ndfTypeVehicule = :typeVehicule')
            ->setParameter('annee', $annee)
            ->setParameter('typeVehicule', $typeVehicule)
            ->getQuery()
            ->getResult();
    }

}