<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 06/05/2016
 * Time: 14:57
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Operateur;
use AppBundle\Functions\CustomPdoConnection;
use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityRepository;

class OperateurRepository extends EntityRepository
{

    /**
     * Listes des opérateurs triés par nom
     *
     * @return array
     */
    public function getAllOperateur()
    {
        $operateurs = $this->getEntityManager()
            ->getRepository('AppBundle:Operateur')
            ->createQueryBuilder('o')
            ->where('o.supprimer = :supprimer')
            ->setParameter('supprimer', 0)
            ->orderBy('o.nom')
            ->addOrderBy('o.prenom')
            ->getQuery()
            ->getResult();

        return $operateurs;
    }
	
	/**
     * Listes des opérateurs responsable
     *
     * @return array
     */
	public function getAllResponsable()
    {
		$con = new CustomPdoConnection();
        $pdo = $con->connect();
        //  OR organisation_id = 208
        /*$query = "SELECT id,nom,prenom FROM operateur
				  WHERE supprimer = 0 
				  AND (organisation_id = 107 OR organisation_id = 148 OR organisation_id = 156 OR organisation_id = 185 OR organisation_id = 208) 
				  ORDER BY nom ASC";*/
        $query = "SELECT id,nom,prenom FROM operateur  
				  WHERE supprimer = 0 
				  AND affecter_dossier = 1
				  ORDER BY nom ASC";

        $prep = $pdo->prepare($query);
        $prep->execute();
		
		$operateurs = $prep->fetchAll();
       
        return $operateurs;
    }

	
    /**
     * Listes des opérateurs triés par prénom
     *
     * @return array
     */
    public function getAllOperateurByPrenom()
    {
        $operateurs = $this->getEntityManager()
            ->getRepository('AppBundle:Operateur')
            ->createQueryBuilder('o')
            ->where('o.supprimer = :supprimer')
            ->setParameter('supprimer', 0)
            ->orderBy('o.prenom', 'ASC')
            ->getQuery()
            ->getResult();

        return $operateurs;
    }


    public function getOperateurControleSaisie()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        //Requête qui récupère la capacité par groupe cad la somme des capacités par chaque opérateur de saisie
        $subquery = "(SELECT sum(org.capacite) as capacite FROM operateur o JOIN organisation org on o.organisation_id=org.id  
                    JOIN rattachement r ON r.operateur_rat_id=o.id WHERE r.operateur_id IN
                    (SELECT operateur_id FROM rattachement WHERE operateur_rat_id in  
                    (SELECT oper.id
                     FROM operateur  oper
                                      WHERE oper.supprimer = 0 
                                      AND oper.organisation_id = 222 AND oper.id=O.id)) AND org.id=249) as capacite ";
        $query = "SELECT O.id,O.nom,O.prenom,O.coeff, ";
        $query .= "(SELECT capacite FROM organisation WHERE id=O.organisation_id) as capacite_user,";
        $query .= $subquery;
        $query .= " FROM operateur  O
				  WHERE O.supprimer = 0 
				  AND O.organisation_id = 222
				  ORDER BY O.prenom ASC";

        $prep = $pdo->prepare($query);
        $prep->execute();

        $operateurs = $prep->fetchAll();
        return $operateurs;
    }

    public function getUserTenue($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        //Requête qui récupère la capacité par groupe cad la somme des capacités par chaque opérateur de saisie
        $subquery = "(SELECT sum(org.capacite) as capacite FROM operateur o JOIN organisation org on o.organisation_id=org.id  
                    JOIN rattachement r ON r.operateur_rat_id=o.id WHERE r.operateur_id IN
                    (SELECT operateur_id FROM rattachement WHERE operateur_rat_id in  
                    (SELECT oper.id
                     FROM operateur  oper
                                      WHERE oper.supprimer = 0 
                                      AND oper.organisation_id = 208 AND oper.id=O.id))) as capacite ";
        $query = "SELECT O.id,O.nom,O.prenom,O.coeff, ";
        $query .= "(SELECT capacite FROM organisation WHERE id=O.organisation_id) as capacite_user,";
        $query .= $subquery;
        $query .= " FROM operateur  O
				  WHERE O.supprimer = 0 AND O.id!=:userId 
				  AND O.organisation_id =:organisationId
				  ORDER BY O.prenom ASC";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId' => $userId,
            'organisationId' => 208, //Manager
        ));

        $operateurs = $prep->fetchAll();
        return $operateurs;
    }


    public function getPourcentageSaisieGroupe($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT count(p.image_id) as nb , op.id, (SELECT capacite FROM organisation WHERE id=op.organisation_id) as capacite
                  from panier p JOIN operateur op on p.operateur_id=op.id WHERE p.operateur_id in 
                  (SELECT o.id FROM operateur o JOIN organisation org ON o.organisation_id = org.id 
                  WHERE o.id in (SELECT operateur_rat_id FROM rattachement WHERE operateur_id in 
                  (SELECT operateur_id FROM rattachement WHERE operateur_rat_id=:userId)) AND org.id=167) GROUP BY p.operateur_id";

        $prep = $pdo->prepare($query);

        $prep->execute(array(
            'userId' => $userId,
        ));

        $pourcentage_groupe = $prep->fetchAll();
        return $pourcentage_groupe;
    }


    public function getOperateurEtape(EtapeTraitement $etape, $userId =null){

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if($userId !== null && $userId !== 239) {
            $query = "SELECT o.id, o.prenom, o.coeff, org.capacite  FROM operateur o JOIN organisation org ON o.organisation_id=org.id 
                WHERE (o.supprimer = :supprimer AND org.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape)
                AND o.id IN 
                 (select operateur_rat_id from rattachement where operateur_id=:userId))
                ";
        }
        else{
            $query = "SELECT o.id, o.prenom, o.coeff, org.capacite  FROM operateur o JOIN organisation org ON o.organisation_id=org.id 
                WHERE (o.supprimer = :supprimer AND org.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape))                ";
        }


        $prep = $pdo->prepare($query);

        $param = ['etape' => $etape->getId(), 'supprimer' => 0];

        if($userId !== null && $userId !== 239){
            $param['userId'] = $userId;
        }

        $prep->execute($param);


        $operateurs = $prep->fetchAll();
        return $operateurs;
    }


    public function getOperateurEtapeOld(EtapeTraitement $etape, $userId) {
        $poste_ids = $etape->getPostes();
        $poste_ids[] = 0;
        $listePoste = implode(',', $poste_ids);



        /*$qb = $this->getEntityManager()
            ->getRepository('AppBundle:Operateur')
            ->createQueryBuilder('o');
        $operateurs = $qb->select('o')
            ->innerJoin('o.organisation', 'organisation')
            ->where($qb->expr()->in('organisation.id', $poste_ids))
            ->andWhere('o.supprimer = 0')
            ->orWhere('o.id IN (59,239)')
            ->orderBy('o.prenom')
            ->getQuery()
            ->getResult();*/
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT o.id, o.prenom, o.coeff, org.capacite  FROM operateur o JOIN organisation org ON o.organisation_id=org.id 
                WHERE (o.supprimer = :supprimer AND org.id in (". $listePoste .")
                AND o.id IN 
                (select operateur_rat_id from rattachement where operateur_id in (select operateur_id from rattachement where operateur_rat_id=:userId)))
                ";
        //OR o.id in (59, 239)

        $prep = $pdo->prepare($query);

        $prep->execute(array(
            'supprimer' => 0,
            'userId' => $userId,
        ));
        $operateurs = $prep->fetchAll();
        return $operateurs;   
    }

    public function getOperateurCellule($code_cellule, $to_array = FALSE)
    {
        $qb = $this->getEntityManager()
            ->getRepository('AppBundle:Operateur')
            ->createQueryBuilder('o');
        $operateurs = $qb
            ->select('o')
            ->innerJoin('o.poste', 'p')
            ->addSelect('p')
            ->innerJoin('p.cellule', 'c')
            ->where('c.code = :code')
            ->setParameter('code', $code_cellule)
            ->orderBy('o.prenom')
            ->getQuery();
        if ($to_array) {
            return $operateurs->getArrayResult();
        }
        return $operateurs->getResult();
    }

    public function getAllOperateurGroupByPoste()
    {
        $operateurs = [];
        $items = $this->getEntityManager()
            ->getRepository('AppBundle:Operateur')
            ->createQueryBuilder('op')
            ->select('op')
            ->where('op.supprimer = :supprimer')
            ->leftJoin('op.organisation', 'org')
            ->addSelect('org')
            ->setParameters(array(
                'supprimer' => 0
            ))
            ->orderBy('org.nom')
            ->addOrderBy('op.prenom')
            ->getQuery()
            ->getResult();
        /** @var \AppBundle\Entity\Operateur $item */
        foreach ($items as $item) {
            if ($item->getOrganisation()) {
                $operateurs[$item->getOrganisation()->getId()][] = $item;
            } else {
                $operateurs[0][] = $item;
            }

        }
        ksort($operateurs);
        return $operateurs;
    }

    public function operateurActifByOrganisation(Organisation $organisation)
    {
        $operateurs = $this->getEntityManager()
            ->getRepository('AppBundle:Operateur')
            ->createQueryBuilder('o')
            ->select('o')
            ->innerJoin('o.organisation', 'org')
            ->where('o.supprimer = :supprimer')
            ->andWhere('org = :org')
            ->setParameter('supprimer', 0)
            ->setParameter('org', $organisation)
            ->orderBy('o.prenom', 'ASC')
            ->getQuery()
            ->getResult();
        return $operateurs;
    }
}