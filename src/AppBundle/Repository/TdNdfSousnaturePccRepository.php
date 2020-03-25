<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 04/09/2018
 * Time: 10:30
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\Sousnature;
use Doctrine\ORM\EntityRepository;

class TdNdfSousnaturePccRepository extends EntityRepository
{
    public function getTdNdfSousnaturePccByCriteres(Dossier $dossier = null, Sousnature $sousnature = null, $nbparticipant, $distance){

        if($dossier === null || $sousnature === null){
            return null;
        }

        $qb = $this->createQueryBuilder('td')
            ->leftJoin('td.pccResultat', 'pccResultat')
            ->leftJoin('td.pccTva', 'pccTva')
            ->where('pccResultat.dossier = :dossier OR pccTva.dossier = :dossier')
            ->andWhere('td.sousnature = :sousnature')
            ->setParameter('sousnature', $sousnature)
            ->setParameter('dossier', $dossier);

        $res = $qb->getQuery()
            ->getResult();

        if(count($res) > 1){
            $qb->andWhere('td.distance = :distance')
                ->setParameter('distance', $distance);

            $res = $qb->getQuery()->getResult();

            if(count($res) > 1) {
                $qb->andWhere('td.nbParticipant = :nbParticipant')
                    ->setParameter('nbParticipant', $nbparticipant);

                $res = $qb->getQuery()->getResult();
            }

            else if(count($res) < 1){
                $qb->andWhere('td.nbParticipant = :nbParticipant')
                    ->setParameter('nbParticipant', $nbparticipant);

                $res = $qb->getQuery()->getResult();

                if(count($res) > 1){
                    $qb->where('td.distance = :distance')
                        ->setParameter('distance', $distance);

                    $res = $qb->getQuery()->getResult();
                }
            }
        }

        $td = null;
        if(count($res) > 0){
            $td = $res[0];
        }
        return $td;
    }
}