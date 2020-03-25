<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 26/02/2019
 * Time: 12:04
 */

namespace AppBundle\Repository;

use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;


class LotUserGroupRepository extends EntityRepository
{
    public  function removeLotGroupe($userGroupId, $lotId, $etape)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "DELETE FROM lot_user_group WHERE usergroup_id = :userGroupId AND lot_id = :lotId AND code = :codeEtape";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userGroupId' => $userGroupId,
            'lotId' => $lotId,
            'codeEtape' => $etape,
        ));
        return true;
    }

    public function addToLotGroupe($userGroupId, $lotId, $categorieId, $etape, $datePanier)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if ($categorieId != 0) {
            $query = "DELETE FROM lot_user_group WHERE usergroup_id=:userGroupId AND lot_id =:lotId AND categorie_id = :categorieId AND code =:codeEtape";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'lotId' => $lotId,
                'categorieId' => $categorieId,
                'codeEtape' => $etape,
                'userGroupId' => $userGroupId,
            ));
            $query = "INSERT INTO lot_user_group (usergroup_id, lot_id, categorie_id, code, date_panier)
                    VALUES(:userGroupId, :lotId, :categorieId, :codeEtape, :datePanier)";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userGroupId' => $userGroupId,
                'lotId' => $lotId,
                'categorieId' => $categorieId,
                'codeEtape' => $etape,
                'datePanier' => $datePanier,
            ));
        }
        else
        {
            $query = "DELETE FROM lot_user_group WHERE usergroup_id =:userGroupId AND lot_id =:lotId AND code = :codeEtape";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userGroupId' => $userGroupId,
                'lotId' => $lotId,
                'codeEtape' => $etape,
            ));

            $query = "INSERT INTO lot_user_group (usergroup_id, lot_id, code, date_panier) VALUES(:userGroupId, :lotId, :codeEtape, :datePanier)";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
               'userGroupId' => $userGroupId,
               'lotId' => $lotId,
                'codeEtape' => $etape,
                'datePanier' => $datePanier,
            ));
        }
        return true;
                
    }


    public function addToLotTenue($userGroupId, $lotId, $categorieId, $etape)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if ($categorieId != 0) {
            $query = "DELETE FROM affectation_panier_tenue WHERE operateur_id=:userGroupId AND lot_id =:lotId AND categorie_id = :categorieId AND code=:codeEtape";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'lotId' => $lotId,
                'categorieId' => $categorieId,
                'codeEtape' => $etape,
                'userGroupId' => $userGroupId,
            ));
            $query = "INSERT INTO affectation_panier_tenue (operateur_id, lot_id, categorie_id, code)
                    VALUES(:userGroupId, :lotId, :categorieId, :codeEtape)";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userGroupId' => $userGroupId,
                'lotId' => $lotId,
                'categorieId' => $categorieId,
                'codeEtape' => $etape,
            ));
        }
        else
        {
            $query = "DELETE FROM affectation_panier_tenue WHERE operateur_id =:userGroupId AND lot_id =:lotId AND code = :codeEtape";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userGroupId' => $userGroupId,
                'lotId' => $lotId,
                'codeEtape' => $etape,
            ));

            $query = "INSERT INTO affectation_panier_tenue (operateur_id, lot_id, code) VALUES(:userGroupId, :lotId, :codeEtape)";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userGroupId' => $userGroupId,
                'lotId' => $lotId,
                'codeEtape' => $etape,
            ));
        }
        return true;

    }


}