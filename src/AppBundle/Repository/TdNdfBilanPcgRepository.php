<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 04/09/2018
 * Time: 09:38
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\FormeJuridique;
use Doctrine\ORM\EntityRepository;

class TdNdfBilanPcgRepository extends EntityRepository
{
    /**
     * @param $rapprochement
     * @param Dossier $dossier
     * @return int
     */
    public function getTypeCompte($rapprochement, Dossier $dossier){

        if($rapprochement === true){
            $type = 2;
        }
        else{
            $saisieBanque = false;
            $banqueComptes = $this->getEntityManager()
                ->getRepository('AppBundle:BanqueCompte')
                ->findBy(array('dossier' => $dossier));
            //Jerena raha tokony hisy saisie ny any @ banque-n'ilay dossier
            foreach ($banqueComptes as $banqueCompte){
                $banque = $banqueCompte->getBanque();

                if($banque->getCarteReleve() === 1 || $banque->getCarteReleve() === 1){
                    $saisieBanque = true;
                    break;
                }
            }
            //Atao en Attente ilay Compte raha tokony hisy saisie ny any @banque
            if($saisieBanque){
                $type = 3;
            }
            //Jerena raha individuelle ny forme juridique
            else {
                $ei = false;
                if ($dossier->getFormeJuridique() !== null) {
                    /** @var FormeJuridique $fj */
                    $fj = $dossier->getFormeJuridique();
                    if ($fj !== null) {
                        if ($fj->getCode() === 'CODE_ENTREPRISE_INDIVIDUELLE') {
                            $ei = true;
                        }
                    }
                }

                if ($ei) {
                    $type = 1;
                } else {
                    $type = 0;
                }
            }
        }

        return $type;

    }

}