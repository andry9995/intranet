<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 31/08/2018
 * Time: 11:19
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class ConditionDepenseRepository extends EntityRepository
{
    public function getConditionDepenseBy($nbreCouvert = 1, $trajet = 0){
        //Nombre couvert 1 => salarié seul
        if($nbreCouvert === 1){
            return $this->find(3);
        }
        else{
            //Trajet < 40km => reception
            if($trajet < 40){
                return $this->find(2);
            }
            //Trajet > 40km => Deplacement
            else{
                return $this->find(1);
            }
        }
    }

}