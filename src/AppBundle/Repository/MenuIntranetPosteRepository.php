<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 15/03/2019
 * Time: 10:54
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;

class MenuIntranetPosteRepository extends EntityRepository
{
    function getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, menu_intranet_id, organisation_id FROM menu_intranet_poste";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $OrgMenu = $prep->fetchAll();
        return $OrgMenu;
    }
}