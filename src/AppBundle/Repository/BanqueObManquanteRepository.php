<?php

/**
 * BanqueObManquanteRepository
 *
 * @package Intranet
 *
 * @author Scriptura
 * @copyright Scriptura (c) 2019
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;
use Symfony\Component\Console\Helper\ProgressBar;

class BanqueObManquanteRepository extends EntityRepository
{

    /**
     * @var \PDO
     *
     */
    public $pdo = null;

    /**
     * @var array
     *
     */
    public $exerciceList = array();

    /**
     * Contructeur
     *
     * Création de l'instance de l'objet PDO
     */
    public function __construct()
    {
        $con       = new CustomPdoConnection();
        $this->pdo = $con->connect();
    }

    /**
     * Initialisation du liste des exercices à traiter
     *
     */
    public function initExoList()
    {
        $currentYear = date("Y");
        array_push($this->exerciceList, $currentYear - 1);
        array_push($this->exerciceList, $currentYear);
        array_push($this->exerciceList, $currentYear + 1);
    }

    /**
     * Suppression des données dans la table banque_ob_manquante
     */
    public function clearData()
    {
        $query = "DELETE FROM banque_ob_manquante";
        $prep = $this->pdo->prepare($query);
        $prep->execute();
    }
    
    /**
     * Traitement des opérations bancaires manquantes
     */
    public function cronJob($output)
    {

        $this->clearData();
        $this->initExoList();

        $query = "  select d.id, d.cloture, d.nom
                    from dossier d
                    where d.status = 1 ";

        $dossiers = $this->fetch($query);
        
        /**
         * @var ProgressBar
         *
         * Permet d'afficher la progression du cron sur console
         */
        $progressBar = new ProgressBar($output, count($dossiers));

        $output->writeln('cron start ' . date("H:i:s"));
        $output->writeln('cron in progress ...');

        $progressBar->start();

        // Liste des dossiers
        foreach ($dossiers as $dossier) {

            $query = "  select *
                        from banque_compte bc
                        where bc.dossier_id = " . $dossier->id ;

            // Liste des banques pour un dossier
            $banque_comptes = $this->fetch($query);
            foreach ($banque_comptes as $key => $bc) {
                
                foreach ($this->exerciceList as $key => $exercice) {

                    $beginEnd     = $this->beginEnd($dossier->cloture, $exercice);

                    // Année courant
                    if ($exercice == date("Y")) {
                        // Mois précedant
                        $end = ((date("m") - 1) < 10) ? ( '0' . (date("m") - 1)) : (date("m") - 1);
                        $beginEnd['end'] = date("Y") . '-' . ($end) . '-01';
                    }

                    $months       = $this->getBetweenDate($beginEnd);
                    $obm          = $this->getOb($exercice, $dossier->id, $bc->id);
                    
                    $this->saveMissingMonth($obm,$months,$dossier->id, $exercice, $bc->id);

                }
            }

            $progressBar->advance();
            
        }

        $progressBar->finish();

        $output->writeln( "\n" . "cron job success ". date("H:i:s") . "!");

    }

    /**
     * Redéfinir le clés d'une array
     *
     * @param array $array
     *
     * @return array
     */
    public function resetKey($array)
    {
        /**
         * @var array
         *
         * Nouvelle tableau avec clés redéfini
         */
        $new = array();

        foreach ($array as $key => $value) {
            array_push($new, $value);
        }

        return $new;
    }

    public function pasSaisir($dossier_id,$souscategorie_id)
    {

        $query = "  select scps.id
                    from souscategorie_pas_saisir scps
                    where dossier_id = :dossier_id
                    and souscategorie = :souscategorie_id";

        $param = array(
            'dossier_id'       => $dossier_id,
            'souscategorie_id' => $souscategorie_id
        );

        $resultat = $this->fetch($query,$param);

        if (count($resultat) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Insertion ou mie à jour dan la table banque_ob_manquante apres véridication
     *
     * @param array $obm
     * @param array $months
     * @param integer $exercice
     */
    public function saveMissingMonth($obm, $months, $dossier, $exercice, $bc)
    {

        if (!empty($obm)) {
            $missingLCR  = $missingRemise = $missingFrais = $missingVrt  = $lcr = $remise =  $frais = $vrt = array();
            $lcrExist    = $remiseExist   = $fraisExist   = $vrtExist    = false;
            $nbPMLCR     = $nbPMRemise    = $nbPMFrais    = $nbPMVrt     = 0;

            // Nombre des pièces avec des ob manquantes
            foreach ($obm as $key => $value) {

                $pasSaisir = $this->pasSaisir($dossier,$value->souscategorie_id);

                if (!$pasSaisir) {
                    // Pièces LCR
                    if ($value->date_reglement == null && $value->souscategorie_id == 5) {
                        $nbPMLCR++;
                    }

                    // Pièces Remise
                    if ($value->date_reglement == null && $value->souscategorie_id == 7) {
                        $nbPMRemise++;
                    }

                    // Pièces Frais
                    if ($value->date_facture == null && $value->souscategorie_id == 8) {
                        $nbPMFrais++;
                    }

                    // Pièces Virement
                    if ($value->date_echeance == null && $value->souscategorie_id == 6) {
                        $nbPMVrt++;
                    }
                }

            }

            // Vérification des ob existantes par mois
            foreach ($months as $key => $month) {
                foreach ($obm as $key => $value) {

                    $pasSaisir = $this->pasSaisir($dossier,$value->souscategorie_id);

                    if (!$pasSaisir) {
                        // LCR
                        if ($value->souscategorie_id == 5) {
                            $lcrExist = true;
                            if ($value->date_reglement == $month) {
                                if (!in_array($month, $lcr)) {
                                    array_push($lcr, $month);
                                }
                            } 
                        }
                        // Remise en banque
                        if ($value->souscategorie_id == 7) {
                            $remiseExist = true;
                            if ($value->date_reglement == $month) {
                                if (!in_array($month, $remise)) {
                                    array_push($remise, $month);
                                } 
                            } 
                        }
                        // Frais bancaire
                        if ($value->souscategorie_id == 8) {
                            $fraisExist = true;
                            if ($value->date_facture == $month) {
                                if (!in_array($month, $frais)) {
                                    array_push($frais, $month);
                                } 
                            } 
                        }
                        // Virement
                        if ($value->souscategorie_id == 6) {
                            $vrtExist = true;
                            if ($value->date_echeance == $month) {
                                if (!in_array($month, $vrt)) {
                                    array_push($vrt, $month);
                                } 
                            } 
                        }
                    }

                }
            }

            /**
             * @var array
             *
             * liste des ois manquants LCR
             */
            $missingLCR = ($lcrExist) ? $this->resetKey(array_diff($months, $lcr)) : array();

            /**
             * @var array
             *
             * Liste des mois manquants Remise
             */
            $missingRemise = ($remiseExist) ? $this->resetKey(array_diff($months, $remise)) : array();

            /**
             * @var array
             *
             * Liste des mois manquants Virement
             */
            $missingFrais = ($fraisExist) ? $this->resetKey(array_diff($months, $frais)) : array();

            /**
             * @var array
             *
             * Liste des mois manquants Virement
             */
            $missingVrt = ($vrtExist) ? $this->resetKey(array_diff($months, $vrt)) : array();
            
            // requête de verification
            $select = " select id
                        from banque_ob_manquante
                        where dossier_id     = " . $dossier . "
                        and souscategorie_id = :sc
                        and exercice         = " . $exercice . "
                        and banque_compte_id = " . $bc;

            // requête de mie à jour
            $update = "  update banque_ob_manquante
                                set dossier_id = " . $dossier . ",
                                    souscategorie_id     = :sc,
                                    mois                 = :mois,
                                    exercice             = " . $exercice . ",
                                    nb_pieces_manquantes = :nb_pieces_manquantes,
                                    banque_compte_id     = " . $bc . " 
                                where id = :id";

            // LCR
            if (!empty($missingLCR)) {
                $sc     = 5;
                $result = $this->fetch($select,array(
                    'sc' => $sc
                ));

                if (empty($result)) {
                    $values = array($dossier,$sc,json_encode($missingLCR, true),$exercice, $nbPMLCR, $bc);
                    $query = " insert into banque_ob_manquante (`dossier_id`, `souscategorie_id`, `mois`, `exercice`, `nb_pieces_manquantes`, `banque_compte_id`) values ( '" . implode( "', '" , $values ) . "' )";
                    $this->push($query);
                } else {
                    $this->push($update, array(
                        'sc'                   => $sc,
                        'mois'                 => json_encode($missingLCR, true),
                        'id'                   => $result[0]->id,
                        'nb_pieces_manquantes' => $nbPMLCR
                    ));
                }

            }

            // Remise en banque
            if (!empty($missingRemise)) {
                $sc     = 7;
                $result = $this->fetch($select,array(
                    'sc' => $sc
                ));

                if (empty($result)) {
                    $values = array($dossier,$sc,json_encode($missingRemise, true),$exercice, $nbPMRemise, $bc);
                    $query = " insert into banque_ob_manquante (`dossier_id`, `souscategorie_id`, `mois`, `exercice`, `nb_pieces_manquantes`, `banque_compte_id` ) values ( '" . implode( "', '" , $values ) . "' )";
                    $this->push($query);
                } else {
                    $this->push($update, array(
                        'sc'                   => $sc,
                        'mois'                 => json_encode($missingRemise, true),
                        'id'                   => $result[0]->id,
                        'nb_pieces_manquantes' => $nbPMRemise
                    ));
                }
            }

            // Frais bancaire
            if (!empty($missingFrais)) {
                $sc     = 8;
                $result = $this->fetch($select,array(
                    'sc' => $sc
                ));

                if (empty($result)) {
                    $values = array($dossier,$sc,json_encode($missingFrais, true),$exercice, $nbPMFrais, $bc);
                    $query = " insert into banque_ob_manquante (`dossier_id`, `souscategorie_id`, `mois`, `exercice`, `nb_pieces_manquantes`, `banque_compte_id`) values ( '" . implode( "', '" , $values ) . "' )";
                    $this->push($query);
                } else {
                    $this->push($update, array(
                        'sc'                   => $sc,
                        'mois'                 => json_encode($missingFrais, true),
                        'id'                   => $result[0]->id,
                        'nb_pieces_manquantes' => $nbPMFrais
                    ));
                }
            }

            // Virement
            if (!empty($missingVrt)) {

                $sc     = 6;
                $result = $this->fetch($select,array(
                    'sc' => $sc
                ));

                if (empty($result)) {
                    $values = array($dossier,$sc,json_encode($missingVrt, true),$exercice, $nbPMVrt, $bc);
                    $query = " insert into banque_ob_manquante (`dossier_id`, `souscategorie_id`, `mois`, `exercice`, `nb_pieces_manquantes`, `banque_compte_id`) values ( '" . implode( "', '" , $values ) . "' )";
                    $this->push($query);
                } else {
                    $this->push($update, array(
                        'sc'                   => $sc,
                        'mois'                 => json_encode($missingVrt, true),
                        'id'                   => $result[0]->id,
                        'nb_pieces_manquantes' => $nbPMVrt
                    ));
                }
            }

        }

    }

    /**
     * Réccupération des OB
     *
     * @param integer $exercice
     * @param integer $dossier
     *
     * @return array
     */
    public function getOb($exercice, $dossier, $bc)
    {
        $query = "  select date_format(sc.date_reglement,'%Y-%m') as date_reglement, date_format(sc.date_facture, '%Y-%m') as date_facture , date_format(sc.date_echeance, '%Y-%m') as date_echeance , d.nom as nom_dossier, d.cloture, i.exercice, sep.souscategorie_id, c.nom as client
                    from image i
                    inner join lot l on (i.lot_id = l.id)
                    inner join dossier d on (l.dossier_id = d.id)
                    inner join site s on (d.site_id = s.id)
                    inner join client c on (s.client_id = c.id)
                    inner join separation sep on (i.id = sep.image_id)
                    inner join saisie_controle sc on (i.id = sc.image_id)
                    left join banque_compte bc ON (bc.id = sc.banque_compte_id) 
                    where i.exercice = " . $exercice;
        $query .= " and c.status = 1
                    and (d.status = 1 or (d.status <> 1 and d.status_debut is not null and d.status_debut > " .$exercice . "))";

        $query .=  " and (sep.souscategorie_id = 8 or sep.souscategorie_id = 7 or sep.souscategorie_id = 6 or sep.souscategorie_id = 5)";

        $query .=  " and d.id=" . $dossier;

        $query .=  " and sc.banque_compte_id=" . $bc;

        $query .= " and sc.banque_compte_id is not null";

        return $this->fetch($query);
    }

    /**
     * Execution d'une requete  select sql
     *
     * @param string $query
     * @param array $param
     *
     * @return array
     */
    public function fetch($query, $param = array())
    {
        $prep = $this->pdo->prepare($query);
        $prep->execute($param);
        $resultat = $prep->fetchAll();

        return $resultat;
    }

    /**
     * Insertion ou Mise à jour dans une table
     *
     * @param string $query
     */
    public function push($query, $param = array())
    {
        $prep = $this->pdo->prepare($query);
        $prep->execute($param);
    }

    /**
     * Liste des mois dans une exercice
     *
     * @param array $beginEnd
     *
     * @return array
     */
    public function getBetweenDate($beginEnd)
    {
        $time1  = strtotime($beginEnd['start']);
        $time2  = strtotime($beginEnd['end']);
        $my     = date('mY', $time2);
        $months = array(date('Y-m', $time1));
        while ($time1 < $time2) {
            $time1 = strtotime(date('Y-m', $time1) . ' +1 month');
            if (date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('Y-m', $time1);
        }
        $months[] = date('Y-m', $time2);
        return $months;
    }

    /**
     * Mois debut et fin d'un exercice
     *
     * @param integer $cloture
     * @param integer $exercice
     *
     * @return array
     */
    public function beginEnd($cloture, $exercice)
    {
        if ($cloture < 9) {
            $debutMois = ($exercice - 1) . '-0' . ($cloture + 1) . '-01' ;
        } else if ($cloture >= 9 && $cloture < 12) {
            $debutMois = ($exercice - 1) . '-' . ($cloture + 1) . '-01';
        } else{
            $debutMois = $exercice . '-01-01';
        }

        if ($cloture < 10) {
            $finMois = $exercice . '-0' . $cloture . '-01';
        } else {
            $finMois = $exercice . '-' . $cloture . '-01';
        }

        return array(
            'start' => $debutMois,
            'end'   => $finMois
        );
    }



}