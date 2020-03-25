<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 07/09/2018
 * Time: 11:33
 */

namespace AppBundle\Repository;


use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class HistoriqueCategorieRepository extends EntityRepository
{
    public function getNombreEnAchat($dossier, $exercice){

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(DISTINCT i.id) AS nbimage FROM historique_categorie h 
                        INNER JOIN image i ON i.id = h.image_id 
                        INNER JOIN lot l ON l.id = i.lot_id 
                        WHERE i.exercice = :exercice AND l.dossier_id = :dossier AND h.motif = 'Facturette' ;";


        $prep = $pdo->prepare($query);
        $prep->execute([
            'exercice' => $exercice,
            'dossier' => $dossier
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if (count($res) > 0) {
            $images = $res[0]->nbimage;
        }

        return $images;

    }

    public function getListeByBanqueCompte($banquecompte_id, $exercice){

        $query = 'SELECT * FROM historique_categorie hc WHERE image_id IN (SELECT i.id FROM saisie_controle sc INNER JOIN image i ON i.id = sc.image_id WHERE banque_compte_id = :banquecompte_id AND i.exercice = :exercice);';

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'banquecompte_id' => $banquecompte_id,
            'exercice' => $exercice
        ));

        return $prep->fetchAll();

    }

}