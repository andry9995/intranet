<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 31/08/2018
 * Time: 14:34
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class TdNdfBilanPccRepository extends EntityRepository
{
    public function getTdNdfBilanPccByDossierTypeCompte(Dossier $dossier, $typeCompte){
        $tds = $this->createQueryBuilder('td')
            ->where('td.dossier = :dossier')
            ->andWhere('td.typeCompte = :typecompte')
            ->setParameter('dossier', $dossier)
            ->setParameter('typecompte', $typeCompte)
            ->getQuery()
            ->getResult();

        $td = null;
        if(count($tds) > 0){
            $td = $tds[0];
        }

        return $td;
    }

}