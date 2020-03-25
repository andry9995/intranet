<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 10/12/2019
 * Time: 10:44
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;

class JournalDossierRepository extends EntityRepository
{
    public function getJournalDossierByCode(Dossier $dossier, $code){
        $journals = $this->createQueryBuilder('jd')
            ->where('jd.codeStr = :code')
            ->andWhere('jd.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();

        if(count($journals) > 0)
            return $journals[0];

        return null;
    }
}