<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 05/11/2018
 * Time: 14:13
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\RegimeFiscal;
use AppBundle\Entity\RegimeImposition;
use AppBundle\Entity\RegimeTva;
use AppBundle\Entity\Taches;
use AppBundle\Entity\TachesGroup;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class TachesRepository extends EntityRepository
{
    /**
     * @param RegimeFiscal|null $regimeFiscal
     * @param TachesGroup|null $tachesGroup
     * @return Taches[]
     */
    public function getListe(RegimeFiscal $regimeFiscal = null, TachesGroup $tachesGroup = null)
    {
        $resutls = $this->createQueryBuilder('t');
        if ($regimeFiscal) $resutls = $resutls
            ->where('t.regimeFiscal = :regimeFiscal')
            ->setParameter('regimeFiscal',$regimeFiscal);
        else $resutls = $resutls
            ->where('t.tachesGroup = :tachesGroup')
            ->setParameter('tachesGroup',$tachesGroup);

        return $resutls
            ->orderBy('t.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Client $client
     * @param Dossier|null $dossier
     * @return Taches[]
     */
    public function getListeForClient(Client $client,Dossier $dossier = null)
    {
        /** @var RegimeFiscal[] $regimeFiscals */
        $regimeFiscals = $this->getEntityManager()->getRepository('AppBundle:RegimeFiscal')
            ->createQueryBuilder('rf')
            ->where('rf.id in (:ids)')
            ->setParameter('ids',$this->getIdsEntityInfoperdos($client,$dossier,'regime_fiscal_id'))
            ->getQuery()
            ->getResult();

        /** @var RegimeImposition[] $regimeImpositions */
        $regimeImpositions = $this->getEntityManager()->getRepository('AppBundle:RegimeImposition')
            ->createQueryBuilder('ri')
            ->where('ri.id in (:ids)')
            ->setParameter('ids',$this->getIdsEntityInfoperdos($client,$dossier,'regime_imposition_id'))
            ->getQuery()
            ->getResult();

        /** @var RegimeTva[] $regimesTvas */
        $regimesTvas = $this->getEntityManager()->getRepository('AppBundle:RegimeTva')
            ->createQueryBuilder('rt')
            ->where('rt.id IN (:ids)')
            ->setParameter('ids', $this->getIdsEntityInfoperdos($client,$dossier,'regime_tva_id'))
            ->getQuery()
            ->getResult();

        return $this->getEntityManager()->getRepository('AppBundle:TachesItem')
            ->getTachesByRegimes($regimeFiscals,$regimeImpositions,$regimesTvas);
    }

    /**
     * @param Client $client
     * @param Dossier|null $dossier
     * @param string $table
     * @return array
     */
    private function getIdsEntityInfoperdos(Client $client,Dossier $dossier = null, $table = '')
    {
        if ($table == '') return [];
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "
            SELECT DISTINCT d.".$table." AS id FROM dossier d
            JOIN site s ON (d.site_id = s.id)
            WHERE s.client_id = :client AND d.".$table." IS NOT NULL ";
        $params = ['client'=>$client->getId()];
        if ($dossier)
        {
            $query .= "AND d.id = :dossier";
            $params['dossier'] = $dossier->getId();
        }
        $prep = $pdo->prepare($query);
        $prep->execute($params);
        $regimes = $prep->fetchAll();
        $ids = [];
        foreach ($regimes as $regime) $ids[] = $regime->id;

        return $ids;
    }

    /**
     * @param Taches $taches
     * @param Dossier $dossier
     * @return int
     */
    public function getStatusForDossier(Taches $taches, Dossier $dossier)
    {
        $tachesItems = $this->getEntityManager()->getRepository('AppBundle:TachesItem')
            ->getForDossier($dossier,$taches);

        $this->getEntityManager()->getRepository('AppBundle:TachesEntity')
            ->deleteNotForDossier($dossier,$taches, $tachesItems);

        $status = -1;
        if (count($tachesItems) != 0)
        {
            foreach ($tachesItems as $tachesItem)
            {
                $s = $this->getEntityManager()->getRepository('AppBundle:TachesDate')
                    ->getStatus($tachesItem,$dossier);

                if ($status < $s) $status = $s;
            }
        }

        return $status;
    }
}