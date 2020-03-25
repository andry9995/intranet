<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 06/09/2018
 * Time: 17:15
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Saisie1;
use Doctrine\ORM\EntityRepository;

class ImputationRepository extends EntityRepository
{
    public function SaisieToImputation(Saisie1 $saisie1){
        $imputations = $this->findBy(array('image' => $saisie1->getImage()));

    }

}