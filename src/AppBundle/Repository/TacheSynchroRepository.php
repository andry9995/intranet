<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 27/09/2018
 * Time: 17:13
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheEntityLegaleAction;
use AppBundle\Entity\TacheEntityLibreAction;
use AppBundle\Entity\TacheSynchro;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use RevisionBundle\Controller\ModelAgenda;

class TacheSynchroRepository extends EntityRepository
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array $clients
     * @return array
     */
    public function getByClient(\DateTime $start,\DateTime $end,$clients = [])
    {
        if (count($clients) == 0)  return [];

        return $this->createQueryBuilder('ts')
            ->leftJoin('ts.dossier','d')
            ->leftJoin('d.site','s')
            ->where('s.client IN (:clients)')
            ->andWhere('ts.date >= :start')
            ->andWhere('ts.date <= :end')
            ->setParameters([
                'clients' => $clients,
                'start' => $start->format('Y-m-d'),
                'end' =>$end->format('Y-m-d')
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param Dossier $dossier
     * @param TacheEntityLegaleAction|null $tacheEntityLegaleAction
     * @param TacheEntityLibreAction|null $tacheEntityLibreAction
     * @return array
     */
    public function getInEndStart(\DateTime $start,\DateTime $end, Dossier $dossier,TacheEntityLegaleAction $tacheEntityLegaleAction = null,TacheEntityLibreAction $tacheEntityLibreAction = null)
    {
        /** @var QueryBuilder $result */
        $result = $this->createQueryBuilder('ts')
            ->where('ts.dossier = :dossier')
            ->andWhere('ts.date >= :start')
            ->andWhere('ts.date <= :end')
            ->setParameter('dossier',$dossier)
            ->setParameter('start',$start)
            ->setParameter('end',$end);

        if (!is_null($tacheEntityLegaleAction))
            $result = $result
                ->andWhere('ts.tacheEntityLegaleAction = :tacheEntityLegaleAction')
                ->setParameter('tacheEntityLegaleAction',$tacheEntityLegaleAction);
        else
            $result = $result
                ->andWhere('ts.tacheEntityLibreAction = :tacheEntityLibreAction')
                ->setParameter('tacheEntityLibreAction',$tacheEntityLibreAction);

        /** @var TacheSynchro[] $tacheSynchros */
        $tacheSynchros = $result->getQuery()->getResult();

        $results = [];
        foreach ($tacheSynchros as $tacheSynchro)
        {
            $key = $tacheSynchro->getDate()->format('Y-m-d');
            $results[$key] = $tacheSynchro;
        }

        return $tacheSynchros;
    }

    /**
     * @param Dossier $dossier
     * @param \DateTime $date
     * @param TacheEntityLegaleAction|null $tacheEntityLegaleAction
     * @param TacheEntityLibreAction|null $tacheEntityLibreAction
     * @return mixed
     */
    public function findOneByDate(Dossier $dossier,\DateTime $date,TacheEntityLegaleAction $tacheEntityLegaleAction = null, TacheEntityLibreAction $tacheEntityLibreAction = null)
    {
        $result = $this->createQueryBuilder('ts')
            ->where('ts.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('ts.date = :date')
            ->setParameter('date',$date);

        if ($tacheEntityLegaleAction)
            $result = $result
                ->andWhere('ts.tacheEntityLegaleAction = :tacheEntityLegaleAction')
                ->setParameter('tacheEntityLegaleAction',$tacheEntityLegaleAction);
        if ($tacheEntityLibreAction)
            $result = $result
                ->andWhere('ts.tacheEntityLibreAction = :tacheEntityLibreAction')
                ->setParameter('tacheEntityLibreAction',$tacheEntityLibreAction);

        return $result->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $idGoogle
     * @return mixed
     */
    public function getByIdGoogle($idGoogle)
    {
        return $this->createQueryBuilder('ts')
            ->where('ts.idGoogle = :idGoogle')
            ->setParameter('idGoogle',$idGoogle)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function removeStatus_1()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "DELETE FROM tache_synchro WHERE status = 1";
        $prep = $pdo->prepare($query);
        $prep->execute();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array $clients
     * @return array
     */
    public function getExactByClient(\DateTime $start,\DateTime $end,$clients = [])
    {
        /** @var TacheSynchro[] $tacheSynchros */
        $tacheSynchros = $this->getByClient($start,$end,$clients);

        $results = [];
        foreach ($tacheSynchros as $tacheSynchro)
        {
            $tacheSynchroMoov = $this->getEntityManager()->getRepository('AppBundle:TacheSynchroMoov')
                ->getLastMoov($tacheSynchro);

            $results[] = (object)
            [
                'ts' => $tacheSynchro,
                'tsm' => $tacheSynchroMoov
            ];
        }
        return $results;
    }
}