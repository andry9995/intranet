<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 09/08/2016
 * Time: 15:44
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheDossier;
use Doctrine\ORM\EntityRepository;

class TacheDossierRepository extends EntityRepository
{
    public function listeTacheDossier(Dossier $dossier, $nomtache = null)
    {
        if ($nomtache) {
            $taches = $this->getEntityManager()
                ->getRepository('AppBundle:TacheDossier')
                ->createQueryBuilder('td')
                ->select('td')
                ->where('td.dossier = :dossier')
                ->innerJoin('td.tache', 't')
                ->addSelect('t AS tache_principale')
                ->andWhere('t.nom = :nomtache')
                ->innerJoin('td.dossier', 'd')
                ->addSelect('d AS dossier')
                ->setParameters(array(
                    'dossier', $dossier,
                    'nomtache' => $nomtache,
                ))
                ->orderBy('t.nom')
                ->getQuery()
                ->getResult();
        } else {
            $taches = $this->getEntityManager()
                ->getRepository('AppBundle:TacheDossier')
                ->createQueryBuilder('td')
                ->select('td')
                ->where('td.dossier = :dossier')
                ->innerJoin('td.tache', 't')
                ->addSelect('t AS tache_principale')
                ->innerJoin('td.dossier', 'd')
                ->addSelect('d AS dossier')
                ->setParameter('dossier', $dossier)
                ->orderBy('t.nom')
                ->getQuery()
                ->getResult();
        }
        return $taches;

    }

    public function listeTacheAllDossier(Client $client, $nomtache = null)
    {
        $dossiers = $this->getEntityManager()
            ->getRepository('AppBundle:Dossier')
            ->getDossierByClient($client);
        $dossier_ids = [0];

        /** @var Dossier $dossier */
        foreach ($dossiers as $dossier) {
            $dossier_ids[] = $dossier->getId();
        }

        $qb = $this->getEntityManager()
            ->getRepository('AppBundle:TacheDossier')
            ->createQueryBuilder('td');
        if ($nomtache) {
            $taches = $qb->select('td')
                ->innerJoin('td.tache', 't')
                ->addSelect('t AS tache_principale')
                ->innerJoin('td.dossier', 'd')
                ->addSelect('d AS dossier')
                ->where($qb->expr()->in('d.id', $dossier_ids))
                ->andWhere('t.nom = :nomtache')
                ->setParameters(array(
                    'nomtache' => $nomtache,
                ))
                ->orderBy('t.nom')
                ->getQuery()
                ->getResult();
        } else {
            $taches = $qb->select('td')
                ->innerJoin('td.tache', 't')
                ->addSelect('t AS tache_principale')
                ->innerJoin('td.dossier', 'd')
                ->addSelect('d AS dossier')
                ->where($qb->expr()->in('d.id', $dossier_ids))
                ->orderBy('t.nom')
                ->getQuery()
                ->getResult();
        }
        return $taches;
    }

    /**
     * @param Dossier $dossier
     * @return array
     * @throws \Exception
     */
    public function getTachePlusProche(Dossier $dossier)
    {
        $now = new \DateTime();
        $now->setTime(0, 0);
        $year = $now->format('Y');
        $min = new \DateTime();
        $min->sub(new \DateInterval('P1D'));
        $min->setTime(0, 0);
        $taches = $this->getEntityManager()
            ->getRepository('AppBundle:TacheDossier')
            ->listeTacheDossier($dossier);
        $the_tache = '';
        $delai = null;

        /** @var TacheDossier $tache */
        foreach ($taches as $tache) {
            if ($tache->getDateList()) {
                foreach ($tache->getDateList() as $tache_date) {
                    if ($tache_date && strlen($tache_date) === 10 ) {
                        $tmp = new \DateTime($tache_date);
                        $date = new \DateTime($year . '-' . $tmp->format('m-d'));
                        if ($date >= $min && (!$delai || $date < $delai)) {
                            $the_tache = $tache->getTache()->getNom();
                            $delai = $date;
                        }
                    }
                }
            }
        }

        return [
            'delai' => $delai,
            'tache' => $the_tache
        ];
    }
}