<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 08/02/2019
 * Time: 13:34
 */

namespace AppBundle\Repository;


use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class CarteBleuBanqueCompteRepository extends EntityRepository
{
    public function getCarteBleuBanquesCompteByDossier(Dossier $dossier){
        return $this->createQueryBuilder('c')
            ->innerJoin('c.banqueCompte', 'bc')
            ->where('bc.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->select('c')
            ->getQuery()
            ->getResult();
    }

    public function getCbByBanqueCompte(BanqueCompte $banqueCompte, $numcb)
    {
        return $this->createQueryBuilder('c')
            ->where('c.banqueCompte = :banquecompte')
            ->andWhere('c.numCb = :numcb')
            ->setParameter('banquecompte', $banqueCompte)
            ->setParameter('numcb', $numcb)
            ->getQuery()
            ->getResult();
    }
}