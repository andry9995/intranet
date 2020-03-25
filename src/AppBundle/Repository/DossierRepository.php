<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Site;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Dossier;
use AppBundle\Functions\CustomPdoConnection;

class DossierRepository extends EntityRepository
{
    function getDossierByClient(Client $client)
    {
        $dossiers = $this->getEntityManager()
            ->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->select('d')
            ->innerJoin('d.site', 's')
            ->where("s.client = :client")
            ->andWhere("d.nom != ''")
            //->andWhere("d.status = :status")
            ->setParameter('client', $client)
            //->setParameter('status', 1)
            ->orderBy('d.nom')
            ->getQuery()
            ->getResult();
        return $dossiers;
    }

    function getAllDossierObject(Client $client = null,$dossier = false)
    {
        $idsTemps = $this->getAllDossier($client,$dossier);
        $ids = [];
        foreach ($idsTemps as $idsTemp) $ids[] = $idsTemp['iddossier'];

        return $this->createQueryBuilder('d')
            ->where('d.id IN (:ids)')
            ->setParameter('ids',$ids)
            ->orderBy('d.nom')
            ->getQuery()
            ->getResult();
    }

	function getAllDossier(Client $client = null, $dossier = false )
    {
        if ($client) {
            if(!$dossier) {
                $dossiers = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->createQueryBuilder('d')
                    ->select('DISTINCT (d.id) AS iddossier')
                    ->innerJoin('d.site', 's')
                    ->where("s.client = :client")
                    ->andWhere("d.nom != ''")
                    ->andWhere("d.status = :status")
                    ->setParameter('client', $client)
                    ->setParameter('status', 1)
                    ->orderBy('d.nom')
                    ->getQuery()
                    ->getResult();
            }else{
                $dossiers = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->createQueryBuilder('d')
                    ->select('DISTINCT (d.id) AS iddossier')
                    ->innerJoin('d.site', 's')
                    ->where("s.client = :client")
                    ->andWhere("d.nom != ''")
                    ->andWhere("d.id = :idossier")
                    ->andWhere("d.status = :status")
                    ->setParameter('client', $client)
                    ->setParameter('idossier', $dossier)
                    ->setParameter('status', 1)
                    ->orderBy('d.nom')
                    ->getQuery()
                    ->getResult();
            }
        }else {
            $dossiers = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->createQueryBuilder('d')
                ->select('DISTINCT (d.id) AS iddossier')
                ->innerJoin('d.site', 's')
                ->where("d.nom != ''")
                ->andWhere("d.status = :status")
                ->setParameter('status', 1)
                ->orderBy('d.nom')
                ->getQuery()
                ->getResult();
        }
        return $dossiers;
    }

    function getDossierBySite(Site $site) {
        $dossiers = $this->getEntityManager()
            ->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->select('d')
            ->where("d.site = :site")
            //->andWhere("d.status = :status")
            ->andWhere("d.nom != ''")
            ->setParameter('site', $site)
            // ->setParameter('status', 1)
            ->orderBy('d.nom')
            ->getQuery()
            ->getResult();
        return $dossiers;
    }

    /**
     * @param Dossier $dossier
     * @param $exercice
     * @return static
     * @throws \Exception
     *
     */
    function getDateCloture(Dossier $dossier,$exercice)
    {
        $mois_cloture = $dossier->getCloture();
        $mois_cloture++;
        if ($mois_cloture == 13)
        {
            $mois_cloture = 1;
            $exercice++;
        }
        if($mois_cloture < 10) $mois_cloture = '0'.$mois_cloture;
        $date_temp = new \DateTime($exercice.'-'.$mois_cloture.'-01');
        return $date_temp->sub(new \DateInterval('P1D'));
    }
	  /**
     * @param Dossier $dossier
     * @param $exercice
     * @return \DateTime
     */
    function getDateDebut(Dossier $dossier,$exercice)
    {
        $cloture = $this->getDateCloture($dossier,$exercice);
        $year = intval($cloture->format('Y') - 1);
        $cloture_1 = new \DateTime($year.$cloture->format('-m-d'));
        return $cloture_1->add(new \DateInterval('P1D'));
    }
	
	function getDossierClient($client, $actif=true){
		$con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT D.id,D.nom FROM dossier D,site S,client  C 
					WHERE S.id = D.site_id
					AND C.id = S.client_id
					AND C.id =".$client."
					AND D.nom <>'' ";

        if($actif){
            $query .=" AND D.status = 1 ";
        }

        $query .= " ORDER BY D.nom";
                    
        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
	}

	function getStatusDossier(Dossier $dossier, $exercice){
        if($dossier->getStatus() !== 1) {
            if ($dossier->getStatusDebut() !== null) {
                if ($exercice >= $dossier->getStatusDebut()) {
                    if ($dossier->getStatus() === 2) {
                        return 'Suspendu depuis ' . $dossier->getStatusDebut();
                    } else {
                        return 'Radié depuis ' . $dossier->getStatusDebut();
                    }
                }
            } else {
                if ($dossier->getStatus() === 2) {
                    return 'Suspendu';
                } else {
                    return 'Radié';
                }
            }
        }
        return '';
    }
}