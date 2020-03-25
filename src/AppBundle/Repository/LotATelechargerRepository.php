<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 17/01/2019
 * Time: 16:41
 */

namespace AppBundle\Repository;


use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;


class LotATelechargerRepository extends  EntityRepository
{
    public function getDowloadEnCour()
    {

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT cabinet,dossier,exercice,date_scan, lot, status FROM lot_a_telecharger ORDER BY id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $downloadEnCour = $prep->fetchAll();
        return $downloadEnCour;
    }

    public function viderLotATelecharger()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "DELETE FROM lot_a_telecharger WHERE id>0";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return true;
    }
}