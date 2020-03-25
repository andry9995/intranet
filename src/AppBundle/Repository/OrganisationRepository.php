<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 22/01/2018
 * Time: 16:58
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;

class OrganisationRepository extends EntityRepository
{
    public function getAllPoste()
    {
        $postes = $this->getEntityManager()
            ->getRepository('AppBundle:Organisation')
            ->createQueryBuilder('org')
            ->select('org')
            ->innerJoin('org.organisationNiveau', 'niveau')
            ->where('niveau.isPoste = :is_poste')
            ->andWhere("org.nom != '' AND org.nom IS NOT NULL")
            ->orderBy('org.nom')
            ->setParameters(array(
                'is_poste' => TRUE
            ))
            ->getQuery()
            ->getResult();
        return $postes;
    }

    public function getManagerAndSuperviseur()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT  op.id, op.prenom, op.nom, op.organisation_id  
                  FROM operateur op JOIN organisation o ON op.organisation_id=o.id WHERE o.id in (107, 109)";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $resultat = $prep->fetchAll();
        return $resultat;
    }

    public function getChefSuperieur($orgId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, prenom, nom, organisation_id FROM operateur WHERE organisation_id in
                (SELECT  organisation_id FROM organisation WHERE id=:id) AND supprimer=:supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'id' => $orgId,
            'supprimer' => 0,
        ));
        $resultat = $prep->fetchAll();
        if ($resultat != null)
            return $resultat;
        else
        {
            $resultat = null;
            //Monter jusqu'au niveau 5
            $iCpt = 0;
            $iNiveau = 5;
            $orgIdNiv = $orgId;
            $bExist = false;
            while (!$bExist)
            {
                if ($iCpt <= $iNiveau)
                {
                    /** @var Organisation $orgIdTmp */
                    $orgIdTmp = $this->getEntityManager()
                        ->getRepository('AppBundle:Organisation')
                        ->createQueryBuilder('org')
                        ->where("org.id=:idOrg")
                        ->setParameter('idOrg', $orgIdNiv )
                        ->getQuery()
                        ->getOneOrNullResult();
                    if ($orgIdTmp) {

                        $query = "SELECT id, prenom, nom, organisation_id FROM operateur WHERE organisation_id in
                                  (SELECT  organisation_id FROM organisation WHERE id=:id) AND supprimer=:supprimer";
                        $prep = $pdo->prepare($query);
                        $prep->execute(array(
                            'id' => $orgIdTmp->getOrganisation()->getId(),
                            'supprimer' => 0,
                        ));
                        $resultat = $prep->fetchAll();
                        if ($resultat !=null)
                            $bExist = true;
                        else
                            $orgIdNiv = $orgIdTmp->getOrganisation()->getId();
                    }
                    else
                        break;
                }
                $iCpt++;
            }
            return $resultat;
        }

    }

    public function updateOrg($orgId, $orgParentId, $orgNiveau)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE  organisation SET organisation_niveau_id = :orgNivId, organisation_id = :orgParentId WHERE id=:id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'orgNivId' => $orgNiveau,
            'orgParentId' => $orgParentId,
            'id' => $orgId,
        ));

        return true;
    }

    public function updateOrgOld($id)
    {
        try
        {
            $con = new CustomPdoConnection();
            $pdo = $con->connect();

            $query = "UPDATE  organisation SET organisation_id = null WHERE id=:id";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'id' => $id,
            ));

            $query = "UPDATE  organisation SET organisation_id = null WHERE organisation_id=:id AND id>0";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'id' => $id,
            ));
            return true;
        } catch(Exception $e){
            print_r($e);
            return $e;
        }

    }

    public function getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, nom FROM organisation WHERE nom!=:nom ORDER BY nom";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'nom' => '',
        ));
        $postes = $prep->fetchAll();
        return $postes;
    }
}