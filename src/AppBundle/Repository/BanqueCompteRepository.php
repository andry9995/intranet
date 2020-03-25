<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 07/01/2019
 * Time: 16:30
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class BanqueCompteRepository extends EntityRepository
{
    public function getBanqueComptes(Dossier $dossier, $numCompte){
        return $this->createQueryBuilder('bc')
            ->where('bc.dossier = :dossier')
            ->andWhere('bc.numcompte like :numcompte')
            ->setParameter('dossier', $dossier)
            ->setParameter('numcompte', '%'.$numCompte.'%')
            ->getQuery()
            ->getResult();
    }


    public function getBanquesComptes(Dossier $dossier,$banque = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select distinct bc.id 
                  from imputation_controle ic 
                  left join banque_compte bc on bc.id=ic.banque_compte_id 
                  inner join banque bq on bq.id = bc.banque_id 
                  inner join separation s on ic.image_id = s.image_id and s.souscategorie_id = 10
                  where bc.dossier_id=:dossier ";

        $params = ['dossier' => $dossier->getId()];
        if ($banque != null)
        {
            $query .= "AND bq.id = :banque";
            $params['banque'] = $banque->getId();
        }

        $prep = $pdo->prepare($query);

        $prep->execute($params);
        $idsT = $prep->fetchAll();
        $ids = [];
        foreach ($idsT as $item)
            $ids[] = $item->id;

        return $banquesComptes = $this->createQueryBuilder('bc')
            ->where('bc.id IN (:ids)')
            ->setParameter('ids',$ids)
            ->getQuery()
            ->getResult();
    }


    public function getBanqueCompteByNumCompte($numcompte){
        $banqueComptes = $this->createQueryBuilder('bc')
            ->where('bc.numcompte LIKE :numcompte')
            ->setParameter('numcompte', '%'.$numcompte.'%')
            ->getQuery()
            ->getResult();

        if(count($banqueComptes) > 0){
            return $banqueComptes[0];
        }

        return null;
    }


}