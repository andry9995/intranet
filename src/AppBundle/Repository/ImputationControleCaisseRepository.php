<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 28/08/2018
 * Time: 17:33
 */

namespace AppBundle\Repository;


use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class ImputationControleCaisseRepository extends EntityRepository
{
    public function getCaisseByMontant($dossier, $montant){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select ca.id as caisse_id,ca.date ,i.nom as image_nom, i.id as image_id, ca.libelle,ca.sortie_ttc 
                    from imputation_caisse ca
				    inner join image i on i.id = ca.image_id
                    inner join lot l on l.id = i.lot_id
                    where l.dossier_id = :dossier_id and ca.sortie_ttc = :montant";

        $prep = $pdo->prepare($query);
        $prep->execute(
            [
                'dossier_id' => $dossier,
                'montant' => $montant
            ]
        );
        $res = $prep->fetchAll();

        return $res;

    }
}