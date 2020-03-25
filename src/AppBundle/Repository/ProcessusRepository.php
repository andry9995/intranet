<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 13/03/2019
 * Time: 09:31
 */

namespace AppBundle\Repository;

use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;


class ProcessusRepository extends EntityRepository
{
    public function getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, nom, rang FROM processus WHERE processus_id IS NULL AND actif = :actif ORDER BY rang";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 1,
        ));
        $processus = $prep->fetchAll();

        $query = "SELECT id, nom, rang, processus_id, description, unite_oeuvre_id,
                    (select libelle from unite_oeuvre where id=unite_oeuvre_id) as nomUnite,
                    temps_trait,
                    process_ant_id,
                    (select nom from processus  where id=pro.process_ant_id) as processAnt, 
                    process_post_id,
                    (select nom from processus  where id=pro.process_post_id) as processPost
                      FROM processus pro WHERE processus_id IS NOT NULL AND actif = :actif ORDER BY processus_id,rang;";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 1,
        ));
        $process = $prep->fetchAll();



        return array('processus' => $processus, 'process'=> $process);
    }

    public function deleteProcess($processId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE processus SET actif =:actif WHERE id=:processId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 0,
            'processId' => $processId,
        ));

        return true;
    }

    public function deleteProcessus($processusId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE processus SET actif =:actif WHERE id=:processusId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 0,
            'processusId' => $processusId,
        ));

        $query = "UPDATE processus SET actif =:actif WHERE processus_id=:processusId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 0,
            'processusId' => $processusId,
        ));

        return true;
    }

    public function getProcessus()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, nom, rang FROM processus WHERE processus_id IS NULL AND actif = :actif ORDER BY rang";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 1,
        ));
        $processus = $prep->fetchAll();
        return $processus;
    }

    public function getProcess()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, nom, rang, processus_id FROM processus WHERE processus_id IS NOT NULL AND actif = :actif ORDER BY rang";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 1,
        ));
        $process = $prep->fetchAll();
        return $process;
    }

    public function getInfosProcessById($processId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, nom, rang, processus_id, description, unite_oeuvre_id,
                    (select libelle from unite_oeuvre where id=unite_oeuvre_id) as nomUnite,
                    temps_trait,
                    process_ant_id,
                    (select nom from processus  where id=pro.process_ant_id) as processAnt, 
                    process_post_id,
                    (select nom from processus  where id=pro.process_post_id) as processPost
                      FROM processus pro WHERE id=:id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'id' => $processId,
        ));
        $process = $prep->fetchAll();
        return $process;
    }



    public function getNewRang()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT max(rang) as maxrang FROM processus WHERE processus_id IS NULL AND actif =:actif";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'actif' => 1,
        ));
        $processus = $prep->fetchAll();
        $newRang = 1;
        foreach ($processus as $process ) {
            $newRang = intval($process->maxrang) + 1;
        }
        return $newRang;
    }

    public function getNewRangProcess($processusId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT max(rang) as maxrang FROM processus WHERE processus_id=:processusId AND actif =:actif";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'processusId' => $processusId,
            'actif' => 1,
        ));
        $processus = $prep->fetchAll();
        $newRang = 1;
        foreach ($processus as $process ) {
            $newRang = intval($process->maxrang) + 1;
        }
        return $newRang;
    }

    public function edit($processusId, $rang, $nom)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $newRang= $rang;
        if (intval($newRang) == 0) {
            $query = "SELECT max(rang) as maxrang FROM processus WHERE processus_id IS NULL AND actif =:actif";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'actif' => 1,
            ));
            $processus = $prep->fetchAll();
            $newRang = 1;
            foreach ($processus as $process) {
                $newRang =  $process->maxrang + 1;
            }
        }


        $query = "UPDATE processus SET nom =:nom, rang=:rang WHERE id=:processusId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'nom' => $nom,
            'rang' => $newRang,

            'processusId' => $processusId,
        ));
        return true;
    }

    /*Modification process*/
    public function editProcess($processusId, $processId, $rang, $nom, $unite, $temps, $processAnt, $processPost, $description)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $newRang = $rang;
        if (intval($newRang) == 0) {
            $query = "SELECT max(rang) as maxrang FROM processus WHERE processus_id=:processusId";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'processusId' => $processusId,

            ));
            $processus = $prep->fetchAll();
            $newRang = 1;
            foreach ($processus as $process) {
                $newRang =  $process->maxrang + 1;
            }
        }
        if ($unite == 0)
            $unite = null;
        if ($processAnt == 0)
            $processAnt = null;
        if ($processPost == 0)
            $processPost = null;

        $query = "UPDATE processus SET nom =:nom, rang=:rang, processus_id=:processusId,
                    unite_oeuvre_id=:unite,
                    temps_trait=:temps,
                    process_ant_id=:processAnt,
                    process_post_id=:processPost,
                    description=:description
                    WHERE id=:processId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'nom' => $nom,
            'rang' => $newRang,
            'processusId' => $processusId,
            'unite' => $unite,
            'temps' => $temps,
            'processAnt' => (intval($processAnt)==0)?null:intval($processAnt),
            'processPost' => (intval($processPost)==0)?null:intval($processPost),
            'description' => $description,
            'processId' =>$processId,
        ));
        return true;
    }




    public function save($rang, $nom)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if ($rang == 0) {
            $query = "SELECT max(rang) as maxrang FROM processus WHERE processus_id IS NULL AND actif =:actif";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'actif' => 1,
            ));
            $processus = $prep->fetchAll();
            $rang = 1;
            foreach ($processus as $process) {
                $rang =  $process->maxrang + 1;
            }
        }
        $query = "INSERT INTO processus (nom, rang, actif) VALUES(:nom, :rang, :actif)";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'nom' => $nom,
            'rang' => $rang,
            'actif' => 1,
        ));

        return true;
    }

    public function saveProcess($processusId, $rang, $nom, $unite, $temps, $processAnt, $processPost, $description)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if ($rang == 0) {
            $query = "SELECT max(rang) as maxrang FROM processus WHERE processus_id =:processusId AND actif =:actif";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'processusId' => $processusId,
                'actif' => 1,
            ));
            $processus = $prep->fetchAll();
            $rang = 1;
            foreach ($processus as $process) {
                $rang =  $process->maxrang + 1;
            }
        }
        echo $processPost;
        $query = "INSERT INTO processus (nom, rang, processus_id, unite_oeuvre_id, temps_trait, process_ant_id, process_post_id, description, actif)
                VALUES(:nom, :rang, :processusId, :unite,:temps,:processAnt, :processPost, :description, :actif)";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'nom' => $nom,
            'rang' => $rang,
            'processusId' => $processusId,
            'unite' => $unite,
            'temps' => $temps,
            'processAnt' => (intval($processAnt)==0)?null:intval($processAnt),
            'processPost' => (intval($processPost)==0)?null:intval($processPost),
            'description' => $description,
            'actif' => 1,
        ));

        return true;
    }
}