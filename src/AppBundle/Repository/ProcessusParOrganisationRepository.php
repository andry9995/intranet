<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 15/03/2019
 * Time: 07:42
 */

namespace AppBundle\Repository;

use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class ProcessusParOrganisationRepository extends EntityRepository
{
    function getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT porg.id, porg.processus_id, porg.organisation_id, org.nom FROM processus_par_organisation
                  porg JOIN organisation org ON porg.organisation_id=org.id ORDER BY porg.organisation_id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $pOrg = $prep->fetchAll();

        return $pOrg;
    }

    public function saveRelation($tableaux)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $tabPostes = array();
        foreach ($tableaux as $tab ) {

            if (intval($tab["process-id"]) != 0) {
                $processId = $tab["process-id"];
                $sql = "DELETE FROM processus_par_organisation WHERE processus_id=:processusId";
                $prep = $pdo->prepare($sql);
                $prep->execute(array(
                    'processusId' => $processId,
                ));
            }
            else
                $processId =  null;
            if (array_key_exists("poste-id", $tab)) {
                if ($processId != null) {


                    //Enregistrer processus par organisation
                    foreach ($tab["poste-id"] as $poste) {
                        $posteId = $poste;

                        $sql = "INSERT INTO processus_par_organisation (processus_id, organisation_id) VALUES(:processusId,:posteId)";
                        $prep = $pdo->prepare($sql);
                        $prep->execute(array(
                            'processusId' => $processId,
                            'posteId' => $posteId,
                        ));
                        if (array_key_exists($posteId, $tabPostes) == false) {
                            $tabPostes[$posteId] = array();
                        }
                    }
                }
            }
            //Enregistrer menus par processus
            if ($processId != null)
            {
                //Supprimer d'abord les menus liés au processus en cours
                $sql = "DELETE FROM processus_menu_intranet WHERE processus_id=:processusId";
                $prep = $pdo->prepare($sql);
                $prep->execute(array(
                    'processusId' => $processId,
                ));
                if (array_key_exists("menus-id", $tab)){
                    //Enregistrer maintenant les menus par processus
                    foreach ($tab["menus-id"] as $menu) {
                        $menuId = $menu;
                        $sql = "INSERT INTO processus_menu_intranet (processus_id, menu_intranet_id) VALUES(:processusId,:menuId)";
                        $prep = $pdo->prepare($sql);
                        $prep->execute(array(
                            'processusId' => $processId,
                            'menuId' => $menuId,
                        ));

                    }
                }

            }
        }
        //Enregistrer dans menu_intranet_poste
        if (count($tabPostes) > 0)
        {
            $listePostes = "";
            foreach ($tabPostes as $key => $value)
            {
                if ($listePostes == "")
                    $listePostes .= $key;
                else
                    $listePostes .= ",".$key;
            }
            $sql = "DELETE FROM menu_intranet_poste WHERE organisation_id in (".$listePostes.")";
            $prep = $pdo->prepare($sql);
            $prep->execute();

            $sql = "INSERT INTO menu_intranet_poste (menu_intranet_id, organisation_id) 
                    (select  pmi.menu_intranet_id,ppo.organisation_id from processus_par_organisation ppo
                     join processus_menu_intranet pmi on ppo.processus_id=pmi.processus_id  )";
            $prep = $pdo->prepare($sql);
            $prep->execute();
        }
        return true;
    }
}