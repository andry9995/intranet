<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 14/12/2018
 * Time: 14:49
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\HistoriqueUpload;
use Doctrine\ORM\EntityRepository;

class HistoriqueUploadRepository extends EntityRepository
{
    public function getLastUploadDossier(Dossier $dossier){
        /** @var HistoriqueUpload[] $res */
        $res = $this->createQueryBuilder('h')
            ->where('h.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->setMaxResults(1)
            ->orderBy('h.id', 'DESC')
            ->getQuery()
            ->getResult();

        if(count($res) > 0){
            return $res[0];
        }

        return null;



    }

}