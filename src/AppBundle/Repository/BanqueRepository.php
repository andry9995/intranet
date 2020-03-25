<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 11/03/2019
 * Time: 13:39
 */

namespace AppBundle\Repository;


class BanqueRepository extends EntiteRepository
{
    public function getAllBanques(){
        return $this->createQueryBuilder('banque1')
            ->innerJoin('AppBundle:Banque2', 'banque2', 'WITH', 'banque2.codebanque = banque1.codebanque')
            ->orderBy('banque1.codebanque', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getBanqueByCode($codeBanque){
        $banques = $this->createQueryBuilder('banque')
            ->innerJoin('AppBundle:Banque2', 'banque2', 'WITH', 'banque2.codebanque = banque.codebanque')
            ->where('banque.codebanque = :codebanque')
            ->setParameter('codebanque', $codeBanque)
            ->getQuery()
            ->getResult();

        if(count($banques) > 0){
            return $banques[0];
        }

        return null;
    }

}