<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 09/08/2016
 * Time: 15:44
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use Doctrine\ORM\EntityRepository;

class TacheClientRepository extends EntityRepository
{
    public function listeTacheClient(Client $client, $to_array = false)
    {
        $taches = $this->getEntityManager()
            ->getRepository('AppBundle:TacheClient')
            ->createQueryBuilder('tc')
            ->select('tc')
            ->where('tc.client = :client')
            ->innerJoin('tc.tache', 't')
            ->addSelect('t AS tache_principale')
            ->innerJoin('tc.client', 'c')
            ->addSelect('c AS client')
            ->setParameter('client', $client)
            ->orderBy('t.nom')
            ->getQuery();
        if ($to_array) {
            return $taches->getArrayResult();
        } else {
            return $taches->getResult();
        }
    }
}