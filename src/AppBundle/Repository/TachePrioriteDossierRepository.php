<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 02/10/2018
 * Time: 14:45
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\TachePrioriteDossier;
use Doctrine\ORM\EntityRepository;

class TachePrioriteDossierRepository extends EntityRepository
{
    /**
     * @param Client $client
     * @param array $news
     */
    public function updateTachePriority(Client $client, $news = [])
    {
        $tachePrioriteDossiers = $this->createQueryBuilder('tpd')
            ->leftJoin('tpd.dossier','d')
            ->leftJoin('d.site','s')
            ->where('s.client = :client')
            ->setParameter('client',$client)
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();
        foreach ($tachePrioriteDossiers as $tachePrioriteDossier) $em->remove($tachePrioriteDossier);
        $em->flush();

        foreach ($news as $key => $new)
        {
            $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')->find($key);
            $tachePrioriteDossier = new TachePrioriteDossier();
            $tachePrioriteDossier
                ->setDate($new->d)
                ->setDossier($dossier)
                ->setDateCalcul(new \DateTime());

            if ($new->t == 0) $tachePrioriteDossier->setGoogleId($new->id);
            elseif ($tachePrioriteDossier) $tachePrioriteDossier->setTacheSynchro($new->id);

            /*'d' => clone $d,
            'id' => $tacheSynchro->getId(),
            't' => 1*/

            $em->persist($tachePrioriteDossier);
        }

        $em->flush();
    }
}