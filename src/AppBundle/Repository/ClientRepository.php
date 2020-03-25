<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;


class ClientRepository extends EntityRepository
{
    public function getAllClient()
    {
        $clients = $this->getEntityManager()
            ->getRepository('AppBundle:Client')
            ->createQueryBuilder("c")
            ->select("c")
            ->where("c.nom != ''")
            ->andWhere("c.status = :status")
            ->setParameters(array(
                'status' => 1
            ))
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();
        return $clients;
    }

    public function getAllClientByResponsable($operateurId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT c.nom as nom, c.id as id 
                  FROM responsable_client rc
                  INNER JOIN client c ON (rc.client = c.id)
                  WHERE c.nom IS NOT NULL
                  AND c.status = 1
                  AND rc.responsable = " . $operateurId ;
        $query .= " ORDER BY c.nom";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;  

    }

    /**
     * @return string
     */
    public function defaultTacheColor() { return '#778899'; }
}