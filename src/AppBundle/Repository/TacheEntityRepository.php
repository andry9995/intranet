<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/09/2018
 * Time: 11:30
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientTheme;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Tache;
use AppBundle\Entity\TacheEntity;
use AppBundle\Entity\TacheLegale;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TacheEntityRepository extends EntityRepository
{
    /**
     * @param TacheLegale|null $tacheLegale
     * @param Tache|null $tache
     * @param Client|null $client
     * @param Dossier|null $dossier
     * @return mixed
     */
    public function getByClientDossier(TacheLegale $tacheLegale = null, Tache $tache = null, Client $client = null, Dossier $dossier = null)
    {
        /** @var QueryBuilder $result */
        $result = $this->createQueryBuilder('te');

        if (is_null($tache))
        {
            $result = $result
                ->where('te.tacheLegale = :tacheLegale')
                ->setParameter('tacheLegale',$tacheLegale);
        }
        else
        {
            $result = $result
                ->where('te.tache = :tache')
                ->setParameter('tache',$tache);
        }

        if (is_null($dossier))
        {
            $result = $result
                ->andWhere('te.client = :client')
                ->setParameter('client',$client);
        }
        else
        {
            $result = $result
                ->andWhere('te.dossier = :dossier')
                ->setParameter('dossier',$dossier);
        }

        return $result
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $clients
     * @param Dossier|null $dossier
     * @return TacheEntity[]
     */
    public function getTacheEntityForClients($clients = [],Dossier $dossier = null)
    {
        $result = $this->createQueryBuilder('te')
            ->leftJoin('te.dossier','d')
            ->leftJoin('d.site','s')
            ->leftJoin('s.client','c')
            ->where('te.dossier IS NOT NULL')
            ->andWhere('c IN (:clients)')
            ->andWhere('te.desactiver = 0')
            ->addSelect('d')
            ->setParameter('clients',$clients);

        if ($dossier)
            $result = $result
                ->andWhere('te.dossier = :dossier')
                ->setParameter('dossier',$dossier);

        return $result
            ->orderBy('c.nom')
            ->addOrderBy('d.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $clients
     * @param Dossier|null $dossier
     * @return TacheEntity[]
     */
    public function getAndGroupByClient($clients = [],Dossier $dossier = null)
    {
        $tacheEntitys = $this->getTacheEntityForClients($clients,$dossier);
        $results = [];
        foreach ($tacheEntitys as $tacheEntity)
        {
            $key = $tacheEntity->getDossier()->getSite()->getClient()->getId();
            if (!array_key_exists($key,$results)) $results[$key] = [];

            $results[$key][] = $tacheEntity;
        }

        return $results;
    }

    /**
     * @param Client|null $client
     * @return Client[]
     */
    public function getClientsHavingTache(Client $client = null)
    {
        $clients = [];
        $tacheEntitys = $this->createQueryBuilder('te')
            ->where('te.dossier IS NOT NULL');

        if ($client)
            $tacheEntitys = $tacheEntitys
                ->leftJoin('te.dossier','d')
                ->leftJoin('d.site','s')
                ->andWhere('s.client = :client')
                ->setParameter('client',$client);

        /** @var TacheEntity[] $tacheEntitys */
        $tacheEntitys = $tacheEntitys
            ->getQuery()->getResult();

        foreach ($tacheEntitys as $tacheEntity)
        {
            $cl = $tacheEntity->getDossier()->getSite()->getClient();
            $key = $cl->getId();
            if (!array_key_exists($key,$clients))
                $clients[$key] = $cl;
        }

        return $clients;
    }
}