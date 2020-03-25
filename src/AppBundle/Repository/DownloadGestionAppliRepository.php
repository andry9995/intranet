<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 24/01/2019
 * Time: 15:55
 */

namespace AppBundle\Repository;

use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class DownloadGestionAppliRepository extends EntityRepository
{

    public function fermerDownload()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE download_gestion_appli SET status=:status WHERE id>0";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 1,
        ));
        return true;
    }

    public function lancerDownload()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE download_gestion_appli SET status=:status WHERE id>0";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 3,
        ));
        return true;
    }

}