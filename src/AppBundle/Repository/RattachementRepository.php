<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 12/02/2019
 * Time: 17:00
 */

namespace AppBundle\Repository;

use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class RattachementRepository extends EntityRepository
{

    public function getManagerSup($operateurRattache)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT ra.operateur_id, op.prenom, op.nom FROM rattachement ra JOIN operateur op ON 
                  ra.operateur_id=op.id WHERE operateur_rat_id=:op_rattache";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'op_rattache' => $operateurRattache,
        ));
        $managerSuper = $prep->fetchAll();
        $array = array('id'=>0, 'prenom'=>'', 'nom'=>'');

        if (count($managerSuper)>0)
        {
            foreach ($managerSuper as $manage )
            {
                $array = array('id'=>$manage->operateur_id, 'prenom'=>$manage->prenom, 'nom'=>$manage->nom);
            }
        }

        return $array;
    }

    public function saveRattachement($operateurRattache, $op_manage)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if ($op_manage > 0) {
            $query = "SELECT id FROM rattachement WHERE operateur_rat_id=:op_rattache";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'op_rattache' => $operateurRattache,
            ));
            $read = $prep->fetchAll();
            if (count($read)) {
                //Mettre à jour le rattachement
                $query = "UPDATE rattachement SET operateur_id=:op_manage WHERE operateur_rat_id=:op_a_rattacher";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'op_manage' => $op_manage,
                    'op_a_rattacher' => $operateurRattache,
                ));
            } else {
                $query = "INSERT INTO rattachement (operateur_id, operateur_rat_id) VALUES(:op_manage,:op_rattache)";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'op_manage' => $op_manage,
                    'op_rattache' => $operateurRattache,
                ));
            }
        }
        else
        {
            $query = "DELETE FROM rattachement  WHERE operateur_rat_id=:op_rattache";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'op_rattache' => $operateurRattache,
            ));
        }
        return true;
    }
}