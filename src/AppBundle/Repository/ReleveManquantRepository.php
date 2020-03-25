<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 11/04/2017
 * Time: 11:12
 */

namespace AppBundle\Repository;

use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\ReleveComplet;
use AppBundle\Entity\ReleveManquant;
use AppBundle\Entity\TbimagePeriode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;

class ReleveManquantRepository extends EntityRepository
{
    function InitializeInfoReleves($resReleves,Dossier $dossier, $exercice)
    {

        $infoReleves = array();

        //Manala ny doublon rehetra
        for ($i = 0; $i < count($resReleves) - 1; $i++) {

            $tempI = $resReleves[$i];
            $trouveDoublon = false;

            for ($j = $i + 1, $jMax = count($resReleves); $j < $jMax; $j++) {

                $tempJ = $resReleves[$j];

                if ($tempI->periode_deb == $tempJ->periode_deb && $tempI->periode_fin == $tempJ->periode_fin &&
                    $tempI->solde_deb == $tempJ->solde_deb && $tempI->solde_fin == $tempJ->solde_fin
                ) {
                    $trouveDoublon = true;
                    break;
                }
            }

            if ($trouveDoublon == false) {
                $infoReleves[] = $tempI;
            }
        }

        if (count($resReleves) > 0) {
            $infoReleves[] = $resReleves[count($resReleves) - 1];
        }


        $listeManquant = array();


        //Mijery ny releve manquant
        for ($i = 0; $i < count($infoReleves) - 1; $i++) {

            $ligneI = $infoReleves[$i];

            $soldeFinI = round($ligneI->solde_fin, 2, PHP_ROUND_HALF_DOWN);
            $soldeDebI = round($ligneI->solde_deb, 2, PHP_ROUND_HALF_DOWN);

            $periodeFinI = new \DateTime($ligneI->periode_fin);
            $periodeDebI = new \DateTime($ligneI->periode_deb);

            //Jerena raha 0 ny solde fin i && 0 ny solde debut i+1 && datefin i == datedebut i+1
            $ligneI1 = $infoReleves[$i + 1];
            $soldeDebI1 = round($ligneI1->solde_deb, 2, PHP_ROUND_HALF_DOWN);
            $soldeFinI1 = round($ligneI1->solde_fin, 2, PHP_ROUND_HALF_DOWN);
            $periodeDebI1 = new \DateTime($ligneI1->periode_deb);

            $trouveSuivant = false;

            if ($ligneI1->image_id_precedent == 0) {

                if (!($soldeDebI1 == 0 && $soldeFinI1 == 0)) {

                    //Raha mitovy ny periode debut i & i+1 & sode debut i+1 = solde fin i
                    if ($soldeDebI1 == 0 && $soldeFinI == 0 && $periodeDebI->diff($periodeDebI1)->days == 0) {

                        $ligneI->image_id_suivant = $ligneI1->image_id;
                        $ligneI1->image_id_precedent = $ligneI->image_id;

                        $trouveSuivant = true;
                    } //Raha 0 ny solde debut & fin an'ny i sy ny solde debut an'ny i+1
                    else if ($soldeDebI1 == 0 && $soldeFinI == 0 && $soldeDebI == 0 && $periodeFinI->diff($periodeDebI1)->days <= 15) {
                        $ligneI->image_id_suivant = $ligneI1->image_id;
                        $ligneI1->image_id_precedent = $ligneI->image_id;

                        $trouveSuivant = true;
                    }
                }
            }


            //Raha mahita ao @ i1 dia tsy mila miditra ao @ j & k intsony

            if ($trouveSuivant == false) {

                //Mijery ny contrepartie an'ny i
                for ($j = $i + 1, $jMax = count($infoReleves); $j < $jMax; $j++) {

                    $ligneJ = $infoReleves[$j];

                    $trouve = false;

                    $soldeDebJ = round($ligneJ->solde_deb, 2, PHP_ROUND_HALF_DOWN);
                    $soldeFinJ = round($ligneJ->solde_fin, 2, PHP_ROUND_HALF_DOWN);

//                    if($soldeFinJ == 0 && $soldeDebJ == 0){
//                        continue;
//                    }

                    if ($ligneJ->image_id_precedent == 0 || ($ligneJ->image_id_precedent != 0 && $ligneJ->solde_deb == 0)) {

                        $periodeDebJ = new \DateTime($ligneJ->periode_deb);

                        $diff = $periodeFinI->diff($periodeDebJ)->days;

                        if ($soldeDebJ == $soldeFinI && $diff <= 1) {
                            $ligneI->image_id_suivant = $ligneJ->image_id;
                            $ligneJ->image_id_precedent = $ligneI->image_id;

                            $trouve = true;

                            $trouveSuivant = true;

                        } else {
                            //Jerena raha entre an'ilay periode debut sy fin an'ny i ny periode debut an'i j
                            if ($soldeDebJ == $soldeFinI && $periodeDebJ >= $periodeFinI && $periodeDebJ <= $periodeFinI) {
                                $ligneI->image_id_suivant = $ligneJ->image_id;
                                $ligneJ->image_id_precedent = $ligneI->image_id;

                                $trouve = true;

                                $trouveSuivant = true;

                            } //Raha tsy anaty période dia date à verifier
                            else {
//                            if ($soldeDebJ != 0 && $soldeDebJ == $soldeFinI && ($diff > 1 || $diff < -1)) {
                                if ($soldeDebJ == $soldeFinI && abs($diff) > 1) {

                                    $ligneI->image_id_suivant = $ligneJ->image_id;
                                    $ligneJ->image_id_precedent = $ligneI->image_id;

                                    if (!($soldeDebJ == $soldeFinJ && $soldeDebJ == 0)) {
                                        $infoReleves[$j]->controle = 'Date à verifier';
                                    }

                                    $trouve = true;

                                    $trouveSuivant = true;
                                }
                            }
                        }

                        if ($trouve == true) {

                            if ($soldeFinI != 0) {
                                //Relevé Intermediaire
                                for ($k = $i + 1; $k < $j; $k++) {

                                    $ligneK = $infoReleves[$k];
                                    $periodeDebK = new \DateTime($ligneK->periode_deb);

//                                    if ($periodeDebK != $periodeDebI)
                                    {

                                        //Mbola tokony ho verifier-na ny condition faha2 '||'
                                        if ($ligneK->image_id_precedent == 0) {
                                            if (($periodeDebK >= $periodeDebI && $periodeDebK <= $periodeFinI)
                                                || ($periodeDebK >= $periodeFinI && $periodeDebK <= $periodeDebJ)
                                            ) {
                                                $ligneK->releve_intermediaire = 1;

//                                                $ligneK->controle = 'Relevé intermediaire';
                                            }

                                        } elseif ($ligneK->image_id_suivant == 0) {
                                            if (($periodeDebK >= $periodeDebI && $periodeDebK <= $periodeFinI)
                                                || ($periodeDebK >= $periodeFinI && $periodeDebK <= $periodeDebJ)
                                            ) {
                                                $ligneK->releve_intermediaire = 1;

//                                                $ligneK->controle = 'Relevé intermediaire';
                                            }
                                        }
                                    }
                                }
                            }

                            break;
                        }
                    }
                }
            }

            if ($trouveSuivant == false) {

                if ($ligneI->releve_intermediaire == 0) {

//                    $ligneI->controle = 'Relevé Manquant';

                    if (!in_array($i, $listeManquant)) {
                        $listeManquant[] = $i;
                    }
//                    if(($ligneI->solde_deb == 0 && $ligneI->solde_fin == 0)) {
//
//                        if($i>0)
//                        {
//                            if($infoReleves[$i+1]->image_id_precedent == 0)
//                            {
//
//                            }
//                        }
//
//                    }
                }

            } else {
                if ($ligneI->image_id_precedent == 0 && $ligneI->releve_intermediaire == 0) {
                    if ($i > 0) {

                        if ($infoReleves[$i - 1]->image_id_suivant == 0) {

                            if ($infoReleves[$i - 1]->solde_deb == 0 && $infoReleves[$i - 1]->solde_fin == 0 &&
                                $infoReleves[$i]->solde_deb != 0
                            ) {

//                                $infoReleves[$i - 1]->controle = 'Relevé Manquant';


                                if (!in_array($i - 1, $listeManquant)) {
                                    $listeManquant[] = $i - 1;
                                }
                            }
                        } else {
                            if ($ligneI->image_id_suivant == 0) {
//                                $infoReleves[$i]->controle = 'Relevé Manquant';

                                if (!in_array($i, $listeManquant)) {
                                    $listeManquant[] = $i;
                                }

                            } //Cas mitranga rehefa misy 0 solde debut & fin
                            else if ($ligneI->image_id_precedent == 0) {
//                                $infoReleves[$i - 1]->controle = 'Relevé Manquant';


                                if (!in_array($i - 1, $listeManquant)) {
                                    $listeManquant[] = $i - 1;
                                }
                            }
                        }
                    }
                }
            }
        }


        //Ajout ligne ho an'ny releve Manquant
        $res = array();

        $j = 0;
        while ($j < count($infoReleves)) {
            if (!in_array($j, $listeManquant)) {
                $res[] = $infoReleves[$j];

            } else {

                $res[] = $infoReleves[$j];

                $res[] = (object)array(

                    'banque_nom' => '',
                    'numcompte' => '',
                    'periode_deb' => '',
                    'periode_fin' => '',
                    'num_releve' => '',
                    'solde_deb' => '',//0.01,
                    'solde_fin' => '',//0.01,
                    'controle' => 'Relevé Manquant',
                    'date_scan' => null,
                    'image_id' => -1,
                    'image_nom' => ''
                );
            }


            $j++;


        }


        $infoReleves = $res;
        //Verifier-na raha tsy misy image eo alohan'ny' releve debut_periode
        $infoRelevesDebut = array();

        if (count($infoReleves) > 0) {

            /** @var TbimagePeriode[] $tbImagePeriodes */
            $tbImagePeriodes = $this->getEntityManager()
                ->getRepository('AppBundle:TbimagePeriode')
                ->findBy(array('dossier' => $dossier));

            $demarrageTb = false;
            $demarrage = null;
            $premiereCloture = null;

            if (count($tbImagePeriodes) > 0) {
                $tbImagePeriode = $tbImagePeriodes[0];

                if($dossier->getDebutActivite() === null)
                    $demarrage = $tbImagePeriode->getDemarrage();
                else
                    $demarrage = $dossier->getDebutActivite();

                /** @var \DateTime $premiereCloture */
                $premiereCloture = $tbImagePeriode->getPremiereCloture();

                if (null !== $demarrage && null !== $premiereCloture) {

                    if(null !== $infoReleves[0]->periode_deb){
                        $firstReleveDebutDate = new \DateTime($infoReleves[0]->periode_deb);
                        if ((int)$firstReleveDebutDate->format('Y') <= $premiereCloture->format('Y')) {
                            $demarrageTb = true;
                        }
                    }
//
//                    else if ($exercice === $premiereCloture->format('Y')) {
//                        $demarrageTb = true;
//                    }
                }
            }
//            else{
//                $demarrage = $dossier->getDebutActivite();
//            }


            $clotureDate = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossier, $exercice);

            if (!$demarrageTb) {

                $debutActivite = $dossier->getDebutActivite();
                $useDebutAct = false;
                if(null !== $debutActivite){
                    $anneeDebAct = $debutActivite->format('Y');

                    if($anneeDebAct == $exercice){
                        $debutDate = $debutActivite;
                        $useDebutAct = true;
                    }
                }
                if(!$useDebutAct)
                    $debutDate = $clotureDate->modify('-11 months');

            } else {
                $debutDate = $demarrage;
            }

            $debutYear = $debutDate->format('Y');
            $debutMonth = $debutDate->format('m');


            $infoReleve = $infoReleves[0];

            if (null !== $infoReleve->periode_deb) {

                $firstReleveDebutDate = new \DateTime($infoReleve->periode_deb);

                $complete = true;
                if($premiereCloture !== null){
                    if($premiereCloture <= $firstReleveDebutDate){
                        $complete = false;
                    }
                }
                if($complete) {

                    if ($firstReleveDebutDate > $debutDate) {

                        $firstReleveMonth = $firstReleveDebutDate->format('m');
                        $firstReleveDay = $firstReleveDebutDate->format('d');

                        $diffYear = (int)$firstReleveDebutDate->format('Y') - (int)$debutYear;
                        $diff = ($diffYear * 12) + ((int)$firstReleveMonth - (int)$debutMonth);

                        $firstReleveMonth = (int)$debutMonth;

                        if ((int)$firstReleveDay > 15) {
                            $diff++;
                        }

                        for ($i = 0; $i < $diff; $i++) {

                            if ($firstReleveMonth < 10) {
                                $firstReleveMonth = "0" . $firstReleveMonth;
                            }

                            if ($firstReleveMonth == 13) {
                                $firstReleveMonth = 1;
                                $debutYear = $debutYear + 1;
                            }

                            $periodeDeb = $debutYear . '-' . $firstReleveMonth . '-01';

                            $info = (object)array(
//                            'banque_nom' => $banqueNom,
//                            'numcompte' => $numCompte,
//                            'periode_deb' => $periodeDeb,
                                'banque_nom' => '',
                                'numcompte' => '',
                                'periode_deb' => $periodeDeb,
                                'periode_fin' => null,
                                'num_releve' => '',
                                'solde_deb' => '',//0.01,
                                'solde_fin' => '',//0.01,
                                'controle' => 'Relevé Manquant',
                                'date_scan' => null,
                                'image_id' => -1,
                                'image_nom' => ''
                            );

                            $infoRelevesDebut[] = $info;

                            $firstReleveMonth++;

                        }
                    }
                }
            }
        }

        //Atambatra ny releve debut & inforeleve
        $res = array_merge($infoRelevesDebut, $infoReleves);
        $infoReleves = $res;

        //Verification raha tsy misy images eo anelanelan'ny mois actuel sy ny mois cloture
        if (count($infoReleves) >= 1) {

            $clotureDate = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossier, $exercice);

            $lastReleveFinDate = null;


            if (null !== $infoReleves[count($infoReleves) - 1]->periode_fin && null !== $infoReleves[count($infoReleves) - 1]->periode_deb) {

                $lastReleveFinDate = new \DateTime($infoReleves[count($infoReleves) - 1]->periode_fin);
                $lastReleveDebutDate = new \DateTime($infoReleves[count($infoReleves) - 1]->periode_deb);

                if($lastReleveFinDate >= $lastReleveDebutDate) {

                    if ($lastReleveFinDate < $clotureDate && $lastReleveDebutDate < $clotureDate) {

                        $currentDate = new \DateTime('now');
                        $currentYear = $currentDate->format('Y');
                        $currentMonth = $currentDate->format('m');

                        $clotureMonth = (int)$clotureDate->format('m');

                        if((int)$lastReleveFinDate->format('d') > 15 && ((int)$lastReleveFinDate->format('m') < $clotureMonth)) {
                            $lastRelevemonth = (int)$lastReleveFinDate->format('m') + 1;
                        }
                        else{
                            $lastRelevemonth = (int)$lastReleveFinDate->format('m');
                        }

                        $lastReleveYear = $lastReleveFinDate->format('Y');



                        $anneeSuivante = false;


                        $diffYear = (int)$clotureDate->format('Y') - (int)$lastReleveYear;
                        $diffMonth = ($diffYear * 12) + (int)$clotureDate->format('m') - (int)$lastReleveFinDate->format('m');


                        if($diffMonth > 0) {

                            for ($i = 0; $i <= $diffMonth; $i++) {

                                if ($lastRelevemonth < 10) {
                                    $lastRelevemonth = "0" . $lastRelevemonth;
                                }

                                if ($lastRelevemonth == 13) {
                                    $lastRelevemonth = 1;
                                    $lastReleveYear = (int)$lastReleveYear + 1;
                                    $anneeSuivante = true;
                                }

                                if ($anneeSuivante) {
                                    if ($lastRelevemonth - 1 == $clotureMonth) {
                                        break;
                                    }
                                }

                                if ($lastReleveYear == $currentYear) {
                                    if ($lastRelevemonth - 1 == $currentMonth) {
                                        break;
                                    }
                                }

                                if ($lastReleveYear > (int)$clotureDate->format('Y')) {
                                    break;
                                }


                                $periodeDeb = $lastReleveYear . '-' . $lastRelevemonth . '-01';

                                $info = (object)array(
//                            'banque_nom' => $banqueNom,
//                            'numcompte' => $numCompte,
//                            'periode_deb' => $periodeDeb,
                                    'banque_nom' => '',
                                    'numcompte' => '',
                                    'periode_deb' => $periodeDeb,
                                    'periode_fin' => null,
                                    'num_releve' => '',
                                    'solde_deb' => '',//0.01,
                                    'solde_fin' => '',//0.01,
                                    'controle' => 'Relevé Manquant',
                                    'date_scan' => null,
                                    'image_id' => -1,
                                    'image_nom' => ''
                                );

                                $infoReleves[] = $info;

                                $lastRelevemonth++;
                            }
                        }
                    }

                }
                else{
                    $infoReleves[count($infoReleves) - 1]->controle = 'Relevé Manquant';
                }
            }

        }


        return $infoReleves;
    }

    function InitializePasImageInfoReleves($dossier, $exercice)
    {
        $infoReleves = array();

        $clotureDate = $this->getEntityManager()
            ->getRepository('AppBundle:Dossier')
            ->getDateCloture($dossier, $exercice);


        $currentDate = new \DateTime('now');

        $lastReleveMonth = 0;

        $currentMonth = (int)$currentDate->format('m');
        $currentYear = (int)$currentDate->format('Y');
        $clotureMonth = (int)$clotureDate->format('m');


        //Raha now > exerice => katreo @ date cloture no fenoina relevé manquant
        $month = $lastReleveMonth + 1;

        if ($currentYear > $exercice) {
//            if ($lastReleveMonth < $clotureMonth) {
//                for ($i = 0; $i < ($clotureMonth - $lastReleveMonth); $i++) {
//
//                    if($month < 10){
//                        $month = "0".$month;
//                    }
//
//                    $periodeDeb = $exercice . '-' . $month . '-01';
//                    $info = (object)array(
////                        'banque_nom' => $banqueNom,
////                        'numcompte' => $numCompte,
////                        'periode_deb' => $periodeDeb,
//                        'banque_nom' => '',
//                        'numcompte' => '',
//                        'periode_deb' => $periodeDeb,
//                        'periode_fin' => null,
//                        'num_releve' => '',
//                        'solde_deb' => '',//0.01,
//                        'solde_fin' => '',//0.01,
//                        'controle' => 'Relevé Manquant',
//                        'date_scan' => null,
//                        'image_id' => -1,
//                        'image_nom' => ''
//                    );
//
//                    $infoReleves[] = $info;
//                    $month++;
//                }


            $month = $clotureMonth;

            if($month == 12){
                $month = 1;
                $exerciceManquant = $exercice;
            }
            else {
                $exerciceManquant = $exercice - 1;
            }

            for ($i = 0; $i < 12; $i++) {


                if($month > 12){
                    $exerciceManquant = $exerciceManquant + 1;
                    $month = 1;
                }



                if($month < 10){
                    $month = "0".$month;
                }

                $periodeDeb = $exerciceManquant . '-' . $month . '-01';
                $info = (object)array(
//                        'banque_nom' => $banqueNom,
//                        'numcompte' => $numCompte,
//                        'periode_deb' => $periodeDeb,
                    'banque_nom' => '',
                    'numcompte' => '',
                    'periode_deb' => $periodeDeb,
                    'periode_fin' => null,
                    'num_releve' => '',
                    'solde_deb' => '',//0.01,
                    'solde_fin' => '',//0.01,
                    'controle' => 'Relevé Manquant',
                    'date_scan' => null,
                    'image_id' => -1,
                    'image_nom' => ''
                );

                $infoReleves[] = $info;
                $month++;
            }
//            }
        } //Raha now = exercice => katreo @ mois actuel no fenoina relevé manquant
        elseif ($currentYear == $exercice) {
//            if ($lastReleveMonth < $currentMonth) {
//                for ($i = 0; $i < ($currentMonth - $lastReleveMonth); $i++) {
//
//                    if($month <10){
//                        $month = "0".$month;
//                    }
//
//                    $periodeDeb = $currentYear . '-' . $month . '-01';
//                    $info = (object)array(
////                        'banque_nom' => $banqueNom,
////                        'numcompte' => $numCompte,
//                        'banque_nom' => '',
//                        'numcompte' => '',
//                        'periode_deb' => $periodeDeb,
//                        'periode_fin' => null,
//                        'num_releve' => '',
//                        'solde_deb' => '',//0.01,
//                        'solde_fin' => '',//0.01,
//                        'controle' => 'Relevé Manquant',
//                        'date_scan' => null,
//                        'image_id' => -1,
//                        'image_nom' => ''
//                    );
//
//                    $infoReleves[] = $info;
//                    $month++;
//                }


            $month = $clotureMonth +1;
            $exerciceManquant = $exercice - 1;

            if($month == 12){
                $month = 1;
                $exerciceManquant = $exercice;
                $difference = $currentMonth -1;
            }
            else{
                $difference = (12-$month) + $currentMonth;
            }

            //Calcul difference


//                for ($i = 0; $i < ($currentMonth - $lastReleveMonth); $i++) {

            $nbMois = 0;
            for ($i = 0; $i < $difference; $i++){

                if($month > 12){
                    $exerciceManquant = $exerciceManquant + 1;
                    $month = 1;
                }

                if($month <10){
                    $month = "0".$month;
                }

                $periodeDeb = $exerciceManquant . '-' . $month . '-01';
                $info = (object)array(
//                        'banque_nom' => $banqueNom,
//                        'numcompte' => $numCompte,
                    'banque_nom' => '',
                    'numcompte' => '',
                    'periode_deb' => $periodeDeb,
                    'periode_fin' => null,
                    'num_releve' => '',
                    'solde_deb' => '',//0.01,
                    'solde_fin' => '',//0.01,
                    'controle' => 'Relevé Manquant',
                    'date_scan' => null,
                    'image_id' => -1,
                    'image_nom' => ''
                );

                $infoReleves[] = $info;
                $month++;

                $nbMois++;

                if($nbMois >= 12){
                    break;
                }
            }



//            }
        }

        return $infoReleves;

    }

    function SaveRelevesManquant($exercice,Dossier $dossier,BanqueCompte $banqueCompte, $moisList, $statusReleve,$em){
        //SAVE
        /** @var  $releveManquant ReleveManquant */
        $resReleveManquant = $this->getEntityManager()
            ->getRepository('AppBundle:ReleveManquant')
            ->findBy(array('exercice' => $exercice, 'dossier' => $dossier, 'banqueCompte' => $banqueCompte));

        //Insertion na mise à jour any @ base de données
        if (count($moisList) > 0) {

            //Raha mbola tsy misy dia insertion
            if (count($resReleveManquant) == 0) {
                $releveManquant = new ReleveManquant();

                $releveManquant->setDossier($dossier);
                $releveManquant->setBanqueCompte($banqueCompte);
                $releveManquant->setExercice($exercice);
                $releveManquant->setMois($moisList);

                $releveManquant->setStatus($statusReleve);

                $em->persist($releveManquant);
            } //Raha efa misy dia atao mise à jour fotsiny ny mois
            else {
                $releveManquant = $resReleveManquant[0];
                $releveManquant->setMois($moisList);

                $releveManquant->setStatus($statusReleve);
            }

            $em->flush();

            $info = array('dossier' => $dossier->getId(), 'banque' => $banqueCompte->getId(),
                'exercice' => $exercice, 'mois' => $moisList);

            $listeRelevesManquant[] = $info;
        } //Fafana ao anaty table raha efa feno ilay relevé
        else if (count($resReleveManquant) > 0) {

            $releveManquant = $resReleveManquant[0];
            $em->remove($releveManquant);

            $em->flush();

        }
    }

    function SaveRelevesComplet($exercice,Dossier $dossier,BanqueCompte $banqueCompte, $moisOk,$em){
        //SAVE

        $resReleveComplet = $this->getEntityManager()
            ->getRepository('AppBundle:ReleveComplet')
            ->findBy(array('exercice' => $exercice, 'dossier' => $dossier, 'banqueCompte' => $banqueCompte));

        //Insertion na mise à jour any @ base de données
        if ($moisOk != "") {

            //Raha mbola tsy misy dia insertion
            if (count($resReleveComplet) == 0) {
                $releveComplet = new ReleveComplet();

                $releveComplet->setDossier($dossier);
                $releveComplet->setBanqueCompte($banqueCompte);
                $releveComplet->setExercice($exercice);
                $releveComplet->setMois($moisOk);

                $em->persist($releveComplet);
            } //Raha efa misy dia atao mise à jour fotsiny ny mois
            else {
                /** @var  $releveComplet ReleveComplet*/
                $releveComplet = $resReleveComplet[0];
                $releveComplet->setMois($moisOk);
            }

            $em->flush();

            $info = array('dossier' => $dossier->getId(), 'banque' => $banqueCompte->getId(),
                'exercice' => $exercice, 'mois' => $moisOk);

            $listeRelevesManquant[] = $info;
        } //Fafana ao anaty table raha efa feno ilay relevé
        else
            if (count($resReleveComplet) > 0) {

                $releveComplet = $resReleveComplet[0];
                $em->remove($releveComplet);

                $em->flush();

            }
    }

    #==========================FANJAVA==========================
	public function getRevPieces($dossier,$x,$exercice,$filtre){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT count(I.id) AS nb 
        FROM  image I, lot L,dossier D
        WHERE  I.lot_id = L.id
        AND L.dossier_id = D.id
        AND L.dossier_id =".$dossier;
        if ($exercice>0){
            $query .=" AND I.exercice=".$exercice;
        }
        $query .=" ".$filtre;
        $prep = $pdo->query($query);
        $recu =0;
        $recu =  $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($recu[0])){
            $recu = $recu[0]['nb'];
        }
        return $recu;
    }
    public function getEnInstance($dossier,$x,$exercice){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT count(I.id) AS nb 
        FROM  image I, lot L,dossier D ,panier P
        WHERE  I.lot_id = L.id
        AND L.dossier_id = D.id
        AND P.image_id = I.id
        AND P.status =1
        AND L.dossier_id =".$dossier; 
		if($exercice>0){
			$query .=" AND I.exercice=".$exercice;
		}
    
        $prep = $pdo->query($query);
        $recu =0;
        $recu =  $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($recu[0])){
            $recu = $recu[0]['nb'];
        }
        return $recu;
    } 
    public function getGenerale($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT * FROM dossier WHERE id =".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($data[0])){
            $data = $data[0];
        }
        return $data;
    }
    public function getInstructions($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT ISS.instruction as isaisie,ISD.instruction as idossier 
        FROM dossier D ,client C, site S,instruction_dossier ISD, instruction_saisie ISS
        WHERE D.site_id = S.id
        AND S.client_id = C.id
        AND ISS.dossier_id = D.id
        AND ISD.client_id = C.id
        AND D.id=".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }
    public function getRegimeFiscal($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT F.libelle
        FROM dossier D ,regime_fiscal F
        WHERE D.regime_fiscal_id = F.id
        AND D.id=".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $fisc="";
        if (isset($data[0])){
            $fisc = $data[0]['libelle'];
        }
        return $fisc;
    }
    public function getRegimeImposition($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT I.libelle
        FROM dossier D ,regime_imposition I
        WHERE D.regime_imposition_id = I.id
        AND D.id=".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $fisc="";
        if (isset($data[0])){
            $fisc = $data[0]['libelle'];
        }
        return $fisc;
    }
    public function getRegimeTva($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT T.libelle
        FROM dossier D ,regime_tva T
        WHERE D.regime_tva_id = T.id
        AND D.id=".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $fisc="";
        if (isset($data[0])){
            $fisc = $data[0]['libelle'];
        }
        return $fisc;
    }
    public function getTypeTva($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT T.libelle
        FROM dossier D ,tva_type T
        WHERE D.tva_type_id = T.id
        AND D.id=".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $fisc="";
        if (isset($data[0])){
            $fisc = $data[0]['libelle'];
        }
        return $fisc;
    }
    public function getMandataire($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT * FROM responsable_csd WHERE type_responsable=0 AND dossier_id =".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $m['nom']="";$m["prenom"]="";$m["tel_portable"]="";$m["email"]="";
        if (isset($data[0])){
            $m = $data[0];
        }
        return $m;
    }
    public function getTenue($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT * FROM methode_comptable WHERE dossier_id =".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($data[0])){
            $data = $data[0]['tenue_comptablilite'];
        }
        return $data;
    }
    public function getConvention($dossier){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT C.libelle
                  FROM methode_comptable M,convention_comptable C 
                  WHERE M.convention_comptable_id = C.id
                  AND M.dossier_id =".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $c="";
        if (isset($data[0])){
            $c = $data[0]['libelle'];
        }
        return $c;
    }
    public function getAutres($dossier,$x,$exercice){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT count(I.id) as nb
        FROM tva_saisie_controle T, image I, lot L,dossier D ,soussouscategorie as SSC
        WHERE  T.image_id = I.id 
        AND I.lot_id = L.id 
        AND L.dossier_id = D.id 
        AND SSC.id = T.soussouscategorie_id 
        AND T.soussouscategorie_id IN (5,288,287)
        AND D.id =".$dossier;
		if($exercice>0){
			$query .=" AND I.exercice=".$exercice;
		}

        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $c=0;
        if (isset($data[0])){
            $c = $data[0]['nb'];
        }
        return $c;
    }
    public function getDateEcriture($dossier,$exercice){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT date_upload
                  FROM historique_upload
                  WHERE dossier_id =".$dossier."
                  ORDER BY date_upload DESC";    
        $prep = $pdo->query($query);$d="";
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($data[0])){
            $d = $data[0]['date_upload'];
            $d = new \DateTime($d);
            $d =  $d->format('d-m-Y');
        }
        return $d;
    }
    public function getReleveManq($dossier,$exercice,$complet){
        //$dossier="9239";
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $complet = $complet->ms;    
        $query = "SELECT banque_compte_id,mois
                  FROM releve_manquant 
                  WHERE dossier_id =".$dossier."
                  AND exercice=".$exercice." 
                  AND banque_compte_id is not null";    
        $prep = $pdo->query($query);$m=array();
        $datas = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $j=0;
        foreach ($datas as $data) {      
            $man ="";
            $man.= $data['mois'];
            $man .=$this->getMoisManq($dossier,$data['banque_compte_id'],$exercice-1);
            $man .=$this->getMoisManq($dossier,$data['banque_compte_id'],$exercice+1);
            $manquant = explode(",",$man);
            $i=0;
            $mtrim = array(); 
            //netoyer les espaces
            foreach ($manquant as $v){
                $mtrim[]=trim($v);
            } 
            $s = sizeof($complet);
            foreach ($complet as $v){
                if (in_array($v,$mtrim)){
                    $i++;
                }
            }
            rsort($complet);
           //echo $i;echo sizeof($complet);print_r($complet);print_r($mtrim);
            if ($i==0){
                $m[$j]['manquant'] = "Incomplet";
            } 
            if ($i==sizeof($complet)){
                $m[$j]['manquant'] = "Abscence Totale";    
            } else {
                $k=0;$trouve=false;$trouve2=false;$trouve3=false;
                foreach($complet as $c){
                    //echo $c;echo $k;
                    if ($k==0){
                        $k++;
                        if (in_array($c,$mtrim)){ 
                            $m[$j]['manquant'] = "m-1";
                            $trouve = true;
                            continue;
                        } else {
                            $m[$j]['manquant'] = "Incomplet";
                        }
                    }
                    if ($k==1){
                        $k++;
                        if ($trouve){
                            if (in_array($c,$mtrim)){ 
                                $m[$j]['manquant'] = "m-2";
                                $trouve2=true;
                                continue;
                            }
                        }    
                    }
                    if ($k==2){
                        $k++;
                        if ($trouve && $trouve2){
                            if (in_array($c,$mtrim)){ 
                                //echo $c;print_r($mtrim);
                                $m[$j]['manquant'] = "m-3";
                                $trouve3=true;
                                continue;
                            }
                        }    
                    }
                    //echo $m[$j]['manquant'];    
                    if ($k>2){
                        $k++;
                        if ($trouve || $trouve2 || $trouve3){
                            if(in_array($c,$mtrim)){
                                $m[$j]['manquant'] = "Incomplet";
                            }
                        }    
                    }
                              
                }
            }             
            $m[$j]['banque_compte']=$this->getNomBanque($data['banque_compte_id']);
            $j++;
        }
        return $m;
    }
    public function getNomBanque($bid){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT B.Nom FROM banque B,banque_compte BC WHERE BC.banque_id = B.id AND BC.id=".$bid;  

        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $manquant ="";
        if (isset($data[0])){          
            $manquant .= $data[0]['Nom'];
        }
        return $manquant;
    }    
    public function getMoisManq($dossier,$banque,$exercice){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT mois  
                  FROM releve_manquant
                  WHERE dossier_id =".$dossier."
                  AND banque_compte_id=".$banque."
                  AND exercice=".$exercice;    
        $prep = $pdo->query($query);$m=array();
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $manquant ="";
        if (isset($data[0])){          
            $manquant .= ",".$data[0]['mois'];
        }
        return $manquant;
    }    
    function getRevDateCloture($dossier,$exercice)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT * FROM dossier WHERE id =".$dossier;    
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($data[0])){
            $mois_cloture = $data[0]['cloture'];
        }

        $mois_cloture++;
        if($mois_cloture == 13)
        {
            $mois_cloture = 1;
            $exercice++;
        }
        if($mois_cloture < 10) $mois_cloture = '0'.$mois_cloture;
        $date_temp = new \DateTime($exercice.'-'.$mois_cloture.'-01');
        return $date_temp->sub(new \DateInterval('P1D'));
    }
    public function getExercices($dossier)
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT DISTINCT I.exercice AS exec
        FROM  image I, lot L
        WHERE I.lot_id = L.id
		AND I.exercice <>0
        AND L.dossier_id =".$dossier;

        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $exercice = array();
        foreach ($data as $v){
            $exercice[]=$v['exec'];
        }
        $query = "SELECT DISTINCT exercice AS exec
        FROM  releve_manquant 
        WHERE exercice <>0
		AND dossier_id =".$dossier;

        $prep = $pdo->query($query);
        $data =  $prep->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $v){
            $exercice[]=$v['exec'];
        } 
        $exercice = array_unique($exercice);
        rsort($exercice);
        if (!sizeof ($exercice)>0){
                $exercice[] = 2018;
        }
        return $exercice;
    }
	public function getImages($dossier,$exercice,$stati)
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT  I.*,L.date_scan
					FROM  image I, lot L
					WHERE I.lot_id = L.id
					AND L.dossier_id =".$dossier."
                    AND I.exercice =".$exercice;
            if(is_numeric($stati)){                    
                $query .= " AND I.status=".$stati;
            }            

        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }
	public function getCategories()
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT id,libelle_new FROM categorie ORDER BY libelle_new";

        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }
	public function getSousCategories()
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT id,libelle_new FROM souscategorie WHERE libelle_new <>'' ORDER BY libelle_new";

        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getSousCategoriesBanque()
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT id,libelle_new FROM souscategorie WHERE libelle_new <>'' AND categorie_id = 16  AND actif =1  ORDER BY libelle_new";

        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getSoussousCategoriesBanque()
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id,libelle_new FROM soussouscategorie WHERE libelle_new <>'' 
                AND souscategorie_id IN (SELECT id FROM souscategorie WHERE categorie_id = 16) 
                ORDER BY libelle_new";
        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }
	public function getSoussousCategories()
    { 
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT id,libelle_new FROM soussouscategorie WHERE libelle_new <>'' ORDER BY libelle_new";

        $prep = $pdo->query($query);
        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }
	public function getInfosImage($iid)
    {
		$con = new CustomPdoConnection();
        $pdo = $con->connect();
		$data=array();$data['c']=0;$data['sc']=0;$data['ssc']=0;
        
		$query = "SELECT categorie_id as id FROM separation WHERE image_id =".$iid;
		$prep = $pdo->query($query);
        $c = $prep->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($c[0])){
			if($c[0]['id']>0){
				$data['c']=$c[0]['id'];	
			} 
		}
		
		$query = "SELECT souscategorie_id as id FROM separation WHERE image_id =".$iid;
		$prep = $pdo->query($query);
        $c = $prep->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($c[0])){
			if($c[0]['id']>0){
				$data['sc']=$c[0]['id'];	
			} 
		}
		
		$query = "SELECT soussouscategorie_id as id FROM separation WHERE image_id =".$iid;
		$prep = $pdo->query($query);
        $c = $prep->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($c[0])){
			if($c[0]['id']>0){
				$data['ssc']=$c[0]['id'];	
			} 
		}
		return $data;
	}
	public function sMaj($id,$cat,$champ)
    {   
		$con = new CustomPdoConnection();
        $pdo = $con->connect();
		if ($cat==0){$cat='NULL';}
		$query = 'UPDATE separation SET '.$champ.'='.$cat.' WHERE image_id='.$id;
        return  $pdo->exec($query);
	}	
	public function getResponsable($dossier){
		$con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT O.nom,O.prenom
					FROM client C, site S, dossier D,responsable_client R,operateur as O
					WHERE D.site_id=S.id
					AND S.client_id=C.id
					AND C.id = R.client
					AND O.id = R.responsable
					AND D.id =".$dossier;

        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($data[0])){
			$data = $data[0]['prenom'];//." ".$data[0]['nom'];
		} else {
			$data="";
		}
		return $data;
	}
	public function getPriorite($dossier){
		$con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT MIN(priorite) as pri
					FROM lot
					WHERE dossier_id =".$dossier;

        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($data[0])){
			if ($data[0]['pri']>0){
				$data = $data[0]['pri'];
			} else {
				$data="100";	
			}
		} else {
			$data="100";
		}
		if ($data=="100"){
			$data ='<span class="badge badge-primary prior" data-toggle="modal" data-target="#myModal" data-id="'.$dossier.'">NORM</span>';
		}
		if ($data=="1"){
			$data ='<span class="badge badge-warning prior" data-toggle="modal" data-target="#myModal" data-id="'.$dossier.'">URG</span>';
		}
		if ($data=="2"){
			$data ='<span class="badge badge-danger prior" data-toggle="modal" data-target="#myModal" data-id="'.$dossier.'">T. URG</span>';
		}
		return $data;
	}
	public function getLots($dossier,$exercice){
		$con = new CustomPdoConnection();
        $pdo = $con->connect();	
		$data['resp'] = $this->getResponsable($dossier);
		$query = "SELECT L.*,count(I.id) as nbimage FROM lot L,image I
				  WHERE I.lot_id = L.Id
				  AND L.dossier_id =".$dossier."
				  AND I.exercice =".$exercice."
				  GROUP BY L.id		
				 ORDER BY date_scan DESC";
        $prep = $pdo->query($query);
		$data['lots'] = $prep->fetchAll(\PDO::FETCH_ASSOC);
		return $data;
    }
    public function getDossierImage($imagid){
		$con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT D.id 
                    FROM  image I, lot L,dossier D         
                    WHERE  I.lot_id = L.id         
                    AND L.dossier_id = D.id         
                    AND I.id =".$imagid;

        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($data[0])){
			$data = $data[0]['id'];
		} else {
			$data=0;
		}
		return $data;
	}	
}