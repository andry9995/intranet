<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 24/08/2018
 * Time: 08:59
 */

namespace AppBundle\Repository;


use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class ImputationControleRepository extends EntityRepository
{
    public function getListeImageByMontantTtc($dossier,$montant, $exercice){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

//        $query = "SELECT i.id image_id, ROUND(SUM(t.montant_ht + t.montant_ht * tva.taux ),2)
//                    montant ,i.nom image_nom, ic.date_facture, t.libelle tiers, ic.id as imputation_id
//                    from imputation_controle ic
//					inner join image i on i.id = ic.image_id
//                    inner join lot l on i.lot_id = l.id
//                    inner join tva_imputation t on i.id = t.image_id
//                    inner join tva_taux tva on tva.id = t.tva_taux_id
//                    inner join tiers ti on t.tiers_id = ti.id
//                    where  l.dossier_id = :dossier_id and i.exercice = :exercice
//                    group by image_id having montant = :montant_ttc;";


//        $prep->execute(
//            [
//                'dossier_id' => $dossier,
//                'montant_ttc' => $montant,
//                'exercice' => $exercice
//            ]
//        );

        $query = "SELECT i.id image_id, ROUND(SUM(t.montant_ht + t.montant_ht * tva.taux ),2) 
                    montant ,i.nom image_nom, ic.date_facture, t.libelle tiers, ic.id as imputation_id 
                    from imputation_controle ic
					inner join image i on i.id = ic.image_id
                    inner join lot l on i.lot_id = l.id 
                    inner join tva_imputation t on i.id = t.image_id 
                    inner join tva_taux tva on tva.id = t.tva_taux_id 
                    inner join tiers ti on t.tiers_id = ti.id 
                    where i.exercice = :exercice
                    group by image_id limit 10";


        $prep = $pdo->prepare($query);
        $prep->execute(
            [
                'exercice' => $exercice
            ]
        );
        $res = $prep->fetchAll();

        return $res;
    }

}