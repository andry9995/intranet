<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 28/08/2018
 * Time: 16:37
 */

namespace AppBundle\Repository;


use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class SaisieNdfDepenseRepository extends EntityRepository
{

    public function getNdfDepensesByMontant($dossier, $montant){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select dep.id as depense_id,dep.date ,i.nom as image_nom,note.libelle,dep.ttc 
                    from saisie1_ndf_depense dep 
                    inner join saisie1_ndf_note note on note.id=dep.saisie1_ndf_note_id
                    inner join image i on i.id = note.image_id
                    inner join lot l on l.id = i.lot_id
                    where l.dossier_id = :dossier_id and dep.ttc = :montant";

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