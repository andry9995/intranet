<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 15/03/2019
 * Time: 16:41
 */

namespace AppBundle\Repository;

use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class UniteOeuvreRepository extends EntityRepository
{
    public function  getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, libelle FROM unite_oeuvre ORDER BY libelle";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $unite = $prep->fetchAll();
        return $unite;
    }

}