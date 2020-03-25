<?php
/**
 * Created by PhpStorm.
 * User: Dinoh
 * Date: 16/05/2019
 * Time: 16:32
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;

class TvaImputationControleRepository extends EntityRepository
{
    public function getImageFlaguesByImageid($imageId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT r.image_id, im.nom, ic.num_facture, tic.libelle
                FROM tva_imputation_controle tic
                left join imputation_controle ic on ic.image_id = tic.image_id
                JOIN image i on i.id = tic.image_id
                JOIN releve r on r.image_flague_id = tic.image_flague_id
                JOIN image im on im.id = r.image_id
                WHERE i.id = ".$imageId."
                GROUP BY tic.image_id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $res = $prep->fetchAll();
        if(count($res) > 0) return $res;
        $query = "SELECT r.image_id, im.nom, ic.num_facture, bsca.libelle
            FROM banque_sous_categorie_autre bsca
            left join imputation_controle ic on ic.image_id = bsca.image_id
            JOIN image i on i.id = bsca.image_id
            JOIN releve r on r.image_flague_id = bsca.image_flague_id
            JOIN image im on im.id = r.image_id
            WHERE i.id = ".$imageId."
            GROUP BY bsca.image_id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }
}