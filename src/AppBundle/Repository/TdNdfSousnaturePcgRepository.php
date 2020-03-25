<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 03/09/2018
 * Time: 14:47
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Sousnature;
use Doctrine\ORM\EntityRepository;

class TdNdfSousnaturePcgRepository extends EntityRepository
{
    public function getTdNdfSousnaturePcgByCriteres(Sousnature $sousnature = null, $nbparticipant, $distance){

        if($sousnature === null){
            return null;
        }

        if($distance === '' || intval($distance) === 0){
            $distance = null;
        }
        elseif (intval($distance) > 50){
            $distance = 1;
        }
        else{
            $distance = 0;
        }

        if($nbparticipant === ''){
            $nbparticipant = null;
        }
        elseif (intval($nbparticipant) > 1){
            $nbparticipant = 2;
        }
        else if (intval($nbparticipant) === 1){
            $nbparticipant = 1;
        }
        else{
            $nbparticipant = null;
        }


        $qb = $this->createQueryBuilder('td')
            ->where('td.sousnature = :sousnature')
            ->setParameter('sousnature', $sousnature);

        $res = $qb->getQuery()
            ->getResult();

        if(count($res) > 1){

            if($distance === null) {
                $qb->andWhere('td.distance is NULL');
            }
            else {
                $qb->andWhere('td.distance = :distance');
                $qb->setParameter('distance' , $distance);
            }

            if($nbparticipant === null){
                $qb->andWhere('td.nbParticipant is NULL');
            }
            else{
                $qb->andWhere('td.nbParticipant = :nbParticipant');
                $qb->setParameter('nbParticipant', $nbparticipant);
            }

            $res = $qb->getQuery()
                ->getResult();
        }

        $td = null;
        if(count($res) > 0){
            $td = $res[0];
        }

        return $td;
    }

    public function getTdNdfSousnature(Sousnature $sousnature = null, $nbparticipant, $distance){

        if($sousnature === null){
            return null;
        }

        $qb = $this->createQueryBuilder('td')
            ->where('td.sousnature = :sousnature')
            ->setParameter('sousnature', $sousnature);

        $res = $qb->getQuery()
            ->getResult();

        if(count($res) > 1){
            if($distance === null) {
                $qb->andWhere('td.distance is NULL');
            }
            else {
                $qb->andWhere('td.distance = :distance');
                $qb->setParameter('distance' , $distance);
            }

            if($nbparticipant === null){
                $qb->andWhere('td.nbParticipant is NULL');
            }
            else{
                $qb->andWhere('td.nbParticipant = :nbParticipant');
                $qb->setParameter('nbParticipant', $nbparticipant);
            }

            $res = $qb->getQuery()
                ->getResult();
        }

        return $res;
    }

}