<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 03/01/2019
 * Time: 17:15
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Tache;
use AppBundle\Entity\TachesLibre;
use Doctrine\ORM\EntityRepository;

class TachesLibreRepository extends EntityRepository
{
    /**
     * @param Tache $tache
     * @param Client|null $client
     * @param Dossier|null $dossier
     * @param int $responsable
     * @param TachesLibre|null $tachesLibre
     * @param int $activer
     * @return TachesLibre
     */
    public function getTachesLibre(Tache $tache,Client $client = null,Dossier $dossier = null, $responsable = -1, TachesLibre $tachesLibre = null, $activer = 1)
    {
        $em = $this->getEntityManager();

        /** @var Tache TachesLibre $tacheLibre */
        $tacheLibre = null;

        if ($tachesLibre) $tacheLibre = $tachesLibre;
        else
        {
            $tacheLibre = $this->createQueryBuilder('tl')
                ->where('tl.tache = :tache')
                ->setParameter('tache',$tache);

            if ($dossier)
                $tacheLibre = $tacheLibre
                    ->andWhere('tl.dossier = :dossier')
                    ->setParameter('dossier',$dossier);
            else
                $tacheLibre = $tacheLibre
                    ->andWhere('tl.client = :client')
                    ->setParameter('client',$client);

            /** @var Tache TachesLibre $tacheLibre */
            $tacheLibre = $tacheLibre->setMaxResults(1)->getQuery()->getOneOrNullResult();
        }

        if ($tacheLibre)
        {
            if ($tacheLibre->getTachesLibre())
                $tachesLibreDates = $this->getEntityManager()->getRepository('AppBundle:TachesLibreDate')
                    ->tachesLibreDates($tacheLibre->getTachesLibre());
            else
                $tachesLibreDates = $this->getEntityManager()->getRepository('AppBundle:TachesLibreDate')
                    ->tachesLibreDates($tacheLibre);

            if (count($tachesLibreDates) == 0)
            {
                if ($tacheLibre->getTachesLibre())
                    $em->remove($tacheLibre->getTachesLibre());
                else
                    $em->remove($tacheLibre);

                $em->flush();
                $tacheLibre = null;
            }
        }

        if ($responsable != -1)
        {
            $add = false;
            if (!$tacheLibre)
            {
                $add = true;
                $tacheLibre = new TachesLibre();
                $tacheLibre
                    ->setDossier($dossier)
                    ->setTache($tache)
                    ->setClient($client);
                $em->persist($tacheLibre);
                $em->flush();
            }

            $tacheLibre
                ->setTachesLibre($tachesLibre)
                ->setResponsable($responsable)
                ->setStatus($activer);

            if ($add) $em->persist($tacheLibre);
            $em->flush();
        }

        if (!$tacheLibre && $dossier)
        {
            $tacheParent = $this->getTachesLibreParent($tache,$dossier);
            if ($tacheParent)
            {
                $tacheLibre = new TachesLibre();
                $tacheLibre
                    ->setTache($tache)
                    ->setTachesLibre($tacheParent)
                    ->setDossier($dossier);

                $em = $this->getEntityManager();
                $em->persist($tacheLibre);
                $em->flush();
            }
        }

        return $tacheLibre;
    }

    /**
     * @param Tache $tache
     * @param Dossier $dossier
     * @return TachesLibre
     */
    public function getTachesLibreParent(Tache $tache, Dossier $dossier)
    {
        $result = $this->createQueryBuilder('tl')
            ->where('tl.tache = :tache')
            ->andWhere('tl.client = :client')
            ->setParameters([
                'tache' => $tache,
                'client' => $dossier->getSite()->getClient()
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result)
        {
            $em = $this->getEntityManager();
            $tacheLibreDates = $em->getRepository('AppBundle:TachesLibreDate')
                ->tachesLibreDates($result);

            if (count($tacheLibreDates) == 0)
            {
                $em->remove($result);
                $em->flush();
                $result = null;
            }
        }

        return $result;
    }

    /**
     * @param TachesLibre|null $tachesLibre
     * @return bool
     */
    public function isActive(TachesLibre $tachesLibre = null)
    {
        $status = false;
        if ($tachesLibre)
        {
            if ($tachesLibre->getTachesLibre() && $tachesLibre->getStatus() == 1) $status = true;
            else
            {
                $tachesLibreDates = $this->getEntityManager()->getRepository('AppBundle:TachesLibreDate')
                    ->tachesLibreDates($tachesLibre);
                if (count($tachesLibreDates) > 0) $status = true;
            }
        }

        return $status;
    }

    /**
     * @param Client[] $clients
     * @return TachesLibre[]
     */
    public function getTachesLibreForClients($clients)
    {
        return $this->createQueryBuilder('tl')
            ->join('tl.dossier','d')
            ->join('d.site','s')
            ->where('s.client IN (:clients)')
            ->setParameter('clients',$clients)
            ->getQuery()
            ->getResult();
    }
}