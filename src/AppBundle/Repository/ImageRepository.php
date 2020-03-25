<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 06/05/2016
 * Time: 14:57
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Image;
use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Client;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Lot;
use AppBundle\Entity\Souscategorie;
use AppBundle\Entity\Soussouscategorie;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class ImageRepository extends EntityRepository
{

    /**
     * TAF : Nombre des doublons et trou dans RB1
     *
     * @param array $param
     * @param integer $souscategorie
     *
     * @return array
     */
    public function getRb1AControler( $param, $souscategorie = 10 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT sc.banque_compte_id, sc.periode_d1, sc.periode_f1, sc.solde_debut, sc.solde_fin, false as is_doublon, i.ctrl_saisie
                FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (i.id = sep.image_id)
                INNER JOIN saisie_controle sc ON (i.id = sc.image_id)
                WHERE sep.souscategorie_id = " . $souscategorie;

        if ( $param['client'] != 0 ) {
            $query .= " AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
        }

        if ( $param['dossier'] != 0 ) {
            $query .= " AND d.id = " . $param['dossier'];
        }

        $query .= " AND i.exercice = " . $param['exercice'];

        $query .= " AND i.supprimer = 0";

        $query .= " AND sc.banque_compte_id IS NOT NULL";

        $orderby = " ORDER BY sc.banque_compte_id, sc.periode_d1, sc.periode_f1, i.nom";

        switch ( $param['periode'] ) {
            case 1:
                $now = new \DateTime();
                $dateNow = $now->format('Y-m-d');

                $query .= " AND l.date_scan = :dateNow" . $orderby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateNow' => $dateNow
                ));
                break;

            case 2:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneWeek = date_modify($dateNow, "-7 days");
                $dateDeb = $oneWeek->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin" . $orderby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 3:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneMonth = date_modify($dateNow, "-1 months");
                $dateDeb = $oneMonth->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin" . $orderby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 4:
                $prep = $pdo->prepare($query . $orderby);
                $prep->execute();
                break;

            case 5:
                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin" . $orderby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $param['perioddeb'],
                    'dateFin' => $param['periodfin'],
                ));
                break;

        }

        $resultat = $prep->fetchAll();

        $nbDoublon = 0;

        $nbTrou = 0;

        $nbImgSaisieKo = 0;

        $response = array();

        for ( $i = 0; $i < count($resultat) - 1; $i++ ) {
            if ( !$resultat[$i]->is_doublon ) {

                $ti = $resultat[$i];

                for ( $j = $i + 1; $j < count($resultat); $j++ ) {

                    $tj = $resultat[$j];

                    if ( ($tj->periode_d1 === $ti->periode_d1 && $tj->periode_f1 === $ti->periode_f1 && $tj->solde_debut === $ti->solde_debut && $tj->solde_fin === $ti->solde_fin) && ($tj->banque_compte_id == $ti->banque_compte_id)
                    ) {
                        $nbDoublon++;
                        $resultat[$j]->is_doublon = true;
                    }
                }
            }
        }

        for ( $i = 0; $i < count($resultat) - 1; $i++ ) {
            $ti = $resultat[$i];
            $tj = $resultat[$i + 1];

            if ( !$tj->is_doublon ) {
                if ( ($ti->solde_fin != $tj->solde_debut) && ($tj->banque_compte_id == $ti->banque_compte_id) ) {
                    $nbTrou++;
                }
            }
        }

        for ( $i = 0; $i < count($resultat) - 1; $i++ ) {
            $ti = $resultat[$i];

            if ( $ti->ctrl_saisie < 2 ) {
                $nbImgSaisieKo++;
            }
        }

        $response['doublon'] = $nbDoublon;

        $response['trou'] = $nbTrou;

        $response['acontroler'] = $nbTrou + $nbDoublon;

        $response['imgSaisieKo'] = $nbImgSaisieKo;

        return $response;

    }

    /**
     * TAF : Nombres d'images à controler dans RB2
     *
     * @param array $param
     *
     * @return integer
     */
    public function getRb2AControler( $param )
    {

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT d.nom, count(i.id) as nb, sc.banque_compte_id
                FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (i.id = sep.image_id)
                INNER JOIN saisie_controle sc ON (i.id = sc.image_id)
                WHERE sep.souscategorie_id = 10
                AND i.ctrl_saisie >= 2";

        if ( $param['client'] != 0 ) {
            $query .= " AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
        }

        if ( $param['dossier'] != 0 ) {
            $query .= " AND d.id = " . $param['dossier'];
        }

        $query .= " AND i.exercice = " . $param['exercice'];

        $query .= " AND i.supprimer = 0";

        $query .= " AND sc.banque_compte_id IS NOT NULL";

        $groupby = " GROUP BY sc.banque_compte_id";

        switch ( $param['periode'] ) {
            case 1:
                $now = new \DateTime();
                $dateNow = $now->format('Y-m-d');

                $query .= " AND l.date_scan = :dateNow" . $groupby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateNow' => $dateNow
                ));
                break;

            case 2:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneWeek = date_modify($dateNow, "-7 days");
                $dateDeb = $oneWeek->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin" . $groupby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 3:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneMonth = date_modify($dateNow, "-1 months");
                $dateDeb = $oneMonth->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin" . $groupby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 4:

                $query .= $groupby;

                $prep = $pdo->prepare($query);

                $prep->execute();
                break;

            case 5:
                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin" . $groupby;

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $param['perioddeb'],
                    'dateFin' => $param['periodfin'],
                ));
                break;

        }

        $resultat = $prep->fetchAll();

        $mouvements = 0;

        $nb = 0;

        foreach ( $resultat as $value ) {

            $mouvements = floatval($this->getMouvement($param['exercice'], $value->banque_compte_id));

            $soldeDebut = $this->getSolde($value->banque_compte_id, $param['exercice'], true);

            $soldeFin = $this->getSolde($value->banque_compte_id, $param['exercice'], false);

            $ecart = floatval(floor($soldeFin) - (floor($soldeDebut + $mouvements)));

            if ( $ecart !== floatval(0) ) {
                $nb += $value->nb;
            }

        }

        return $nb;

    }


    /**
     * TAF : Nombre d'images à saisir dans RB2
     *
     * @param array $param
     *
     * @return integer
     */
    public function getTafRb2Data( $param )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT count(i.id) AS nb
                FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (i.id = sep.image_id)
                WHERE sep.souscategorie_id = 10
                AND i.ctrl_saisie >= 2";

        if ( $param['client'] != 0 ) {
            $query .= " AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
        }

        if ( $param['dossier'] != 0 ) {
            $query .= " AND d.id = " . $param['dossier'];
        }

        $query .= " AND i.exercice = " . $param['exercice'];

        $query .= " AND i.id NOT IN (SELECT r.image_id FROM releve r)";

        switch ( $param['periode'] ) {
            case 1:
                $now = new \DateTime();
                $dateNow = $now->format('Y-m-d');

                $query .= " AND l.date_scan = :dateNow";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateNow' => $dateNow
                ));
                break;

            case 2:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneWeek = date_modify($dateNow, "-7 days");
                $dateDeb = $oneWeek->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 3:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneMonth = date_modify($dateNow, "-1 months");
                $dateDeb = $oneMonth->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 4:
                $prep = $pdo->prepare($query);

                $prep->execute();
                break;

            case 5:
                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $param['perioddeb'],
                    'dateFin' => $param['periodfin'],
                ));
                break;

        }

        $resultat = $prep->fetchAll();

        return $resultat[0]->nb;

    }

    /**
     * TAF : Nombre d'images à saisir spésifique au sous categorie, par défaut RB1 (souscategorie = 10)
     *
     * @param array $param
     * @param integer $souscategorie
     *
     * @return array
     */
    public function getTafData( $param, $souscategorie = 10 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT count(i.id) AS nb, sc.libelle_new
                FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (i.id = sep.image_id)
                INNER JOIN souscategorie sc ON (sc.id = sep.souscategorie_id)
                WHERE sep.souscategorie_id = " . $souscategorie;

        $query .= " AND i.ctrl_saisie < 2";

        if ( $param['client'] != 0 ) {
            $query .= " AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
        }

        if ( $param['dossier'] != 0 ) {
            $query .= " AND d.id = " . $param['dossier'];
        }

        $query .= " AND i.exercice = " . $param['exercice'];

        switch ( $param['periode'] ) {
            case 1:
                $now = new \DateTime();
                $dateNow = $now->format('Y-m-d');

                $query .= " AND l.date_scan = :dateNow";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateNow' => $dateNow
                ));
                break;

            case 2:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneWeek = date_modify($dateNow, "-7 days");
                $dateDeb = $oneWeek->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 3:
                $dateNow = new \DateTime();
                $now = clone $dateNow;
                $oneMonth = date_modify($dateNow, "-1 months");
                $dateDeb = $oneMonth->format('Y-m-d');
                $dateFin = $now->format('Y-m-d');

                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
                break;

            case 4:
                $prep = $pdo->prepare($query);
                $prep->execute();
                break;

            case 5:
                $query .= " AND l.date_scan >= :dateDeb";
                $query .= " AND l.date_scan <= :dateFin";

                $prep = $pdo->prepare($query);

                $prep->execute(array(
                    'dateDeb' => $param['perioddeb'],
                    'dateFin' => $param['periodfin'],
                ));
                break;

        }

        $resultat = $prep->fetchAll();

        return $resultat[0];


    }

    /**
     * Liste des dossiers actifs par rapport à l'exercice
     *
     * @param integer $client
     * @param string $exercice
     *
     * @return array
     */
    public function getListDosierByExo( $client, $exercice )
    {
        $resultat = array();

        if ( $client != 0 ) {
            $con = new CustomPdoConnection();
            $pdo = $con->connect();

            $clientQuery = " WHERE c.status = 1 ";

            $clientQuery .= " AND c.id = " . $client;

            $query = "SELECT d.nom, d.id, d.cloture,d.date_cloture, d.debut_activite,c.id as client_id, c.nom as client
                  FROM dossier d
                  INNER JOIN site s ON (d.site_id = s.id)
                  INNER JOIN client c ON (s.client_id = c.id)";

            $query .= $clientQuery;

            $query .= " AND (d.status = 1";
            $query .= " OR ( d.status <> 1 
                    AND d.status_debut IS NOT NULL 
                    AND d.status_debut > " . $exercice . " ))";

            $query .= " ORDER BY d.nom";

            $prep = $pdo->prepare($query);

            $prep->execute();

            $resultat = $prep->fetchAll();
        }


        return $resultat;
    }


    /**
     * Liste des dossiers priorisés dans Banque OB
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksBanquesObPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";


        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND i.saisie1 = 1
                  AND sep.categorie_id = 16 AND sep.souscategorie_id <> 10 GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    } else {
                        $min['dossier'] .= $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";
        }

        return $min;

    }

    /**
     * Nombre des images dans Banque OB
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksBanquesOb( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND i.saisie1 = 1
                  AND sep.categorie_id = 16 AND sep.souscategorie_id <> 10 GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans Banque RB2
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksBanquesRb2Priorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND i.ctrl_saisie = 1
                  AND sep.souscategorie_id = 10 GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans Banque RB2
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksBanquesRb2( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND i.ctrl_saisie = 1
                  AND sep.souscategorie_id = 10 GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans Banque RB1
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksBanquesRb1Priorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND i.saisie1 = 1
                  AND sep.souscategorie_id = 10 GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans Banque RB1
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksBanquesRb1( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND i.saisie1 = 1
                  AND sep.souscategorie_id = 10 GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans CTRL IMPUTATION
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksCtrlImputationPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND i.ctrl_imputation = 1
                  AND sep.categorie_id <> 16 GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans CTRL IMPUTATION
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksCtrlImputation( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND i.ctrl_imputation = 1
                  AND sep.categorie_id <> 16 GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans IMPUTATION
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksImputationPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";


        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND i.imputation = 1
                  AND sep.categorie_id <> 16 GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans IMPUTATION
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksImputation( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND i.imputation = 1
                  AND sep.categorie_id <> 16 GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }


    /**
     * Liste des dossiers priorisés dans CTRL SAISIE
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksCtrlSaisiePriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";


        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND i.ctrl_saisie = 1
                  AND sep.categorie_id <> 16 GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;
    }

    /**
     * Nombre des images dans CTRL SAISIE
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksCtrlSaisie( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)
                INNER JOIN separation sep ON (sep.image_id = i.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND i.ctrl_saisie = 1
                  AND sep.categorie_id <> 16 GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans SAISIES
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksSaisiesPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";


        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND (i.saisie1 = 1 OR i.saisie2 = 1) GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans SAISIES
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksSaisies( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND (i.saisie1 = 1 OR i.saisie2 = 1) GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans SEPARATION
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksSeparationPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.download IS NOT NULL
                  AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND (l.status = 2 OR l.status = 3) GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans SEPARATION
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksSeparation( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.download IS NOT NULL
                  AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND (l.status = 2 OR l.status = 3) GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans RECEPTION
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksReceptionPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.download IS NOT NULL
                  AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " AND (l.status = 0 OR l.status = 1) GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;
    }

    /**
     * Liste des dossiers priorisés dans RECEPTION
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksReception( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                INNER JOIN lot l ON (i.lot_id = l.id)
                INNER JOIN dossier d ON (l.dossier_id = d.id)
                INNER JOIN site s ON (d.site_id = s.id)
                INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                AND c.status = 1
                AND c.id = " . $client;

        $query .= " AND i.download IS NOT NULL
                  AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " AND (l.status = 0 OR l.status = 1) GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();


        return $resultat;

    }

    /**
     * Liste des dossiers priorisés dans PICDATA
     *
     * @param integer $exercice
     * @param integer $client id client
     *
     * @return array
     */
    public function getImagesStocksPicdataPriorite( $exercice, $client )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $inner = "";

        $as = "";

        $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                 LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

        $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";


        $query = "SELECT DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) as diff,
                  d.nom as dossier,
                  count(i.id) as nb";

        $query .= $as;

        $query .= " FROM image i 
                  INNER JOIN lot l ON (i.lot_id = l.id)
                  INNER JOIN dossier d ON (l.dossier_id = d.id)
                  INNER JOIN site s ON (d.site_id = s.id)
                  INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                  AND c.status = 1
                  AND c.id = " . $client;

        $query .= " AND i.download IS NULL
                  AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= " GROUP BY d.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        $min = array();

        foreach ( $resultat as $value ) {

            if ( empty($min) ) {
                if ( $value->diff != null ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] = $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;
                }
            } else {

                if ( ($value->diff <= $min['diff']) && ($value->couleur == $min['couleur']) && ($value->diff != null) ) {
                    $min['diff'] = $value->diff;
                    $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                    $min['couleur'] = $value->couleur;

                } else {
                    if ( ($value->diff < $min['diff']) && ($value->couleur != $min['couleur']) && ($value->diff != null) ) {
                        $min['diff'] = $value->diff;
                        $min['dossier'] .= ',' . $value->dossier . '*' . $value->couleur . '*' . $value->nb;
                        $min['couleur'] = $value->couleur;

                    }
                }
            }

        }

        if ( empty($min) ) {
            $min['couleur'] = "transparent";
            $min['dossier'] = "";

        }

        return $min;

    }

    /**
     * Nombre des images dans PICDATA
     *
     * @param integer $exercice
     * @param integer $client id client
     * @param integer $dossier id dossier
     *
     * @return array
     */
    public function getImagesStocksPicdata( $exercice, $client, $dossier = 0 )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $queryDossier = "";

        $inner = "";

        $as = "";

        if ( $dossier != 0 ) {

            $queryDossier = " AND d.id = " . $dossier;

            $inner = " LEFT JOIN taches_priorite_dossier tpd ON (tpd.dossier_id = d.id)
                     LEFT JOIN taches_priorite_couleur tpc ON ( DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) >= tpc.min AND DATEDIFF(   (DATE(tpd.date)), (DATE(now())) ) < tpc.max ) ";

            $as = ", IFNULL(tpc.code_couleur,'transparent') as couleur, d.nom as dossier";

        }

        $query = "SELECT count(i.id) as nb, c.nom as client";

        $query .= $as;

        $query .= " FROM image i 
                  INNER JOIN lot l ON (i.lot_id = l.id)
                  INNER JOIN dossier d ON (l.dossier_id = d.id)
                  INNER JOIN site s ON (d.site_id = s.id)
                  INNER JOIN client c ON (s.client_id = c.id)";

        $query .= $inner;

        $query .= " WHERE c.nom IS NOT NULL
                  AND c.status = 1
                  AND c.id = " . $client;

        $query .= " AND i.download IS NULL
                  AND i.exercice = " . $exercice;

        $query .= " AND (d.status = 1 
                  OR( d.status <> 1
                      AND d.status IS NOT NULL
                      AND d.status_debut > " . $exercice . " ))";

        $query .= $queryDossier;

        $query .= " GROUP BY c.id";

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Reqêtte pour Graphe des répartition
     *
     * @param array $param
     *
     * @return array
     */
    public function getImageReputation( $param )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /*Répartition par Client*/
        if ( $param['client'] == 0 ) {
            $query = "SELECT count(I.id) as y, C.nom as name
                  FROM image I
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  WHERE I.exercice = " . $param['exercice'];

            $query .= " AND (D.status = 1 
                    OR( D.status <> 1
                      AND D.status IS NOT NULL
                      AND D.status_debut > " . $param['exercice'] . " ))";

            $query .= " AND C.status = 1";

            $query .= " GROUP BY C.id";
        } /*Répartition par dossier du client séléctionné*/
        else {
            $query = "SELECT count(I.id) as y, D.nom as name, C.nom as client
                  FROM image I
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  WHERE I.exercice = " . $param['exercice'];

            $query .= " AND (D.status = 1 
                    OR( D.status <> 1
                      AND D.status IS NOT NULL
                      AND D.status_debut > " . $param['exercice'] . " ))";

            $query .= " AND C.status = 1";

            $query .= " AND C.id = " . $param['client'];
            $query .= " GROUP BY D.nom";
        }

        $prep = $pdo->prepare($query);

        $prep->execute();

        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Tableau des images reçues avec filtre
     *
     * @param array $param
     *
     * @return array
     */
    public function getImagesRecues( $param )
    {

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $and = "";

        /*Client différent de Tous*/
        if ( $param['client'] != 0 ) {
            $and .= " C.id = " . $param['client'] . " AND";
            if ( $param['dossier'] != 0 ) {
                $and .= " D.id = " . $param['dossier'] . " AND";
            }
        } /*Client égale à Tous*/
        else {
            if ( $param['dossier'] != 0 ) {
                $and .= " D.id = " . $param['dossier'] . " AND";
            }
        }

        $and .= " I.exercice = " . $param['exercice'];

        $and .= "   AND (
                        (D.status = 1 and D.active = 1)
                        OR (D.status <> 1 
                            AND D.status is not null 
                            AND D.status_debut > " . $param['exercice'] . " 
                            AND D.active = 1
                        )
                    )";

        /*Filtre par date pièce*/
        if ( $param['typedate'] == 2 ) {

            $inner = "";
            $as = "";
            $having = " HAVING date_piece IS NOT NULL";

            if ( $param['cas'] == 1 ) {
                $having .= " AND date_format(date_piece,'%Y-%m') = :periode";
            } else {
                if ( $param['cas'] !== 5 ) {
                    $having .= " AND date_format(date_piece,'%Y-%m') >= :dateDeb";
                    $having .= " AND date_format(date_piece,'%Y-%m') <= :dateFin";
                }
            }

            $as = ", date_format(IF(I.ctrl_imputation >= 2,IFNULL(ic.date_facture,ic.periode_d1),IF(I.imputation >= 2, IFNULL(im.date_facture,im.periode_d1),IF(I.ctrl_saisie >= 2, IFNULL(sc.date_facture,sc.periode_d1), IF(I.saisie2 >= 2, IFNULL(s2.date_facture,s2.periode_d1),IF(I.saisie1 >= 2, IFNULL(s1.date_facture,s1.periode_d1),null))))), '%Y-%m') as date_piece, D.date_cloture, D.debut_activite, '' as isnull";

            $inner = " INNER JOIN imputation_controle ic ON (ic.image_id = I.id)";
            $inner .= " INNER JOIN imputation im ON (im.image_id = I.id)";
            $inner .= " INNER JOIN saisie_controle sc ON (sc.image_id = I.id)";
            $inner .= " INNER JOIN saisie2 s2 ON (s2.image_id = I.id)";
            $inner .= " INNER JOIN saisie1 s1 ON (s1.image_id = I.id)";

            $query = "SELECT count(I.id) as nb, D.cloture, C.nom as client, D.nom as dossier, date_format(L.date_scan,'%Y-%m') as date_scan, L.id as lot, D.id as dossier_id, C.id as client_id " . $as . "
                    FROM image I
                    INNER JOIN lot L ON(I.lot_id=L.id) " . $inner . "
                    INNER JOIN dossier D ON(L.dossier_id = D.id)
                    INNER JOIN site S ON (D.site_id = S.id)
                    INNER JOIN client C ON (S.client_id = C.id)
                    WHERE C.status = 1 AND" . $and . "";

            $query .= " GROUP BY I.id " . $having . "  ORDER BY D.nom ASC";

        } /*Filtre date envoi*/
        else {

            $queryScan = "";

            if ( $param['cas'] == 1 ) {
                $queryScan = " AND L.date_scan = :periode";
            } else {
                if ( $param['cas'] !== 5 ) {
                    $queryScan .= " AND L.date_scan >= :dateDeb";
                    $queryScan .= " AND L.date_scan <= :dateFin";
                }
            }

            $query = "SELECT count(I.id) as nb, D.cloture, C.nom as client, D.nom as dossier, date_format(L.date_scan,'%Y-%m') as date_scan, L.id as lot, D.id as dossier_id, C.id as client_id, '' as isnull
                    FROM image I
                    INNER JOIN lot L ON(I.lot_id=L.id)
                    INNER JOIN dossier D ON(L.dossier_id = D.id)
                    INNER JOIN site S ON (D.site_id = S.id)
                    INNER JOIN client C ON (S.client_id = C.id)
                    WHERE C.status = 1 AND" . $and . $queryScan;

            $query .= " GROUP BY L.id ORDER BY D.nom ASC";
        }

        $prep = $pdo->prepare($query);

        switch ( $param['cas'] ) {
            case 1:
                $now = $param['aujourdhui'];
                $prep->execute(array(
                    'periode' => $now,
                ));
                break;
            case 5:
                $prep->execute();
                break;
            default:
                if ( isset($param['dateFin']) && isset($param['dateFin']) ) {
                    $dateDeb = $param['dateDeb'];
                    $dateFin = $param['dateFin'];
                    $prep->execute(array(
                        'dateDeb' => $dateDeb,
                        'dateFin' => $dateFin,
                    ));
                } else {
                    $prep->execute();
                }
        }


        $resultat = $prep->fetchAll();

        return $resultat;

    }

    /**
     * Liste des images d'un lot et d'une Catégorie
     *
     * @param $lot
     * @param $categorie
     * @param bool $to_array
     *
     * @return array
     */
    public function imageLotCategorie( $lot, $categorie = NULL, $to_array = FALSE )
    {
        $qb = $this->getEntityManager()->getRepository('AppBundle:Image')
                   ->createQueryBuilder('i');
        if ( $categorie != NULL ) {
            $images = $qb
                ->where('i.decouper = :decouper')
                ->andWhere('i.lot = :lot')
                ->innerJoin('AppBundle\Entity\Separation', 's', 'WITH', 'i.id = s.image')
                ->andWhere('s.categorie = :categorie')
                ->innerJoin('AppBundle\Entity\Categorie', 'cat', 'WITH', 'cat.id = s.categorie')
                ->setParameter('decouper', 0)
                ->setParameter('lot', $lot)
                ->setParameter('categorie', $categorie)
                ->getQuery();
        } else {
            $images = $qb
                ->where('i.decouper = :decouper')
                ->andWhere('i.lot = :lot')
                ->innerJoin('AppBundle\Entity\Separation', 's', 'WITH', 'i.id = s.image')
                ->setParameter('decouper', 0)
                ->setParameter('lot', $lot)
                ->getQuery();
        }
        if ( $to_array ) {
            return $images->getArrayResult();
        }

        return $images->getResult();
    }

    public function imageCategorieSeparation( \AppBundle\Entity\Image $image )
    {
        $qb = $this->getEntityManager()->getRepository('AppBundle:Categorie')
                   ->createQueryBuilder('cat');
        $categorie = $qb
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.categorie = cat.id')
            ->innerJoin('AppBundle\Entity\Image', 'img', 'WITH', 'sep.image = img.id')
            ->where('img.decouper = :decouper')
            ->andWhere('img.id = :image_id')
            ->setParameter('decouper', 0)
            ->setParameter('image_id', $image->getId())
            ->getQuery()
            ->getOneOrNullResult();
        return $categorie;
    }

    /**
     * Lots sur picdata non descendus
     *
     * @param $lots
     *
     * @return mixed
     */
    public function imageNonDescendu( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(i.id) AS nbimage FROM image i JOIN lot l ON i.lot_id=l.id JOIN 
            dossier d ON l.dossier_id=d.id WHERE i.download IS NULL AND supprimer=:supprimer AND decouper=:decouper AND i.renommer=:renommer 
            AND  d.date_stop_saisie is null";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'supprimer' => 0,
            'decouper' => 0,
            'renommer' => 1,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT lot_id) AS nblot FROM image i JOIN lot l ON i.lot_id=l.id 
                        JOIN dossier d ON l.dossier_id=d.id 
                        WHERE i.download IS NULL AND d.date_stop_saisie is NULL AND 
                        i.supprimer=:supprimer AND i.decouper=:decouper AND i.renommer=:renommer";


        $prep = $pdo->prepare($query);
        $prep->execute([
            'supprimer' => 0,
            'decouper' => 0,
            'renommer' => 1,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        $pdo = NULL;
        $con = NULL;
        return $images;
    }

    /**
     * Lots descendus en attente traitement
     *
     * @param $lots
     *
     * @return mixed
     */
    public function imageAttenteTraitement( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(I.id) AS nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id) 
                  WHERE I.download IS NOT NULL AND I.supprimer = :supprimer AND I.decouper=:decouper AND L.status = :status AND
                  I.renommer=:renommer AND
                  I.saisie1=:saisie1  AND I.saisie2=:saisie2 and I.ctrl_saisie=:ctrlSaisie and I.imputation=:imputation";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'decouper' => 0,
            'supprimer' => 0,
            'status' => 0,
            'renommer' => 1,
            'saisie1' => 0,
            'saisie2' => 0,
            'ctrlSaisie' => 0,
            'imputation'=> 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT i.lot_id) AS nblot FROM image i JOIN lot l ON i.lot_id=l.id 
                        JOIN dossier d ON l.dossier_id=d.id 
                        WHERE i.download IS NOT NULL AND l.status=:status AND d.date_stop_saisie is NULL AND i.supprimer=:supprimer 
                        AND i.decouper=:decouper AND i.renommer=:renommer AND
                        i.saisie1=:saisie1 AND i.saisie2=:saisie2 AND i.ctrl_saisie=:ctrlSaisie AND i.imputation=:imputation ";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 0,
            'supprimer' => 0,
            'decouper' => 0,
            'renommer' => 1,
            'saisie1' => 0,
            'saisie2' => 0,
            'ctrlSaisie' => 0,
            'imputation' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        $pdo = NULL;
        $con = NULL;
        return $images;
    }

    /**
     * Lots et images à traiter en Reception Niveau 1
     *
     * @param $lots
     *
     * @return mixed
     */
    public function imageTraitementN1( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(I.id) AS nbimage FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN lot L ON(I.lot_id = L.id) 
                  WHERE I.download IS NOT NULL AND I.supprimer = :supprimer 
                  AND L.status = :status AND A.status = :astatus";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 0,
            'astatus' => 0,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT L.id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id) 
                  INNER JOIN  lot L ON (I.lot_id = L.id) 
                  WHERE L.status = :status AND A.status = :astatus 
                  AND I.download IS NOT NULL AND I.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 0,
            'astatus' => 0,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        $pdo = NULL;
        $con = NULL;
        return $images;
    }

    /**
     * Lots et images à traiter en Reception Niveau 1
     *
     * @param $lots
     *
     * @return mixed
     */
    public function imageTraitementB( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(I.id) AS nbimage FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN lot L ON(I.lot_id = L.id) 
                  INNER JOIN  separation S ON (S.image_id = I.id) 
                  WHERE I.download IS NOT NULL AND I.supprimer = :supprimer 
                  AND S.categorie_id=16
                  AND L.status = :lstatus AND A.status = :astatus AND I.saisie1 = :ssaisie";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'ssaisie' => 0,
            'lstatus' => 4,
            'astatus' => 4,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT L.id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id) 
                  INNER JOIN  lot L ON (I.lot_id = L.id) 
                  INNER JOIN  separation S ON (S.image_id = I.id) 
                  WHERE L.status = :lstatus AND A.status = :astatus  AND I.saisie1 = :ssaisie
                  AND S.categorie_id=16
                  AND I.download IS NOT NULL AND I.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'ssaisie' => 0,
            'lstatus' => 4,
            'astatus' => 4,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        $pdo = NULL;
        $con = NULL;
        return $images;
    }

    /**
     * Images découpées  à remonter sur picdata
     *
     * @param $lots
     *
     * @return mixed
     */
    public function imageRemonteCurrent( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(id) AS nbimage FROM image WHERE a_remonter = :aremonter AND supprimer = :supprimer 
                  AND decouper = :decouper AND numerotation_local = :numerotationLocal";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'supprimer' => 0,
            'aremonter' => 1,
            'decouper'  => 0,
            'numerotationLocal'  => 1
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT lot_id) AS nblot FROM image WHERE a_remonter = :aremonter AND 
                  supprimer = :supprimer AND decouper = :decouper AND numerotation_local = :numerotationLocal";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'supprimer' => 0,
            'aremonter' => 1,
            'decouper'  => 0,
            'numerotationLocal'  => 1
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        $pdo = NULL;
        $con = NULL;
        return $images;
    }

    /**
     * Traitement Niv. 2
     *
     * @param $lots
     *
     * @return mixed
     */
    public function imageTraitementN2( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(I.id) AS nbimage FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id) 
                  INNER JOIN lot L ON(I.lot_id = L.id) 
                  WHERE L.status = :status AND I.supprimer = :supprimer 
                  AND A.status = :astatus";

        $prep = $pdo->prepare($query);
        $prep->execute([
            'supprimer' => 0,
            'status' => 2,
            'astatus' => 2,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT L.id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id) 
                  INNER JOIN lot L ON(I.lot_id = L.id) 
                  WHERE L.status = :status AND I.supprimer = :supprimer
                  AND A.status = :astatus";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 2,
            'supprimer' => 0,
            'astatus' => 2,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        $pdo = NULL;
        $con = NULL;
        return $images;
    }

    public function imageSaisie1( &$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'OS_1';

        /** IMAGES */
//        $query = "SELECT COUNT(id) AS nbimage FROM image_a_traiter
//                    WHERE status = :status AND saisie1 = :saisie1 AND decouper = :decouper";

        $query = "SELECT COUNT(it.id) AS nbimage FROM image_a_traiter it JOIN image i ON it.image_id=i.id 
                JOIN separation SEP ON SEP.image_id=i.id
                JOIN lot l ON i.lot_id=l.id JOIN dossier d ON l.dossier_id=d.id 
                WHERE SEP.categorie_id != :categorieId AND d.site_id IN (SELECT id FROM site WHERE client_id IN
                (SELECT rc.client FROM responsable_client rc JOIN rattachement r ON rc.responsable=r.operateur_id 
                WHERE r.operateur_rat_id = :userId))
                AND it.status = :status AND it.saisie1 = :saisie1 AND it.decouper = :decouper AND i.saisie1 = :isaisie1 
                AND i.supprimer=:supprimer AND l.status = :lstatus ";

        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'lstatus' => 4,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
//        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id)
//                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper";
        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON (A.image_id = I.id) 
                  JOIN separation SEP ON I.id=SEP.image_id
                  join lot l on I.lot_id=l.id join dossier d on l.dossier_id=d.id 
                  where SEP.categorie_id!= :categorieId AND d.site_id in (select id from site where client_id in
                  (select client from responsable_client rc join rattachement r on rc.responsable=r.operateur_id where r.operateur_rat_id=:userId))
                                  AND A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND I.supprimer = :supprimer
                                  AND l.status = :lstatus";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
            'lstatus' => 4,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }


    public function imageTenueSaisie1( &$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'OS_1';

        /** IMAGES */


        $query = "SELECT COUNT(A.image_id) AS nbimage
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini AND code=:codeEtape)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'userRat' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'statusLot' => 4,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** IMAGE DANS affectation tenue */

        $query = "SELECT COUNT(I.id) AS nbimage FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id 
                JOIN separation s ON s.image_id=I.id AND s.categorie_id=a.categorie_id
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $images += $res[0]->nbimage;
        }
        

        /** LOTS */

        $query = "SELECT COUNT(DISTINCT L.id) AS nblot
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'userRat' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'statusLot' => 4,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
            'fini' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }


        /** NOMBRE LOT DANS AFFECTATION TENUE*/
        $query = "SELECT COUNT(DISTINCT a.lot_id) AS nblot FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $lots += $res[0]->nblot;
        }


        return $images;
    }


    public function imageSaisie1Banque( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'OS_1';

        /** IMAGES */


        $query = "SELECT COUNT(A.image_id) AS nbimage
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
				  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND I.supprimer = :supprimer
                  AND (SEP.souscategorie_id IN (5,6,7,8,10, 937, 939, 941) OR (SEP.souscategorie_id = 153 AND SEP.soussouscategorie_id = 1905)) 
                  AND SEP.categorie_id = :categorie";

        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 4,
            'saisie1' => 0,
            'decouper' => 0,
            'supprimer' => 0,
            'categorie' => 16
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id) 
                  INNER JOIN separation SEP on SEP.image_id = A.image_id AND SEP.categorie_id = :categorie 
                  AND (SEP.souscategorie_id IN (5,6,7,8,10, 937, 939, 941) OR (SEP.souscategorie_id = 153 AND SEP.soussouscategorie_id = 1905))
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 4,
            'saisie1' => 0,
            'decouper' => 0,
            'categorie' => 16,
//            'obids' => implode(',', $obIds)

        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }


    public function imageTenueSaisie2( &$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'OS_2';

        /** IMAGES */


        $query = "SELECT COUNT(A.image_id) AS nbimage
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'userRat' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'statusLot' => 4,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
            'fini' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** IMAGE DANS affectation tenue */

        $query = "SELECT COUNT(I.id) AS nbimage FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id
                JOIN separation s ON s.image_id=I.id AND s.categorie_id=a.categorie_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini ";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $images += $res[0]->nbimage;
        }
        

        /** LOTS */

        $query = "SELECT COUNT(DISTINCT L.id) AS nblot
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'userRat' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'statusLot' => 4,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
            'fini' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }

        /** NOMBRE LOT DANS AFFECTATION TENUE*/
        $query = "SELECT COUNT(DISTINCT a.lot_id) AS nblot FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $lots += $res[0]->nblot;
        }

        return $images;
    }


    public function imageSaisie2( &$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
//        $query = "SELECT COUNT(A.id) AS nbimage FROM image_a_traiter A
//                  INNER JOIN separation SEP ON(A.image_id = SEP.image_id)
//                  INNER JOIN categorie CAT ON(SEP.categorie_id = CAT.id)
//                  WHERE A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper
//                  AND CAT.code NOT IN('CODE_CAISSE', 'CODE_BANQUE', 'CODE_FISC', 'CODE_NDF', 'CODE_SOC')";


//        $query = "SELECT COUNT(A.id) AS nbimage FROM image_a_traiter A
//                  INNER JOIN separation SEP ON(A.image_id = SEP.image_id)
//                  WHERE A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper
//                  AND SEP.categorie_id NOT IN('14', '16', '20', '11', '21')";


        $query = "SELECT COUNT(it.id) AS nbimage FROM image_a_traiter it JOIN image i ON it.image_id=i.id 
                JOIN separation SEP ON SEP.image_id=i.id
                JOIN lot l ON i.lot_id=l.id JOIN dossier d ON l.dossier_id=d.id 
                WHERE SEP.categorie_id != :categorieId AND d.site_id IN (SELECT id FROM site WHERE client_id IN
                (SELECT rc.client FROM responsable_client rc JOIN rattachement r ON rc.responsable=r.operateur_id 
                WHERE r.operateur_rat_id = :userId))
                AND it.status = :status AND it.saisie2 = :saisie2 AND it.decouper = :decouper AND i.saisie2 = :isaisie2
                 AND i.supprimer = :supprimer AND l.status = :lstatus";


        $prep = $pdo->prepare($query);
//        $prep->execute([
//            'status' => 4,
//            'saisie2' => 0,
//            'decouper' => 0,
//        ]);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie2' => 0,
            'isaisie2' => 0,
            'lstatus' => 4,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;

        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
//        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id)
//                  INNER JOIN separation SEP ON(SEP.image_id = I.id)
//                  INNER JOIN categorie CAT ON(SEP.categorie_id = CAT.id)
//                  WHERE A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper
//                  AND CAT.code NOT IN('CODE_CAISSE', 'CODE_BANQUE', 'CODE_FISC', 'CODE_NDF', 'CODE_SOC')";


//        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id)
//                  INNER JOIN separation SEP ON(SEP.image_id = I.id)
//                  WHERE A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper
//                  AND SEP.categorie_id NOT IN('14', '16', '20', '11', '21')";
//
//        $prep = $pdo->prepare($query);
//        $prep->execute([
//            'status' => 4,
//            'saisie2' => 0,
//            'decouper' => 0,
//        ]);


        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON (A.image_id = I.id) 
                  JOIN separation SEP ON I.id=SEP.image_id
                  join lot l on I.lot_id=l.id join dossier d on l.dossier_id=d.id 
                  where SEP.categorie_id!= :categorieId AND d.site_id in (select id from site where client_id in
                  (select client from responsable_client rc join rattachement r on rc.responsable=r.operateur_id where r.operateur_rat_id=:userId))
                                  AND A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper  AND I.saisie2 =:isaisie2
                                  AND I.supprimer = :supprimer AND l.status=:lstatus";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie2' => 0,
            'isaisie2' => 0,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
            'lstatus' => 4,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }


    public function imageSaisie2Banque( &$lots, $exercice )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(I.id) AS nbimage FROM image I 
                  INNER JOIN separation SEP ON I.id = SEP.image_id 
                  INNER JOIN saisie_controle SC on I.id = SC.image_id AND SC.banque_compte_id IS NOT NULL
                  WHERE I.ctrl_saisie >= :ctrl_saisie AND I.decouper = :decouper
                  AND SEP.categorie_id = :categorie_id
                  AND SEP.souscategorie_id = :souscategorie_id 
                  AND I.id NOT IN (SELECT DISTINCT image_id FROM releve) AND I.supprimer = :supprimer 
                  AND I.id NOT IN (SELECT DISTINCT  image_id FROM panier WHERE etape_traitement_id = :etape_traitement_id)
                  AND I.exercice >= :exercice LIMIT 10000 ";

        $prep = $pdo->prepare($query);
        $prep->execute([
            'ctrl_saisie' => 3,
            'categorie_id' => 16,
            'souscategorie_id' => 10,
            'exercice' => $exercice,
            'supprimer' => 0,
            'decouper' => 0,
            'etape_traitement_id' => 26
        ]);
        $res = $prep->fetchAll();
        $images = 0;

        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image I 
                  INNER JOIN separation SEP ON I.id = SEP.image_id 
                  WHERE I.ctrl_saisie >= :ctrl_saisie 
                  AND SEP.categorie_id = :categorie_id
                  AND SEP.souscategorie_id = :souscategorie_id 
                  AND I.id NOT IN (SELECT DISTINCT image_id FROM releve) 
                  AND I.exercice = :exercice LIMIT 1000 ";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'ctrl_saisie' => 3,
            'categorie_id' => 16,
            'souscategorie_id' => 10,
            'exercice' => $exercice
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }

    public function imageCtrlSaisie( &$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'OS_1';

        /** IMAGES */
//        $query = "SELECT COUNT(id) AS nbimage FROM image_a_traiter WHERE status = :status
//                  AND saisie1 = :saisie1 AND saisie2 = :saisie2 AND decouper = :decouper";
//        $prep = $pdo->prepare($query);
//        $prep->execute([
//            'status' => 4,
//            'saisie1' => 2,
//            'saisie2' => 2,
//            'decouper' => 0,
//        ]);

        $query = "SELECT COUNT(it.id) AS nbimage FROM image_a_traiter it JOIN image i ON it.image_id=i.id 
                  JOIN separation SEP ON SEP.image_id=i.id
                  JOIN lot l ON i.lot_id=l.id JOIN dossier d ON l.dossier_id=d.id 
                  WHERE SEP.categorie_id != :categorieId AND d.site_id IN (SELECT id FROM site WHERE client_id IN
                  (SELECT rc.client FROM responsable_client rc JOIN rattachement r ON rc.responsable=r.operateur_id 
                  WHERE r.operateur_rat_id = :userId))
                    AND it.status = :status AND it.saisie1 = :saisie1 AND it.saisie2 = :saisie2 AND it.decouper = :decouper
                  AND i.saisie1 = :isaisie1 AND i.saisie2 = :isaisie2
                  AND i.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie1' => 2,
            'saisie2' => 2,
            'isaisie1' => 3,
            'isaisie2' => 3,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
//        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id)
//                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 AND A.decouper = :decouper";
//        $prep = $pdo->prepare($query);
//        $prep->execute([
//            'status' => 4,
//            'saisie1' => 2,
//            'saisie2' => 2,
//            'decouper' => 0,
//        ]);

        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON (A.image_id = I.id)
                  JOIN separation SEP ON I.id=SEP.image_id
                  join lot l on I.lot_id=l.id join dossier d on l.dossier_id=d.id 
                  where SEP.categorie_id!= :categorieId AND d.site_id in (select id from site where client_id in
                  (select client from responsable_client rc join rattachement r on rc.responsable=r.operateur_id where r.operateur_rat_id=:userId))
                                  AND A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 AND 
                                  A.decouper = :decouper  AND I.saisie1 = :isaisie1 AND I.saisie2 =:isaisie2
                                  AND I.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'saisie2' => 0,
            'isaisie1' => 3,
            'isaisie2' => 3,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);

        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }


    public function imageTenueCtrlSaisie( &$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'CTRL_OS';

        $query = "SELECT COUNT(A.image_id) AS nbimage
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                
                  INNER JOIN separation SEP ON(SEP.image_id=I.id) 
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id               
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini AND code =:codeEtape)
                  AND A.decouper = :decouper AND I.supprimer = :supprimer AND
                  c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userId' => $userId,
                'userRat' => $userId,
                'status' => 4,
                'saisie1' => 2,
                'saisie2' => 2,
                'decouper' => 0,
                'supprimer' => 0,
                'categorieId' => 16,
                'fini' => 0,
                'codeEtape'=> $code_etape,
            ));


        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** IMAGE DANS affectation tenue */

        $query = "SELECT COUNT(I.id) AS nbimage FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id
                JOIN separation s ON s.image_id=I.id AND s.categorie_id=a.categorie_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini ";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $images += $res[0]->nbimage;
        }


        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON (A.image_id = I.id)
                  JOIN separation SEP ON I.id=SEP.image_id
                  join lot l on I.lot_id=l.id join dossier d on l.dossier_id=d.id 
                  where SEP.categorie_id!= :categorieId AND d.site_id in (select id from site where client_id in
                  (select client from responsable_client rc join rattachement r on rc.responsable=r.operateur_id where r.operateur_rat_id=:userId))
                                  AND A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 AND 
                                  A.decouper = :decouper  AND I.saisie1 = :isaisie1 AND I.saisie2 =:isaisie2
                                  AND I.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 4,
            'saisie1' => 0,
            'saisie2' => 0,
            'isaisie1' => 3,
            'isaisie2' => 3,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);

        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }

        /** NOMBRE LOT DANS AFFECTATION TENUE*/
        $query = "SELECT COUNT(DISTINCT a.lot_id) AS nblot FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $lots += $res[0]->nblot;
        }
        return $images;
    }


    public function imageImputationTenue(&$lots, $userId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'IMP';
        $query = "SELECT COUNT(it.id) AS nbimage FROM image_a_traiter it JOIN image i ON it.image_id=i.id 
                  JOIN separation SEP ON SEP.image_id=i.id
                  JOIN lot l ON i.lot_id=l.id JOIN dossier d ON l.dossier_id=d.id 
                  WHERE SEP.categorie_id != :categorieId AND d.site_id IN (SELECT id FROM site WHERE client_id IN
                  (SELECT rc.client FROM responsable_client rc JOIN rattachement r ON rc.responsable=r.operateur_id 
                  WHERE r.operateur_rat_id = :userId))
                    AND it.status = :status AND it.decouper = :decouper
                  AND i.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 6,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }
        /** IMAGE DANS affectation tenue */

        $query = "SELECT COUNT(I.id) AS nbimage FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id
                JOIN separation s ON s.image_id=I.id AND s.categorie_id=a.categorie_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini ";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $images += $res[0]->nbimage;
        }

        /* NOMBRE LOT IMPUTATION */
        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON (A.image_id = I.id)
                  JOIN separation SEP ON I.id=SEP.image_id
                  join lot l on I.lot_id=l.id join dossier d on l.dossier_id=d.id 
                  where SEP.categorie_id!= :categorieId AND d.site_id in (select id from site where client_id in
                  (select client from responsable_client rc join rattachement r on rc.responsable=r.operateur_id where r.operateur_rat_id=:userId))
                                  AND A.status = :status  AND 
                                  A.decouper = :decouper  AND I.supprimer = :supprimer";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'status' => 6,
            'decouper' => 0,
            'categorieId' => 16,
            'supprimer' => 0,
        ]);

        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }

        /** NOMBRE LOT DANS AFFECTATION TENUE*/
        $query = "SELECT COUNT(DISTINCT a.lot_id) AS nblot FROM affectation_panier_tenue a INNER JOIN image I ON I.lot_id=a.lot_id 
                WHERE a.code=:codeEtape AND a.operateur_id=:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'userId' => $userId,
            'codeEtape' => $code_etape,
            'fini' => 0,
        ]);
        if ( count($res) > 0 ) {
            $lots += $res[0]->nblot;
        }

        return $images;
    }

    public function imageImputation( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(id) AS nbimage FROM image_a_traiter WHERE status = :status AND decouper = :decouper";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 6,
            'decouper' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id) 
                  WHERE A.status = :status AND A.decouper = :decouper";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 6,
            'decouper' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }

    public function imageCtrlImputation( &$lots )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** IMAGES */
        $query = "SELECT COUNT(id) AS nbimage FROM image_a_traiter WHERE status = :status AND decouper = :decouper";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 8,
            'decouper' => 0,
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        /** LOTS */
        $query = "SELECT COUNT(DISTINCT I.lot_id) AS nblot FROM image_a_traiter A INNER JOIN image I ON(A.image_id = I.id) 
                  WHERE A.status = :status AND A.decouper = :decouper";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 8,
            'decouper' => 0,
        ]);
        $res = $prep->fetchAll();
        $lots = 0;
        if ( count($res) > 0 ) {
            $lots = $res[0]->nblot;
        }
        return $images;
    }

    public function categorieSeparation( EtapeTraitement $etape )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $etape_code = $etape->getCode();

        switch ( $etape_code ) {
            case 'OS_1' :
                $col_etape = 'saisie1';
                break;
            case 'OS_2' :
                $col_etape = 'saisie2';
                break;
            case 'CTRL_OS' :
                $col_etape = 'ctrlSaisie';
                break;
            case 'IMP' :
                $col_etape = 'imputation';
                break;
            case 'CTRL_IMP' :
                $col_etape = 'ctrlImputation';
                break;
        }

        $query = "SELECT DISTINCT categorie_id FROM partage_suivi WHERE status = :status AND etape_traitement_id = :etape_id";
        $prep = $pdo->prepare($query);
        $prep->execute([
            'status' => 0,
            'etape_id' => $etape->getId(),
        ]);
        $res = $prep->fetchAll();
        $cat = [];
        $cat[] = -1;

        for ( $i = 0; $i < count($res); $i++ ) {
            $cat[] = $res[$i]->categorie_id;
        }

        $cat_id = $this->getEntityManager()
                       ->getRepository('AppBundle:PartageSuivi')
                       ->createQueryBuilder('partage')
                       ->select('DISTINCT(partage.categorie)')
                       ->where('partage.status = :status AND partage.etapeTraitement = :etape_traitement_id')
                       ->setParameter('status', 0)
                       ->setParameter('etape_traitement_id', $etape->getId())
                       ->getQuery()
                       ->getArrayResult();
        $cat = [];
        $cat[] = -1;

        foreach ( $cat_id as $id_cat ) {
            $cat[] = $id_cat[1];
        }
        $qb = $this->getEntityManager()
                   ->getRepository('AppBundle:Image')
                   ->createQueryBuilder('img');

        $categories = $qb
            ->select('img as image, cat.id as categorie_id,  cat.libelle as categorie')
            ->where("img.$col_etape = :col_status AND img.decouper = 0")
            ->innerJoin('AppBundle\Entity\Lot', 'lot', 'WITH', 'img.lot = lot.id')
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.image = img.id')
            ->innerJoin('AppBundle\Entity\Categorie', 'cat', 'WITH', 'sep.categorie = cat.id')
            ->andWhere('cat.id IN (:partage_cat)')
            ->setParameter('col_status', 0)
            ->setParameter('partage_cat', $cat)
            ->getQuery()
            ->getResult();

        return $categories;
    }

    public function categorieCtrlSaisie()
    {
        $qb = $this->getEntityManager()
                   ->getRepository('AppBundle:Image')
                   ->createQueryBuilder('img');

        $categories = $qb
            ->select('img as image, cat.id as categorie_id,  cat.libelle as categorie')
            ->where("img.saisie1 = 3 AND img.saisie2 = 3 AND img.ctrlSaisie = 0 AND img.decouper = 0")
            ->innerJoin('AppBundle\Entity\Lot', 'lot', 'WITH', 'img.lot = lot.id')
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.image = img.id')
            ->innerJoin('AppBundle\Entity\Categorie', 'cat', 'WITH', 'sep.categorie = cat.id')
            ->getQuery()
            ->getResult();

        return $categories;
    }

    public function categorieImputation()
    {
        $qb = $this->getEntityManager()
                   ->getRepository('AppBundle:Image')
                   ->createQueryBuilder('img');

        $categories = $qb
            ->select('img as image, cat.id as categorie_id,  cat.libelle as categorie')
            ->where("img.ctrlSaisie = 3 AND img.imputation = 0 AND img.decouper = 0")
            ->innerJoin('AppBundle\Entity\Lot', 'lot', 'WITH', 'img.lot = lot.id')
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.image = img.id')
            ->innerJoin('AppBundle\Entity\Categorie', 'cat', 'WITH', 'sep.categorie = cat.id')
            ->getQuery()
            ->getResult();

        return $categories;
    }

    public function categorieCtrlImputation()
    {
        $qb = $this->getEntityManager()
                   ->getRepository('AppBundle:Image')
                   ->createQueryBuilder('img');

        $categories = $qb
            ->select('img as image, cat.id as categorie_id,  cat.libelle as categorie')
            ->where("img.imputation = 3 AND img.ctrlImputation = 0 AND img.decouper = 0")
            ->innerJoin('AppBundle\Entity\Lot', 'lot', 'WITH', 'img.lot = lot.id')
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.image = img.id')
            ->innerJoin('AppBundle\Entity\Categorie', 'cat', 'WITH', 'sep.categorie = cat.id')
            ->getQuery()
            ->getResult();

        return $categories;
    }

    public function categoriePanier( EtapeTraitement $etape )
    {
        $qb = $this->getEntityManager()
                   ->getRepository('AppBundle:Panier')
                   ->createQueryBuilder('panier');

        $etape_code = $etape->getCode();
        switch ( $etape_code ) {
            case 'OS_1' :
                $col_etape = 'saisie1';
                break;
            case 'OS_2' :
                $col_etape = 'saisie2';
                break;
            case 'CTRL_OS' :
                $col_etape = 'ctrlSaisie';
                break;
            case 'IMP' :
                $col_etape = 'imputation';
                break;
            case 'CTRL_IMP' :
                $col_etape = 'ctrlImputation';
                break;
        }

        $categories = $qb
            ->select('panier, categ as categorie')
            ->innerJoin('panier.categorie', 'categ')
            ->innerJoin('panier.image', 'img', 'WITH', "img.$col_etape = 1 AND img.decouper = 0")
            ->where('panier.etapeTraitement = :etape')
            ->setParameter('etape', $etape)
            ->getQuery()
            ->getResult();

        return $categories;
    }

    public function imagePerCateg( Lot $lot, EtapeTraitement $etape )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $etape_code = $etape->getCode();

        $query = "SELECT COUNT(I.id) AS nbimage,cat.id AS categorie_id,cat.libelle AS categorie,
                  L.date_scan AS date_scan, I.exercice AS exercice,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture, S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client
                  FROM separation sep
                  INNER JOIN image_a_traiter A ON(A.image_id = sep.image_id)
                  INNER JOIN categorie cat ON(sep.categorie_id = cat.id)
                  INNER JOIN image I ON(sep.image_id = I.id)
                  INNER JOIN lot L ON(I.lot_id = L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON(D.site_id = S.id)
                  INNER JOIN client C ON(S.client_id = C.id)
                  WHERE L.id = :lot_id";

        switch ( $etape_code ) {
            case 'OS_1' :
                $query .= " AND A.status = 4 AND A.saisie1 = 0";
                break;
            case 'OS_2' :
                $query .= " AND A.status = 4 AND A.saisie2 = 0";
                break;
            case 'CTRL_OS' :
                $query .= " AND A.status = 4 AND A.saisie1 = 2 AND A.saisie2 = 2";
                break;
            case 'IMP' :
                $query .= " AND A.status = 6";
                break;
            case 'CTRL_IMP' :
                $query .= " AND A.status = 8";
                break;
        }

        $query .= " GROUP BY cat.id
                  ORDER BY cat.libelle";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'lot_id' => $lot->getId(),
        ));
        $categories = $prep->fetchAll();
        foreach ( $categories as $categorie ) {
            $priorite = $this->getEntityManager()
                             ->getRepository('AppBundle:PrioriteLot')
                             ->getPrioriteDossier($lot->getDossier());
            $categorie->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
            $categorie->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
            $categorie->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
            $categorie->order = isset($priorite['order']) ? $priorite['order'] : 9000;
        }

        return $categories;
    }

    /**
     * @param $exercice
     * @param string $periode "TODAY"|"WEEK"|"MONTH"|"TOTAL"
     *
     * @return int
     */
    public function imageRecuExercice( $exercice, $periode )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $nbimage = 0;
        switch ( $periode ) {
            case "TODAY":
                $now = new \DateTime();
                $query = "SELECT COUNT(I.id) as nbimage FROM image I INNER JOIN lot L ON(I.lot_id = I.id)
                          WHERE L.date_scan = :now AND I.exercice = :exercice
                          AND I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'exercice' => $exercice,
                    'now' => $now->format('Y-m-d'),
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            case "WEEK":
                $now = new \DateTime();
                $monday = date('Y-m-d', strtotime('monday this week'));
                $query = "SELECT COUNT(I.id) as nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id)
                          WHERE L.date_scan BETWEEN :monday AND :now AND I.exercice = :exercice
                          AND I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'exercice' => $exercice,
                    'monday' => $monday,
                    'now' => $now->format('Y-m-d'),
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            case "MONTH":
                $now = new \DateTime();
                $debut_mois = $now->format('Y-m-01');
                $query = "SELECT COUNT(I.id) as nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id)
                          WHERE L.date_scan BETWEEN :debut_mois AND :now AND I.exercice = :exercice 
                          AND I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'exercice' => $exercice,
                    'debut_mois' => $debut_mois,
                    'now' => $now->format('Y-m-d'),
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            case "TOTAL":
                $query = "SELECT COUNT(I.id) as nbimage FROM image I
                          WHERE I.exercice = :exercice AND I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'exercice' => $exercice,
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            default:
                break;
        }
        if ( isset($prep) && $prep instanceof \PDOStatement ) {
            $res = $prep->fetchAll();
            if ( count($res) > 0 ) {
                $nbimage = $res[0]->nbimage;
            }
        }
        return $nbimage;
    }

    /**
     * @param int $diff différence en jour par rapport à aujourd'hui
     * @param string $type "ALL"|"DOWNLOADED"|"NOT_DOWNLOADED"
     *
     * @return int
     */
    public function imageRecuJour( $diff, $type )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $nbimage = 0;
        $date = new \DateTime();
        $date->setTime(0, 0);
        if ( $diff != 0 ) {
            if ( $diff > 0 ) {
                $date->add(new \DateInterval('P' . $diff . 'D'));
            } else {
                $date->sub(new \DateInterval('P' . abs($diff) . 'D'));
            }
        }

        switch ( $type ) {
            case "ALL":
                $query = "SELECT COUNT(I.id) as nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id)
                          WHERE L.date_scan = :datescan AND I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'datescan' => $date->format('Y-m-d'),
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            case "DOWNLOADED":
                $query = "SELECT COUNT(I.id) as nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id)
                          WHERE L.date_scan = :datescan AND I.download IS NOT NULL AND 
                          I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'datescan' => $date->format('Y-m-d'),
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            case "NOT_DOWNLOADED":
                $query = "SELECT COUNT(I.id) as nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id)
                          WHERE L.date_scan = :datescan AND I.download IS NULL AND 
                          I.decouper = :decouper AND I.supprimer = :supprimer";
                $prep = $pdo->prepare($query);
                $prep->execute(array(
                    'datescan' => $date->format('Y-m-d'),
                    'decouper' => 0,
                    'supprimer' => 0,
                ));
                break;
            default:
                break;
        }
        if ( isset($prep) && $prep instanceof \PDOStatement ) {
            $res = $prep->fetchAll();
            if ( count($res) > 0 ) {
                $nbimage = $res[0]->nbimage;
            }
        }
        return $nbimage;
    }

    public function getSituationsImagesBySeparation( $param )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $data = array();
        switch ( $param['cas'] ) {
            case 1:
                $periode = "AND l.date_scan = '" . $param['aujourdhui'] . "' ";
                $periodeStockNow = "AND l_nw.date_scan = '" . $param['aujourdhui'] . "' ";
                break;
            case 4:
                $periode = " ";
                $periodeStockNow = " ";

                break;
            default:
                $periode = "AND l.date_scan >= '" . $param['dateDeb'] . "'";
                $periode .= " AND l.date_scan <= '" . $param['dateFin'] . "' ";

                $periodeStockNow = "AND l_nw.date_scan >= '" . $param['dateDeb'] . "'";
                $periodeStockNow .= " AND l_nw.date_scan <= '" . $param['dateFin'] . "' ";
        }

        if ( $param['client'] == 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = " ";
            $clientOrDossierStockNow = " ";
        } else if ( $param['client'] == 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";

            $clientOrDossierStockNow = "AND d_nw.id = ";
            $clientOrDossierStockNow .= $param['dossier'] . " ";
        } else if ( $param['client'] != 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierStockNow = "AND c_nw.id IN ( '" . implode("', '", $param['client']) . "' )";
        } else if ( $param['client'] != 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossier .= " AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";

            $clientOrDossierStockNow = "AND c_nw.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierStockNow .= " AND d_nw.id = ";
            $clientOrDossierStockNow .= $param['dossier'] . " ";
        }

        //get le nombre image
        $query = "SELECT count(sep.image_id) as nb_image, scat.libelle_new,
                (SELECT count(sep_nw.image_id) as nb_stock
                FROM separation sep_nw
                inner join image i_nw on sep_nw.image_id=i_nw.id
                inner join lot l_nw on i_nw.lot_id=l_nw.id
                inner join dossier d_nw on d_nw.id=l_nw.dossier_id
                inner join site s_nw ON d_nw.site_id = s_nw.id
                inner join client c_nw on c_nw.id = s_nw.client_id 
                inner join categorie cat_nw on cat_nw.id=sep_nw.categorie_id
                left join souscategorie scat_nw on scat_nw.id=sep_nw.souscategorie_id
                left join soussouscategorie sscat_nw on sscat_nw.id=sep_nw.soussouscategorie_id
                where cat_nw.code='CODE_BANQUE'
                and i_nw.exercice = " . $param['exercice'] . "
                and c.status = 1
                and i_nw.saisie1 <= 1
                and i_nw.saisie2 <= 1
                and i_nw.ctrl_saisie <= 1
                and i_nw.imputation <= 1
                and i_nw.ctrl_imputation <= 1
                and i_nw.status < 2
                " . $periodeStockNow . "
                " . $clientOrDossierStockNow . "
                and sep_nw.soussouscategorie_id = sep.soussouscategorie_id
                group by sep_nw.soussouscategorie_id
                order by sscat_nw.libelle_new) as nb_stock
                FROM separation sep
                inner join image i on sep.image_id=i.id
                inner join lot l on i.lot_id=l.id
                inner join dossier d on d.id=l.dossier_id
                inner join site s ON d.site_id = s.id
                inner join client c on c.id = s.client_id 
                inner join categorie cat on cat.id=sep.categorie_id
                left join souscategorie scat on scat.id=sep.souscategorie_id
                left join soussouscategorie sscat on sscat.id=sep.soussouscategorie_id
                where cat.code='CODE_BANQUE'
                and i.exercice = " . $param['exercice'] . "
                and c.status = 1
                and i.saisie1 <= 1
                and i.saisie2 <= 1
                and i.ctrl_saisie <= 1
                and i.imputation <= 1
                and i.ctrl_imputation <= 1
                " . $periode . "
                " . $clientOrDossier . "
                group by sep.soussouscategorie_id
                order by scat.libelle_new";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $data['situation_image'] = $prep->fetchAll();

        //banque stock aujourd'hui
        $query = "SELECT count(i.id) as nbr_image_banque FROM image i
            inner join lot l on i.lot_id=l.id
            inner join dossier d on d.id=l.dossier_id
            inner join site s ON d.site_id = s.id
            inner join client c on c.id = s.client_id 
            inner join separation sep on sep.image_id=i.id
            inner join categorie cat on cat.id=sep.categorie_id
            where cat.code='CODE_BANQUE'
            and i.saisie1 <= 1
            and i.saisie2 <= 1
            and i.ctrl_saisie <= 1
            and i.imputation <= 1
            and i.ctrl_imputation <= 1
            and i.supprimer = 0
            and i.exercice = :exercice
            and c.status = 1
            " . $periode . "
            " . $clientOrDossier . "";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'exercice' => $param['exercice'],
        ));
        $data_banque_stock = $prep->fetchAll();
        $banque_stock = $data_banque_stock[0]->nbr_image_banque;

        $data['banque_stock'] = $banque_stock;

        return $data;
    }

    public function getNombreImage( $param, $critere )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $data = array();
        $nbImageCat = array();
        $nbImageSousCat = array();

        switch ( $param['cas'] ) {
            case 1:
                $periode = "AND L.date_scan = :periode";
                break;
            case 4:
                $periode = " ";

                break;
            default:
                $periode = "AND L.date_scan >= ";
                $periode .= ":dateDeb";
                $periode .= " AND L.date_scan <= ";
                $periode .= ":dateFin";
        }

        if ( $param['client'] == 0 ) {
            $clientOrDossier = " ";
        } else if ( $param['client'] !== 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = "C.id = ";
            $clientOrDossier .= $param['client'] . " AND";
        } else if ( $param['dossier'] !== 0 ) {
            $clientOrDossier = "D.id = ";
            $clientOrDossier .= $param['dossier'] . " AND";
        }

        $query = "SELECT count(I.id) as tota
                  FROM image I 
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)";
        //recu
        if ( $critere == 1 ) {
            $query .= " WHERE " . $clientOrDossier . " I.exercice = :exercice AND I.download IS NOT NULL " . $periode;
        }
        //separe
        if ( $critere == 2 ) {
            $query .= " INNER JOIN separation E ON  (E.image_id = I.id) WHERE " . $clientOrDossier . " I.exercice = :exercice " . $periode;
        }
        //saisi
        if ( $critere == 3 ) {
            $query .= " WHERE " . $clientOrDossier . " I.exercice = :exercice AND I.ctrl_saisie >=2 " . $periode;
        }
        //impute
        if ( $critere == 4 ) {
            $query .= " WHERE " . $clientOrDossier . " I.exercice = :exercice AND I.ctrl_imputation >=2 " . $periode;
        }
        if ( $critere == 5 ) {

            $query .= " INNER JOIN panier P ON (P.image_id = I.id)  WHERE " . $clientOrDossier . " I.exercice = :exercice AND (P.status <> 0) " . $periode;
        }
        if ( $critere == 6 ) {
            if ( $param['client'] == 0 && $param['dossier'] == 0 ) {
                $query .= " WHERE  I.exercice = :exercice AND C.status = 1 " . $periode;

            }
        }


        $prep = $pdo->prepare($query);

        switch ( $param['cas'] ) {
            case 1:
                $now = $param['aujourdhui'];
                $prep->execute(array(
                    'exercice' => $param['exercice'],
                    'periode' => $now,

                ));
                $data['arrive'] = $now;
                break;
            case 4:
                $prep->execute(array(
                    'exercice' => $param['exercice'],
                ));

                break;
            default:
                $dateDeb = $param['dateDeb'];
                $dateFin = $param['dateFin'];
                if ( isset($param['dateFin']) && isset($param['dateFin']) ) {
                    $prep->execute(array(
                        'exercice' => $param['exercice'],
                        'dateDeb' => $dateDeb,
                        'dateFin' => $dateFin,
                    ));
                } else {
                    $prep->execute(array(
                        'exercice' => $param['exercice']));
                }

        }
        $resultat = $prep->fetchAll();

        if ( isset($resultat[0]) ) {
            return $resultat[0]->tota;
        } else {
            return $resultat[0] = 0;
        }

        return $resultat[0] = 0;

    }

    //Note de frais

    /**
     * @param $dossier
     * @param $exercice
     * @param $listeSoucategorie
     * @return int
     */
    public function getNombreImageBySousCategories( $dossier, $exercice, $listeSoucategorie )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $in = implode(",", $listeSoucategorie);

        $query = "SELECT COUNT(I.id) AS nbimage FROM image I INNER JOIN lot L ON(I.lot_id = L.id) 
                  INNER JOIN separation S on I.id = S.image_id INNER JOIN saisie1 s2 on I.id = s2.image_id
                  WHERE I.exercice = :exercice AND L.dossier_id = :dossier AND S.souscategorie_id IN (" . $in . ")";


        $prep = $pdo->prepare($query);
        $prep->execute([
            'exercice' => $exercice,
            'dossier' => $dossier
        ]);
        $res = $prep->fetchAll();
        $images = 0;
        if ( count($res) > 0 ) {
            $images = $res[0]->nbimage;
        }

        return $images;
    }

    /**
     * @param $dossier
     * @param $exercice
     * @param $listeSoucategorie
     * @param int $rapproche 1: rapprochée, 0: non rapprochée, 2: tous
     * @param \DateTime $periodeDebut
     * @param \DateTime $periodeFin
     * @return array
     */
    public function getListeImageRapprochee( $dossier, $exercice, $listeSoucategorie, $rapproche = 0, $periodeDebut = null, $periodeFin = null )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $in = implode(",", $listeSoucategorie);

        $query = "SELECT DISTINCT i.id AS image_id FROM image i INNER JOIN lot l ON(i.lot_id = l.id) 
                    INNER JOIN separation sep on i.id = sep.image_id INNER JOIN saisie1 saisie on i.id = saisie.image_id                  
                    WHERE i.exercice = :exercice AND l.dossier_id = :dossier AND sep.souscategorie_id IN (" . $in . ")";

        switch ( $rapproche ) {
            case 1:
//                $query .= " AND i.image_flague_id IS NOT null";
                $query .= " AND i.imputation > 1";
                break;
            case 0:
//                $query .= " AND i.image_flague_id IS null";
                $query .= " AND i.imputation <= 1";
                break;
            default:
                break;
        }

        $param = ['exercice' => $exercice, 'dossier' => $dossier];

        if ( $periodeDebut !== null && $periodeFin !== null ) {
            $query .= " AND saisie.date_facture >= :periodeDebut AND saisie.date_facture <= :periodeFin";

            $param['periodeDebut'] = $periodeDebut->format('y-m-d');
            $param['periodeFin'] = $periodeFin->format('y-m-d');
        }

        $prep = $pdo->prepare($query);
        $prep->execute(
            $param
        );
        $res = $prep->fetchAll();

        $images = array();
        foreach ( $res as $image ) {
            $images[] = $image->image_id;
        }


        return $images;
    }

    public function getListeImpute( $param )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $data = array();

        //client dossier principal
        if ( $param['client'] == 0 && $param['dossier'] == 0 ) {
            $clientOrDossier          = " ";
            $clientOrDossierPm        = " ";
            $clientOrDossierChq       = " ";
            $clientOrDossierLettree   = " ";
            $clientOrDossierClef      = " ";
            $clientOrDossierMois      = " ";
            $clientOrDossierALettrer  = " ";
            $clientOrDossierEcrChange = " ";
        } else if ( $param['client'] == 0 && $param['dossier'] != 0 ) {
            $clientOrDossier          = "AND bc.dossier_id = ";
            $clientOrDossier         .= $param['dossier'] . " ";
 
            $clientOrDossierPm        = "AND bcp.dossier_id = ";
            $clientOrDossierPm       .= $param['dossier'] . " ";

            $clientOrDossierChq       = "AND bccq.dossier_id = ";
            $clientOrDossierChq      .= $param['dossier'] . " ";

            $clientOrDossierLettree   = "AND bcle.dossier_id = ";
            $clientOrDossierLettree  .= $param['dossier'] . " ";

            $clientOrDossierClef      = "AND bcc.dossier_id = ";
            $clientOrDossierClef     .= $param['dossier'] . " ";

            $clientOrDossierMois      = "AND rmm.dossier_id = ";
            $clientOrDossierMois     .= $param['dossier'] . " ";

            $clientOrDossierALettrer  = "AND bcale.dossier_id = ";
            $clientOrDossierALettrer .= $param['dossier'] . " ";

            $clientOrDossierEcrChange  = "AND bcechg.dossier_id = ";
            $clientOrDossierEcrChange .= $param['dossier'] . " ";
        } else if ( $param['client'] != 0 && $param['dossier'] == 0 ) {
            $clientOrDossier         = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierPm       = "AND cp.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierChq      = "AND ccq.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierLettree  = "AND cle.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierClef     = "AND cc.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierMois     = "AND cm.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierALettrer = "AND cale.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierEcrChange = "AND cechg.id IN ( '" . implode("', '", $param['client']) . "' )";
        } else if ( $param['client'] != 0 && $param['dossier'] != 0 ) {
            $clientOrDossier          = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossier         .= " AND bc.dossier_id = ";
            $clientOrDossier         .= $param['dossier'] . " ";

            $clientOrDossierPm        = "AND cp.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierPm       .= " AND bcp.dossier_id = ";
            $clientOrDossierPm       .= $param['dossier'] . " ";

            $clientOrDossierChq       = "AND ccq.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierChq      .= " AND bccq.dossier_id = ";
            $clientOrDossierChq      .= $param['dossier'] . " ";

            $clientOrDossierLettree   = "AND cle.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierLettree  .= " AND bcle.dossier_id = ";
            $clientOrDossierLettree  .= $param['dossier'] . " ";

            $clientOrDossierClef      = "AND cc.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierClef     .= " AND bcc.dossier_id = ";
            $clientOrDossierClef     .= $param['dossier'] . " ";

            $clientOrDossierMois      = "AND cm.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierMois     .= " AND rmm.dossier_id = ";
            $clientOrDossierMois     .= $param['dossier'] . " ";

            $clientOrDossierALettrer  = "AND cale.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierALettrer .= " AND bcale.dossier_id = ";
            $clientOrDossierALettrer .= $param['dossier'] . " ";

            $clientOrDossierEcrChange  = "AND cechg.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierEcrChange .= " AND bcechg.dossier_id = ";
            $clientOrDossierEcrChange .= $param['dossier'] . " ";
        }

        //get imputees
        $query = "select count(*) as nb_r, d.status, d.status_debut, b.nom as banque, c.nom as clients, d.nom as dossier, d.cloture, bc.id as banquecompte_id, d.id as dossier_id, c.id as client_id,
            (case
                when length(bc.numcompte) >= 11 then substring(bc.numcompte, length(bc.numcompte)-10, length(bc.numcompte))
                else bc.numcompte
            end) as comptes, rtva.libelle as regime_tva, bc.numcompte, bc.etat, i.valider, bc.id as banque_compte_id, d.tva_date as ech, d.tva_mode, d.debut_activite,
            
            (select rmm.mois
            from releve rm
            inner join releve_manquant rmm on rmm.banque_compte_id = rm.banque_compte_id
            inner join image im on im.id = rm.image_id
            inner join lot lm on (lm.id = im.lot_id)
            inner join dossier dm on (dm.id = lm.dossier_id)
            inner join site sm on sm.id=dm.site_id
            inner join client cm on cm.id=sm.client_id
            inner join banque_compte bcm on (bcm.id = rm.banque_compte_id and bcm.dossier_id = dm.id)
            inner join banque bm on (bm.id=bcm.banque_id)
            inner join separation sepm on (sepm.image_id = im.id)  
            inner join souscategorie sscm on (sepm.souscategorie_id = sscm.id) 
            where rmm.exercice = " . $param['exercice'] . "
            and rm.banque_compte_id=r.banque_compte_id 
            and cm.status = 1
            and rm.operateur_id is null
            and sepm.souscategorie_id IS NOT NULL 
            and sscm.id = 10 
            " . $clientOrDossierMois . "
            group by bcm.numcompte) as mois,

            (select count(*) as nb_c
            from image ic
            left join releve rc on (ic.id = rc.image_id)
            inner join lot lc on (lc.id = ic.lot_id)
            inner join dossier dc on (dc.id = lc.dossier_id)
            inner join site sc on (sc.id = dc.site_id)
            inner join client cc on (cc.id = sc.client_id)
            inner join banque_compte bcc on (bcc.id = rc.banque_compte_id and bcc.dossier_id = dc.id)
            inner join banque bc on (bc.id=bcc.banque_id)
            inner join separation sepc on (sepc.image_id = ic.id)  
            inner join souscategorie sscc on (sepc.souscategorie_id = sscc.id) 
            where ic.supprimer = 0 
            and (rc.cle_dossier_id is not null)
            and ic.exercice = " . $param['exercice'] . "
            and rc.banque_compte_id=r.banque_compte_id 
            and cc.status = 1
            and rc.operateur_id is null
            and rc.image_flague_id is null
            and sepc.souscategorie_id IS NOT NULL 
            and sscc.id = 10 
            " . $clientOrDossierClef . "
            group by bcc.numcompte) as nb_clef,
            
            (select count(*) as nb_rle
            from image ile
            left join releve rle on (ile.id = rle.image_id)
            inner join lot lle on (lle.id = ile.lot_id)
            inner join dossier dle on (dle.id = lle.dossier_id)
            inner join site sle on (sle.id = dle.site_id)
            inner join client cle on (cle.id = sle.client_id)
            inner join banque_compte bcle on (bcle.id = rle.banque_compte_id and bcle.dossier_id = dle.id)
            inner join banque ble on (ble.id=bcle.banque_id)
            inner join separation seple on (seple.image_id = ile.id)  
            inner join souscategorie sscle on (seple.souscategorie_id = sscle.id) 
            where ile.supprimer = 0 
            and rle.image_flague_id is not null
            and ile.exercice = " . $param['exercice'] . "
            and rle.banque_compte_id=r.banque_compte_id 
            and cle.status = 1
            and rle.operateur_id is null
            and seple.souscategorie_id IS NOT NULL 
            AND ((rle.image_flague_id IN (SELECT bsca.image_flague_id FROM banque_sous_categorie_autre bsca  where bsca.compte_tiers_id is not null or bsca.compte_bilan_id is not null or bsca.compte_tva_id is not null or bsca.compte_chg_id is not null))
            OR  (rle.image_flague_id IN (SELECT tic.image_flague_id FROM tva_imputation_controle tic where tic.tiers_id is not null or tic.pcc_bilan_id is not null or tic.pcc_tva_id is not null))
            OR (rle.image_flague_id IN (SELECT rlele.image_flague_id FROM releve rlele where rlele.operateur_id IS NULL and rlele.id <> rle.id)))
            and sscle.id = 10 
            " . $clientOrDossierLettree . "
            group by bcle.numcompte) as nb_lettre,
            
            (SELECT count(*) as nb_rcq 
            from image icq
            left join releve rcq on (icq.id = rcq.image_id)
            inner join lot lcq on (lcq.id = icq.lot_id)
            inner join dossier dcq on (dcq.id = lcq.dossier_id)
            inner join site scq on (scq.id = dcq.site_id)
            inner join client ccq on (ccq.id = scq.client_id)
            inner join banque_compte bccq on (bccq.id = rcq.banque_compte_id and bccq.dossier_id = dcq.id)
            inner join banque bcq on (bcq.id=bccq.banque_id)
            inner join separation sepcq on (sepcq.image_id = icq.id)  
            inner join souscategorie ssccq on (sepcq.souscategorie_id = ssccq.id) 
            left join cle_dossier cldcq on (cldcq.id = rcq.cle_dossier_id)  
            where icq.supprimer = 0 
            and (rcq.libelle like '%CHQ%' OR rcq.libelle like '%CHEQUE%') 
            and (ROUND(rcq.credit - rcq.debit,2) < 0)
            and not (rcq.ecriture_change = 1 and rcq.maj = 3)
            and rcq.image_flague_id is null
            and icq.exercice = " . $param['exercice'] . "
            and rcq.banque_compte_id=r.banque_compte_id 
            and ccq.status = 1
            and rcq.operateur_id is null
            and sepcq.souscategorie_id IS NOT NULL 
            and ssccq.id = 10 
            and (rcq.cle_dossier_id is null or cldcq.pas_piece is null)
            " . $clientOrDossierChq . "
            group by bccq.numcompte) as chq_inconnu,

            (SELECT count(rale.id) as nb_alettrer
            from image iale
            left join releve rale on (iale.id = rale.image_id)
            inner join lot lale on (lale.id = iale.lot_id)
            inner join dossier dale on (dale.id = lale.dossier_id)
            inner join site sale on (sale.id = dale.site_id)
            inner join client cale on (cale.id = sale.client_id)
            inner join banque_compte bcale on (bcale.id = rale.banque_compte_id and bcale.dossier_id = dale.id)
            inner join banque bale on (bale.id=bcale.banque_id)
            inner join separation sepale on (sepale.image_id = iale.id)  
            inner join souscategorie sscale on (sepale.souscategorie_id = sscale.id) 
            where iale.supprimer = 0
            and iale.exercice = " . $param['exercice'] . "
            and cale.status = 1
            and rale.banque_compte_id=r.banque_compte_id 
            and rale.operateur_id is null
            and rale.flaguer = 1
            and sepale.souscategorie_id IS NOT NULL 
            and sscale.id = 10 
            " . $clientOrDossierALettrer . "
            group by bcale.numcompte) as a_lettrer,

            (SELECT count(*) as nb_ecriture_change 
            from image iechg
            left join releve rechg on (iechg.id = rechg.image_id)
            inner join lot lechg on (lechg.id = iechg.lot_id)
            inner join dossier dechg on (dechg.id = lechg.dossier_id)
            inner join site sechg on (sechg.id = dechg.site_id)
            inner join client cechg on (cechg.id = sechg.client_id)
            inner join banque_compte bcechg on (bcechg.id = rechg.banque_compte_id and bcechg.dossier_id = dechg.id)
            inner join banque bechg on (bechg.id=bcechg.banque_id)
            inner join separation sepechg on (sepechg.image_id = iechg.id)  
            inner join souscategorie sscechg on (sepechg.souscategorie_id = sscechg.id) 
            where iechg.supprimer = 0
            and iechg.exercice = " . $param['exercice'] . "
            and cechg.status = 1
            and rechg.banque_compte_id=r.banque_compte_id 
            and rechg.operateur_id is null
            and rechg.ecriture_change = 1
            and sepechg.souscategorie_id IS NOT NULL 
            and sscechg.id = 10 
            " . $clientOrDossierEcrChange . "
            group by bcechg.numcompte) as nb_ecriture_change

            from image i
            left join releve r on (r.image_id = i.id)
            inner join lot l on (l.id = i.lot_id)
            inner join dossier d on (l.dossier_id = d.id)
            inner join site s on (s.id = d.site_id)
            inner join client c on (c.id = s.client_id)
            inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
            inner join banque b on (b.id = bc.banque_id)
            inner join separation sep on (sep.image_id = i.id)  
            inner join souscategorie ssc on (sep.souscategorie_id = ssc.id) 
            left join regime_tva rtva on (d.regime_tva_id = rtva.id)
            where i.exercice = " . $param['exercice'] . " and i.supprimer = 0 
            and c.status = 1
            and r.operateur_id is null
            and sep.souscategorie_id IS NOT NULL 
            and ssc.id = 10 
            " . $clientOrDossier . "
            group by bc.numcompte";

        $prep = $pdo->prepare($query);
        $prep->execute();
        $data['imputees'] = $prep->fetchAll();
        return $data;
    }

    public function getExercicesByDossier( Dossier $dossier = null )
    {

        $exercices = $this
            ->createQueryBuilder('i')
            ->innerJoin('i.lot', 'lot')
            ->where('lot.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->distinct('i.exercice')
            ->select('i.exercice')
            ->orderBy('i.exercice', 'DESC')
            ->getQuery()
            ->getResult();

        $ret = [];
        foreach ( $exercices as $exercice ) {
            $ret[] = $exercice['exercice'];
        }

        return $ret;
    }

    public function getDateScans( Dossier $dossier, Souscategorie $souscategorie, Soussouscategorie $soussouscategorie = null, $exercice )
    {
        $dateScans = $this->createQueryBuilder('image')
                          ->innerJoin('image.lot', 'lot')
                          ->innerJoin('AppBundle:Separation', 'separation', 'WITH', 'separation.image = image')
                          ->where('lot.dossier = :dossier')
                          ->andWhere('image.exercice = :exercice')
                          ->andWhere('separation.souscategorie = :souscategorie')
                          ->setParameter('dossier', $dossier)
                          ->setParameter('souscategorie', $souscategorie)
                          ->setParameter('exercice', $exercice);


        if ( $soussouscategorie !== null ) {
            $dateScans = $dateScans
                ->andWhere('separation.soussouscategorie = :soussouscategorie')
                ->setParameter('soussouscategorie', $soussouscategorie);
        }

        $dateScans = $dateScans
            ->orderBy('lot.dateScan')
            ->distinct('lot.dateScan')
            ->select('lot.dateScan')
            ->getQuery();

        $dateScans = $dateScans->getResult();

        $ret = [];

        foreach ( $dateScans as $dateScan ) {
            $ret[] = $dateScan['dateScan'];
        }

        return $ret;
    }


    public function getSolde( $banquecompte_id, $exercice, $debut = true )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        $query = "SELECT SCTR.solde_debut,  SCTR.solde_fin FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        INNER JOIN saisie_controle SCTRL ON SCTRL.image_id = I.id
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  imputation_controle SCTR ON SCTR.image_id = I.id  
                        WHERE I.exercice= :exercice AND I.supprimer = 0 AND 
                        SCTRL.banque_compte_id = :banquecompte_id AND SC.id = 10
						ORDER BY SCTR.periode_d1, SCTR.periode_f1, I.nom";


        $param = ['banquecompte_id' => $banquecompte_id, 'exercice' => $exercice];
        $prep = $pdo->prepare($query);
        $prep->execute($param);

        $soldes = $prep->fetchAll();

        if ( count($soldes) > 0 ) {
            if ( $debut ) {
                return $soldes[0]->solde_debut;
            }
            return $soldes[count($soldes) - 1]->solde_fin;
        } else {
            return 0;
        }

    }

    public function getMouvement( $exercice, $banquecompte_id )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "select ROUND(SUM(r.credit) - SUM(r.debit),2) as mouvement from releve r                          
                inner join image i on i.id = r.image_id
                inner join separation sep on sep.image_id = i.id
                inner join lot l on l.id = i.lot_id
                inner join banque_compte bc on bc.id = r.banque_compte_id
                where i.exercice = :exercice
                and i.supprimer = 0
                and sep.souscategorie_id = 10
                and r.operateur_id is null               
                and (r.banque_compte_id IS NOT NULL)               
                and (bc.id = :banquecompte_id)";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'exercice' => $exercice,
            'banquecompte_id' => $banquecompte_id
        ));
        $mouvement = $prep->fetchAll();
        return $mouvement[0]->mouvement;
    }

    public function getTravauxAfaire( $param, $etat )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $data = array();

        if ( $param['client'] == 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = " ";
        } else if ( $param['client'] == 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";
        } else if ( $param['client'] != 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = "AND d.status = 1 AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
        } else if ( $param['client'] != 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossier .= " AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";
        }

        if ( $etat == 'total' ) {
            $query = "SELECT count(*) as nb
                    from image i
                    left join releve r on (r.image_id = i.id)
                    inner join lot l on (l.id = i.lot_id)
                    inner join dossier d on (l.dossier_id = d.id)
                    inner join site s on (s.id = d.site_id)
                    inner join client c on (c.id = s.client_id)
                    inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
                    inner join banque b on (b.id = bc.banque_id)
                    where i.exercice = :exercice and i.supprimer = 0 
                    and c.status = 1
                    " . $clientOrDossier . "
                    and r.operateur_id is null";
        }

        if ( $etat == 'lettre' ) {
            $query = "select count(*) as nb
                    from image i
                    left join releve r on (i.id = r.image_id)
                    inner join lot l on (l.id = i.lot_id)
                    inner join dossier d on (d.id = l.dossier_id)
                    inner join site s on (s.id = d.site_id)
                    inner join client c on (c.id = s.client_id)
                    inner join banque_compte bc on (bc.id = r.banque_compte_id and bc.dossier_id = d.id)
                    inner join banque b on (b.id=bc.banque_id)
                    where i.supprimer = 0 
                    and r.image_flague_id is not null
                    and i.exercice = :exercice
                    and c.status = 1
                    " . $clientOrDossier . "
                    and r.operateur_id is null";
        }

        if ( $etat == 'clef' ) {
            $query = "select count(*) as nb
                    from image i
                    left join releve r on (i.id = r.image_id)
                    inner join lot l on (l.id = i.lot_id)
                    inner join dossier d on (d.id = l.dossier_id)
                    inner join site s on (s.id = d.site_id)
                    inner join client c on (c.id = s.client_id)
                    inner join banque_compte bc on (bc.id = r.banque_compte_id and bc.dossier_id = d.id)
                    inner join banque b on (b.id=bc.banque_id)
                    where i.supprimer = 0 
                    and (r.cle_dossier_id is not null)
                    and i.exercice = :exercice
                    and c.status = 1
                    " . $clientOrDossier . "
                    and r.operateur_id is null";
        }

        if ( $etat == 'pc_manquant' ) {
            $query = "select count(*) as nb
                    from image i
                    left join releve r on (i.id = r.image_id)
                    inner join lot l on (l.id = i.lot_id)
                    inner join dossier d on (d.id = l.dossier_id)
                    inner join site s on (s.id = d.site_id)
                    inner join client c on (c.id = s.client_id)
                    inner join banque_compte bc on (bc.id = r.banque_compte_id and bc.dossier_id = d.id)
                    inner join banque b on (b.id=bc.banque_id)
                    inner join cle cle on (cle.id = r.cle_dossier_id)
                    where (r.image_flague_id is null or (r.cle_dossier_id is not null and cle.pas_piece = 1))
                    and i.supprimer = 0 
                    and (r.cle_dossier_id is null or cle.pas_piece <> 1)
                    and i.exercice = :exercice
                    and c.status = 1
                    " . $clientOrDossier . "
                    and r.operateur_id is null";
        }

        if ( $etat == 'cheque_inconnu' ) {
            $query = "SELECT count(*) as nb 
                    from image i
                    left join releve r on (i.id = r.image_id)
                    inner join lot l on (l.id = i.lot_id)
                    inner join dossier d on (d.id = l.dossier_id)
                    inner join site s on (s.id = d.site_id)
                    inner join client c on (c.id = s.client_id)
                    inner join banque_compte bc on (bc.id = r.banque_compte_id and bc.dossier_id = d.id)
                    inner join banque b on (b.id=bc.banque_id)
                    where i.supprimer = 0 
                    and (r.libelle like '%CHQ%' OR r.libelle like '%CHEQUE%') 
                    and (r.credit-r.debit < 0) 
                    and i.exercice = :exercice
                    and c.status = 1
                    " . $clientOrDossier . "
                    and r.operateur_id is null";
        }

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'exercice' => $param['exercice'],
        ));
        $travauxAfaire = $prep->fetchAll();
        return (count($travauxAfaire) > 0) ? $travauxAfaire[0]->nb : 0;
    }


    public function getInfoReleveByDossier( $banquecompte_id, $exercice )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT  DISTINCT b.nom AS banque_nom, bc.numcompte AS numcompte, bc.id AS banquecompte_id, 
                  ic.periode_d1 AS periode_deb, ic.periode_f1 AS periode_fin,
                  CASE WHEN r.num_releve IS NOT NULL THEN r.num_releve ELSE ic.num_releve END AS num_releve, 
                  ic.solde_debut AS solde_deb ,ic.solde_fin AS solde_fin, l.date_scan AS date_scan,
                  i.id AS image_id, i.nom AS image_nom, ssc.libelle AS soussouscategorie_libelle ,
                  0 AS image_id_suivant, 0 AS image_id_precedent, 0 AS releve_intermediaire, '' AS controle
                  FROM image i
                  LEFT JOIN releve r ON r.image_id = i.id
                  INNER JOIN lot l ON l.id = i.lot_id
                  INNER JOIN dossier d ON l.dossier_id = d.id
                  INNER JOIN site s ON s.id = d.site_id
                  INNER JOIN client c ON c.id = s.client_id
                  INNER JOIN imputation_controle ic ON ic.image_id = i.id
                  LEFT JOIN banque_compte bc ON ic.banque_compte_id = bc.id
                  LEFT JOIN banque b ON b.id = bc.banque_id
                  LEFT JOIN souscategorie sc ON sc.id = ic.souscategorie_id
                  LEFT JOIN soussouscategorie ssc ON ssc.id = ic.soussouscategorie_id
                  WHERE i.exercice = :exercice AND i.supprimer = 0  AND r.operateur_id IS NULL AND bc.id = :banquecompte_id AND (ssc.libelle NOT LIKE '%doublon%' OR ssc.libelle IS null ) AND sc.id = 10
                  ORDER BY ic.periode_d1,r.num_releve,i.nom";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'banquecompte_id' => $banquecompte_id,
            'exercice' => $exercice
        ));

        return $prep->fetchAll();

    }

    public function getImageBanque( $imageid )
    {

        $query = "SELECT I.*,S.souscategorie_id, '' AS avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTR ON SCTR.image_id = I.id  
                        WHERE I.id = " . $imageid;

        $query .= " ORDER BY SCTR.periode_d1, SCTR.periode_f1, I.nom";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute();

        $res = $prep->fetchAll();


        foreach ( $res as $re ) {

            $souscatid = $re->souscategorie_id;

            $imageE = $this->getEntityManager()
                           ->getRepository('AppBundle:Image')
                           ->find($re->id);

            //Releve

            $re->valider = 0;
            $re->avec_releve = 0;

            if ( intval($souscatid) === 10 ) {

                $avecReleve = $this->getEntityManager()
                                   ->getRepository('AppBundle:Releve')
                                   ->validerImageByReleve($imageE);

                if ( $avecReleve > 0 ) {
                    $re->avec_releve = 1;

                    if ( $avecReleve === 2 ) {
                        $re->valider = 100;
                    }
                }
            } else {

                $avecOb = $this->getEntityManager()
                               ->getRepository('AppBundle:BanqueSousCategorieAutre')
                               ->validerImageByOb($imageE);

                if ( $avecOb > 0 ) {
                    $re->avec_releve = 1;

                    if ( $avecOb === 2 ) {
                        $re->valider = 100;
                    }
                }

            }
        }

        return $res;
    }

//    public function getListImagePanierBanque( $dossierid, $exercice, $souscat, $soussouscat, $etape, $operateur, $banquecompteid = null )
    public function getListImagePanierBanque( $dossierid, $exercice, $souscat, $etape, $operateur, $banquecompteid = null )
    {

        if ( $etape !== 'BQ_DETAILS' ) {

            $query = "SELECT I.*, S.souscategorie_id FROM  image I 
                        INNER JOIN panier p ON p.image_id = I.id
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTR ON SCTR.image_id = I.id  
                        WHERE L.dossier_id = " . $dossierid . " AND I.saisie1 >= 1 
                        AND SC.id =" . $souscat . " 
                        AND I.supprimer = 0 AND I.exercice =" . $exercice . " 
                        AND I.decouper = 0
                        AND p.fini = 0 AND p.operateur_id = " . $operateur;

//            if ( intval($soussouscat) !== -1 ) {
//                //Raha ticket & virement ihany no misy soussouscategorie
//                if(intval($souscat) === 1 || intval($souscat) === 153) {
//                    $query .= " AND SSC.id =" . $soussouscat;
//                }
//            }

            if(intval($souscat) === 153){
                $query .= " AND SSC.id = 1905";
            }

            $query .= " ORDER BY SCTR.periode_d1, SCTR.periode_f1, I.nom";
        } else {
            $query = "SELECT I.*, '10' as souscategorie_id, '0' as avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN panier P ON I.id = P.image_id AND P.etape_traitement_id = 26 AND P.fini = 0
                        LEFT JOIN  saisie_controle SCTR ON SCTR.image_id = I.id  
                        WHERE L.dossier_id = " . $dossierid . " 
                        AND I.saisie1 > 0 AND I.supprimer = 0 
                        AND I.decouper = 0
                        AND I.exercice = " . $exercice;

            if ( $banquecompteid !== null ) {
                $query .= " AND SCTR.banque_compte_id = " . $banquecompteid;
            }

            $query .= " ORDER BY SCTR.periode_d1, SCTR.periode_f1, I.nom";
        }

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute();

        $res = $prep->fetchAll();


        foreach ( $res as $re ) {

            $souscatid = $re->souscategorie_id;

            $imageE = $this->getEntityManager()
                           ->getRepository('AppBundle:Image')
                           ->find($re->id);

            //Releve

            if ( intval($souscatid) === 10 ) {

                $re->valider = 0;
                $re->avec_releve = 0;

                $avecReleve = $this->getEntityManager()
                                   ->getRepository('AppBundle:Releve')
                                   ->validerImageByReleve($imageE);

                if ( $avecReleve > 0 ) {
                    $re->avec_releve = 1;

                    if ( $avecReleve === 2 ) {
                        $re->valider = 100;
                    }
                }
            } else {
                $avecOb = $this->getEntityManager()
                               ->getRepository('AppBundle:BanqueSousCategorieAutre')
                               ->validerImageByOb($imageE);

                if ( $avecOb > 0 ) {
                    $re->avec_releve = 1;

                    if ( $avecOb === 2 ) {
                        $re->valider = 100;
                    }
                }
            }
        }

        return $res;
    }

    public function getListImageBanque( $did, $exercice, $dscan, $souscat, $etape, $banquecompteid )
    {
        if ( $exercice == '' ) {
            $now = new \DateTime();
            $exercice = $now->format('Y');
        }

        if ( $etape == 1 ) {
            $where = "AND I.saisie1 > 0 ";
        } else {
            $where = "AND I.ctrl_saisie >= 2 ";
        }

        $banquecompte = null;

        if($banquecompteid) {

            //releve bancaire
            $banquecompte = $this->getEntityManager()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);
        }


        if ( !$banquecompte ) {
            $query = "SELECT I.*, S.souscategorie_id, '' AS avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTRL ON SCTRL.image_id = I.id  
                        WHERE L.dossier_id =" . $did . " AND I.exercice=" . $exercice . " AND I.supprimer = 0 " . $where;
        } else {
            $query = "SELECT I.*,S.souscategorie_id, '' AS avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTRL ON SCTRL.image_id = I.id  
                        WHERE L.dossier_id =" . $did . " AND I.exercice=" . $exercice . " AND I.supprimer = 0 AND 
                        SCTRL.banque_compte_id =" . $banquecompteid;
        }


        if ( $dscan <> 0 ) {
            $query .= " AND L.date_scan ='" . $dscan . "'";
        }

//        if ( $soussouscat != -1 && isset($soussouscat) ) {
//            $query .= " AND SSC.id =" . $soussouscat;
//        } else {
//            $query .= " AND SC.id =" . $souscat;
//        }

        $query .= " AND SC.id =" . $souscat;

        $query .= " ORDER BY SCTRL.periode_d1, SCTRL.periode_f1, I.nom";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute();

        $res = $prep->fetchAll();

//        if($etape === 'BQ_DETAILS') {
//            //jerena raha misy releves na tsia
//            foreach ($res as $re) {
//                $img = $this->getEntityManager()
//                    ->getRepository('AppBundle:Image')
//                    ->find($re->id);
//
//                $releves = $this->getEntityManager()
//                    ->getRepository('AppBundle:Releve')
//                    ->getRelevesByImage($img);
//
//                if(count($releves) > 0){
//                    $re->avec_releve = 1;
//                }
//            }
//        }


        foreach ( $res as $re ) {

            $souscatid = $re->souscategorie_id;

            $imageE = $this->getEntityManager()
                           ->getRepository('AppBundle:Image')
                           ->find($re->id);


            //Releve

            $re->valider = 0;
            $re->avec_releve = 0;

            if ( intval($souscatid) === 10 ) {

                $avecReleve = $this->getEntityManager()
                                   ->getRepository('AppBundle:Releve')
                                   ->validerImageByReleve($imageE);

                if ( $avecReleve > 0 ) {
                    $re->avec_releve = 1;

                    if ( $avecReleve === 2 ) {
                        $re->valider = 100;
                    }
                }
            } else {

                $avecOb = $this->getEntityManager()
                               ->getRepository('AppBundle:BanqueSousCategorieAutre')
                               ->validerImageByOb($imageE);

                if ( $avecOb > 0 ) {
                    $re->avec_releve = 1;

                    if ( $avecOb === 2 ) {
                        $re->valider = 100;
                    }
                }

            }
        }

        return $res;
    }

    public function getListImageByids( $ids )
    {
        $query = "SELECT I.*, '' AS avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        INNER JOIN saisie_controle SCTRL ON SCTRL.image_id = I.id
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTR ON SCTR.image_id = I.id  
                        WHERE I.id IN (" . $ids . ")";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute();

        return $prep->fetchAll();
    }

    /**
     * @param Image $image
     * @param \DateTime $periodeDebut
     * @param \DateTime $periodeFin
     * @return int
     * @throws \Exception
     */
    public function getExerciceByPeriode( Image $image, \DateTime $periodeDebut, \DateTime $periodeFin )
    {
        $exerice = $image->getExercice();
        $dossier = $image->getLot()->getDossier();

        $dateCloture = $this->getEntityManager()
                            ->getRepository('AppBundle:Dossier')
                            ->getDateCloture($dossier, $exerice);

        $dateDebutExercice = $this->getEntityManager()
                                  ->getRepository('AppBundle:Dossier')
                                  ->getDateDebut($dossier, $exerice);

        if ( $periodeDebut->setTime(0, 0, 0) >= $dateDebutExercice &&
             $periodeFin->setTime(0, 0, 0) <= $dateCloture ) {
            return $exerice;
        }

        $exerice = $periodeDebut->format('Y');
        $dateCloture = $this->getEntityManager()
                            ->getRepository('AppBundle:Dossier')
                            ->getDateCloture($dossier, $exerice);

        $dateDebutExercice = $this->getEntityManager()
                                  ->getRepository('AppBundle:Dossier')
                                  ->getDateDebut($dossier, $exerice);

        if ( $periodeDebut >= $dateDebutExercice->setTime(0, 0, 0) &&
             $periodeFin->setTime(0, 0, 0) <= $dateCloture ) {
            return $exerice;
        }

        $exerice = $periodeFin->format('Y');
        $dateCloture = $this->getEntityManager()
                            ->getRepository('AppBundle:Dossier')
                            ->getDateCloture($dossier, $exerice);

        $dateDebutExercice = $this->getEntityManager()
                                  ->getRepository('AppBundle:Dossier')
                                  ->getDateDebut($dossier, $exerice);

        if ( $periodeDebut->setTime(0, 0, 0) >= $dateDebutExercice &&
             $periodeFin->setTime(0, 0, 0) <= $dateCloture ) {
            return $exerice;
        }
        return $image->getExercice();
    }

    function saveFileExtension( $imageId, $ext, $page )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE image SET ext_image=:extImage, a_remonter=:aRemonter, nbpage=:nbpage WHERE id=:id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'extImage' => $ext,
            'aRemonter' => 1,
            'nbpage' => $page,
            'id' => $imageId,
        ));
        return true;
    }

    public function getCountBanqueCompte( $client, $dossier )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select count( distinct bc.id) as compte, count( distinct bc.dossier_id) as dossier
                  from imputation_controle ic
                  left join banque_compte bc on bc.id=ic.banque_compte_id 
                  inner join banque bq on bq.id = bc.banque_id 
                  inner join separation s on ic.image_id = s.image_id and s.souscategorie_id = 10
                  
                  ";

        if ( $dossier != 0 ) {
            $query .= " where bc.dossier_id = " . $dossier;
        } else {
            $query .= " inner join dossier d on (d.id = bc.dossier_id)
                        inner join site si on (si.id = d.site_id)
                        inner join client c on (c.id = si.client_id)
                        where c.id = " . $client;
        }

        $prep = $pdo->prepare($query);

        $prep->execute();

        $result = $prep->fetchAll();

        return $result[0];
    }

    public function getListeImputeSansImage( $clientId, $dossierId, $tab_comptes_exist )
    {
        $con = new CustomPdoConnection();    
        $pdo = $con->connect();
        $query = "select c.id as client_id, d.id as dossier_id, b.nom as banque, d.status, c.nom as clients, d.nom as dossier, d.status, d.status_debut,
                (case
                    when length(bc.numcompte) >= 11 then substring(bc.numcompte, length(bc.numcompte)-10, length(bc.numcompte))
                    else bc.numcompte
                end) as comptes, rtva.libelle as regime_tva, bc.numcompte, bc.id as banque_compte_id, bc.etat, d.tva_date as ech, d.tva_mode, d.debut_activite
                from dossier d
                inner join site s on (s.id = d.site_id)
                inner join client c on (c.id = s.client_id)
                left join banque_compte bc on (bc.dossier_id = d.id)
                left join banque b on (b.id = bc.banque_id)
                left join regime_tva rtva on (d.regime_tva_id = rtva.id)
                where c.status = 1 ";
        if ( $dossierId != 0 AND $clientId != 0 ) {
            $query .= "and c.id IN ( '" . implode("', '", $clientId) . "' )
                       group by bc.numcompte, d.id 
                       having client_id IN ( '" . implode("', '", $clientId) . "' ) and dossier_id = " . $dossierId . " 
                       and (bc.numcompte is null or bc.numcompte NOT IN ( '" . implode("', '", $tab_comptes_exist) . "'))";
        } else if ( $dossierId == 0 AND $clientId != 0 ) {
            $query .= "and c.id IN ( '" . implode("', '", $clientId) . "' )
                       group by bc.numcompte, d.id
                       having client_id IN ( '" . implode("', '", $clientId) . "' )
                       and (bc.numcompte is null or bc.numcompte NOT IN ( '" . implode("', '", $tab_comptes_exist) . "'))";
        } else { //tous
            $query .= "group by bc.numcompte, d.id
                       and (bc.numcompte is null or bc.numcompte NOT IN ( '" . implode("', '", $tab_comptes_exist) . "'))";
        }

        $prep = $pdo->prepare($query);

        $prep->execute();
        return $prep->fetchAll();
    }

    public function getListImageByCategorie( $client, $dossier, $exercice, $debPeriode = null, $finPeriode = null )
    {
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT count(sep.image_id) as nb_image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, cat.id as cat_id,
                (case
                    when cat.libelle_new = 'IMAGES' then CONCAT(cat.libelle_new, ' (', cat.libelle,')')
                    else cat.libelle_new
                end) as libelle_new
                FROM separation sep
                inner join image i on sep.image_id=i.id
                inner join lot l on i.lot_id=l.id
                inner join dossier d on d.id=l.dossier_id
                inner join site s ON d.site_id = s.id
                inner join client c on c.id = s.client_id 
                inner join categorie cat on cat.id=sep.categorie_id
                
                left join souscategorie sc on sc.id = sep.souscategorie_id
                left join soussouscategorie ssc on ssc.id = sep.soussouscategorie_id
                
                where i.exercice = " . $exercice . "
                and i.decouper = 0
                and i.supprimer = 0
                and c.status = 1
                and c.id = " . $client . "
                and d.id = " . $dossier . "
                and cat.id not in(16,23, 25)
                
                and (sep.souscategorie_id IS NULL OR (sc.libelle_new NOT LIKE '%doublon%' )) 
                and (sep.soussouscategorie_id IS NULL OR (ssc.libelle_new NOT LIKE '%doublon%' ))
                
                ".$queryScan."
                group by sep.categorie_id
                order by cat.libelle_new";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getDateScan( $client, $dossier, $exercice )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT date_format(l.date_scan,'%d-%m-%Y') as date_scan
                FROM image i
                left join separation sep on sep.image_id = i.id
                inner join lot l on i.lot_id=l.id
                inner join dossier d on d.id=l.dossier_id
                inner join site s ON d.site_id = s.id
                inner join client c on c.id = s.client_id 
                left join categorie cat on cat.id=sep.categorie_id
                
                left join souscategorie sc on sc.id = sep.souscategorie_id
                left join soussouscategorie ssc on ssc.id = sep.soussouscategorie_id
                
                where i.exercice = " . $exercice . "
                and i.decouper = 0
                and c.status = 1
                and i.supprimer = 0
                and c.id = " . $client . "
                and d.id = " . $dossier . "
                
                and (sep.souscategorie_id IS NULL OR (sc.libelle_new NOT LIKE '%doublon%' )) 
                and (sep.soussouscategorie_id IS NULL OR (ssc.libelle_new NOT LIKE '%doublon%' ))
                
                group by l.date_scan
                order by l.date_scan";
        $prep = $pdo->prepare($query);

        $prep->execute();
        return $prep->fetchAll();
    }

    public function getImageByDateScan( $client, $dossier, $exercice, $debPeriode, $finPeriode )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $queryScan = " and l.date_scan >= :dateDeb";
        $queryScan .= " and l.date_scan <= :dateFin";
        $query = "SELECT count(sep.image_id) as nb_image, cat.libelle_new
                FROM separation sep
                inner join image i on sep.image_id=i.id
                inner join lot l on i.lot_id=l.id
                inner join dossier d on d.id=l.dossier_id
                inner join site s ON d.site_id = s.id
                inner join client c on c.id = s.client_id 
                inner join categorie cat on cat.id=sep.categorie_id
                where i.exercice = " . $exercice . "
                and i.decouper = 0
                and i.supprimer = 0
                and c.status = 1
                and cat.id not in(16,23, 25)
                and c.id = " . $client . "
                and d.id = " . $dossier . "
                " . $queryScan . "
                group by sep.categorie_id
                order by cat.libelle_new";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'dateDeb' => $debPeriode,
            'dateFin' => $finPeriode
        ));
        return $prep->fetchAll();
    }

    public function getListImageDetailByCategorie( $client, $dossier, $exercice, $categorie )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT i.id as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, tic.image_flague_id, tic.id as rel_id, -1 as prioriteImageId
                  from image i
                  inner join lot l on (i.lot_id = l.id)
                  inner join dossier d on d.id=l.dossier_id
                  inner join site s ON d.site_id = s.id
                  inner join client c on c.id = s.client_id 
                  left join saisie_controle sc on (sc.image_id = i.id)
                  inner join separation sep on (sep.image_id = i.id)
                  inner join categorie cat on (cat.id = sep.categorie_id)
                  left join imputation_controle ic on (ic.image_id = i.id)
                  left join tva_imputation_controle tic on (i.id = tic.image_id)
                  
                  left join souscategorie scat on scat.id = sep.souscategorie_id
                  left join soussouscategorie ssc on ssc.id = sep.soussouscategorie_id
                  
                  where i.exercice = " . $exercice . "
                  and c.status = 1
                  and c.id = " . $client . "
                  and d.id = " . $dossier . "
                  and i.supprimer = 0
                  and cat.id = " . $categorie . " 
                  and i.decouper = 0
                  
                  and (sep.souscategorie_id IS NULL OR (scat.libelle_new NOT LIKE '%doublon%' )) 
                  and (sep.soussouscategorie_id IS NULL OR (ssc.libelle_new NOT LIKE '%doublon%' ))
                  group by i.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getAllImage( $client, $dossier, $exercice, $debPeriode = null, $finPeriode = null )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if ( $debPeriode == null && $finPeriode == null ) {
            $queryScan = "";
        } else {
            $queryScan = " and l.date_scan >= :dateDeb";
            $queryScan .= " and l.date_scan <= :dateFin";
        }
        $query = "SELECT count(i.id) as nb_image
                FROM image i 
                inner join lot l on i.lot_id=l.id
                inner join dossier d on d.id=l.dossier_id
                inner join site s ON d.site_id = s.id
                inner join client c on c.id = s.client_id 
                where i.exercice = " . $exercice . "
                and i.decouper = 0
                and i.supprimer = 0
                and c.status = 1
                and c.id = " . $client . "
                and d.id = " . $dossier . "
                " . $queryScan . "";
        $prep = $pdo->prepare($query);
        if ( $debPeriode == null && $finPeriode == null ) {
            $prep->execute();
        } else {
            $prep->execute(array(
                'dateDeb' => $debPeriode,
                'dateFin' => $finPeriode
            ));
        }

        return $prep->fetchAll();
    }

    public function getListImageDetailEnCours( $client, $dossier, $exercice )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT i.id as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, sep.id as sep_id
                  from image i
                  inner join lot l on (i.lot_id = l.id)
                  inner join dossier d on d.id=l.dossier_id
                  inner join site s ON d.site_id = s.id
                  inner join client c on c.id = s.client_id 
                  left join saisie_controle sc on (sc.image_id = i.id)
                  left join separation sep on (sep.image_id = i.id)
                  left join imputation_controle ic on (ic.image_id = i.id)
                  where i.exercice = " . $exercice . "
                  and c.status = 1
                  and c.id = " . $client . "
                  and d.id = " . $dossier . "
                  and i.decouper = 0
                  and i.supprimer = 0
                  having sep_id is null";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    /**
     * @param BanqueCompte $banqueCompte
     * @param $exercice
     * @param bool $debut
     * @return float
     */
    public function getSoldes( BanqueCompte $banqueCompte, $exercice, $debut = true )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $banquecompte_id = $banqueCompte->getId();

        if ( $debut ) {
            $query = "SELECT  ic.solde_debut AS solde_deb 
                  FROM image i
                  INNER JOIN releve r ON r.image_id = i.id
                  INNER JOIN lot l ON l.id = i.lot_id
                  INNER JOIN dossier d ON l.dossier_id = d.id
                  INNER JOIN site s ON s.id = d.site_id
                  INNER JOIN client c ON c.id = s.client_id
                  INNER JOIN imputation_controle ic ON ic.image_id = i.id
                  LEFT JOIN banque_compte bc ON ic.banque_compte_id = bc.id
                  LEFT JOIN banque b ON b.id = bc.banque_id
                  LEFT JOIN separation sep ON sep.image_id = i.id 
                  LEFT JOIN souscategorie sc ON sep.souscategorie_id = sc.id 
                  WHERE i.exercice = :exercice AND i.supprimer = 0 AND 
                    bc.id = :banquecompte_id AND sc.id = 10 
                  ORDER BY ic.periode_d1,r.num_releve,i.nom ASC LIMIT 1";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'banquecompte_id' => $banquecompte_id,
                'exercice' => $exercice
            ));

            $infoReleve = $prep->fetchAll();


            if ( count($infoReleve) > 0 ) {
                return $infoReleve[0]->solde_deb;
            } else {
                return 0;
            }

        } else {
            $query = "SELECT  ic.solde_fin AS solde_fin 
                  FROM image i
                  INNER JOIN releve r ON r.image_id = i.id
                  INNER JOIN lot l ON l.id = i.lot_id
                  INNER JOIN dossier d ON l.dossier_id = d.id
                  INNER JOIN site s ON s.id = d.site_id
                  INNER JOIN client c ON c.id = s.client_id
                  INNER JOIN imputation_controle ic ON ic.image_id = i.id
                  LEFT JOIN banque_compte bc ON ic.banque_compte_id = bc.id
                  LEFT JOIN banque b ON b.id = bc.banque_id
                  LEFT JOIN separation sep ON sep.image_id = i.id 
                  LEFT JOIN souscategorie sc ON sep.souscategorie_id = sc.id 
                  WHERE i.exercice = :exercice AND i.supprimer = 0 AND  
                    bc.id = :banquecompte_id AND sc.id = 10  
                  ORDER BY ic.periode_d1 DESC,r.num_releve DESC,i.nom DESC LIMIT 1";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'banquecompte_id' => $banquecompte_id,
                'exercice' => $exercice
            ));

            $infoReleve = $prep->fetchAll();

            if ( count($infoReleve) > 0 ) {
                return $infoReleve[0]->solde_fin;
            } else {
                return 0;
            }

        }
    }

    public function getControlOb( $param, $banquecompteId )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $exercice = intval($param['exercice']);
        $query = "  select obm.mois, scat.libelle_new, scat.id as souscategorie_id
                    from banque_ob_manquante obm
                    inner join souscategorie scat on (scat.id = obm.souscategorie_id)
                    where obm.exercice = ".$exercice."
                    and obm.banque_compte_id = " .$banquecompteId;

        if ($param['dossier'] != 0) {
            $query .= " AND obm.dossier_id = ".$param['dossier']." ";
        }
        $query .= " group by scat.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getSituationsImagesByCategorie( $param )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        switch ( $param['cas'] ) {
            case 1:
                $periode = "AND l.date_scan = :periode ";
                break;
            case 4:
                $periode = " ";

                break;
            default:
                $periode = "AND l.date_scan >= ";
                $periode .= ":dateDeb";
                $periode .= " AND l.date_scan <= ";
                $periode .= ":dateFin ";
        }

        if ( $param['client'] == 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = " ";
        } else if ( $param['client'] == 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";
        } else if ( $param['client'] != 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
        } else if ( $param['client'] != 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossier .= " AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";
        }

        $query = "select c.nom, i.id as img_id, i.ctrl_imputation, i.imputation, i.ctrl_saisie, i.saisie2, i.saisie1 from image i 
            left join lot l on i.lot_id=l.id
            left join dossier d on d.id=l.dossier_id
            left join site s ON d.site_id = s.id
            left join client c on c.id = s.client_id 
            where i.supprimer = 0
            " . $clientOrDossier . "
            " . $periode . "
            and (i.saisie1 > 1 OR i.saisie2 > 1)
            and i.exercice = " . $param['exercice'] . "";

        $prep = $pdo->prepare($query);
        switch ( $param['cas'] ) {
            case 1:
                $now = $param['aujourdhui'];
                $prep->execute(array(
                    'periode' => $now,
                ));
                break;
            case 4:
                $prep->execute();
                break;
            default:
                $dateDeb = $param['dateDeb'];
                $dateFin = $param['dateFin'];
                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
        }
        $images = $prep->fetchAll();
        $listes = array();
        $listeSousCategorie = array();
        $sousCatExist = array();
        foreach ( $images as $img ) {
            if ( $img->ctrl_imputation > 1 ) {

                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaImputationControle t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->img_id)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:ImputationControle t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->img_id)
                                  ->getResult();

            } else if ( $img->imputation > 1 ) {
                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaImputation t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->img_id)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:Imputation t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->img_id)
                                  ->getResult();

            } else if ( $img->ctrl_saisie > 1 ) {
                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaSaisieControle t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->img_id)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:SaisieControle t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->img_id)
                                  ->getResult();


            } else if ( $img->saisie2 > 1 ) {
                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaSaisie2 t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->img_id)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:Saisie2 t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->img_id)
                                  ->getResult();

            } else if ( $img->saisie1 > 1 ) {

                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaSaisie1 t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->img_id)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:Saisie1 t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->img_id)
                                  ->getResult();

            }
            $trouveCategorie = false;
            //Raha efa any @ imputation dia ny tvaSaisie no ijerena ny soussouscategorie
            if ( !is_null($resTvaSaisie) ) {
                //Mety misy categorie betsaka nefa image 1 ihany ny any @ TVA SAISIE
                //Raha tsy mahita categorie ao @tvaSaisie dia mijery ny any @ Saisie

                foreach ( $resTvaSaisie as $res ) {

                    if ( !is_null($res->getSoussouscategorie()) ) {
                        $trouveCategorie = true;

                        if ( $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getId() == 16 ||
                             $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getCode() == 'CODE_BANQUE' ) {
                            $listeSousCategorie [] = $res->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                        }
                    }
                }
            }

            //raha tsy nahita categorie any @ TVA dia any @ table saisie
            if ( $trouveCategorie == false && count($resSaisie) > 0 ) {
                $res = $resSaisie[0];

                if ( !is_null($res->getSoussouscategorie()) ) {
                    if ( $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getId() == 16 ||
                         $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getCode() == 'CODE_BANQUE' ) {
                        $listeSousCategorie[] = $res->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                    }
                } /*Raha tsy misy soussouscategorie dia jerena ny souscategorie any amin'ny imputation (raha imputation)*/
                else {
                    $trouveCategorieImputation = false;
                    if ( $img->imputation > 1 ) {
                        if ( !is_null($res->getSouscategorie()) ) {
                            if ( $res->getSouscategorie()->getCategorie()->getId() == 16 ||
                                 $res->getSouscategorie()->getCategorie()->getCode() == 'CODE_BANQUE' ) {
                                $listeSousCategorie[] = $res->getSouscategorie()->getLibelleNew();
                            }
                            $trouveCategorieImputation = true;
                        }
                    }
                    /*Raha tsy imputation dia any @ separation no hijerena ny souscategorie*/
                    if ( !$trouveCategorieImputation ) {
                        $resSeparations = $this->getEntityManager()
                                               ->createQuery('SELECT s FROM AppBundle:Separation s WHERE s.image = :image_id')
                                               ->setParameter('image_id', $img->img_id)
                                               ->getResult();


                        if ( !is_null($resSeparations) && count($resSeparations) > 0 ) {
                            $resSeparation = $resSeparations[0];
                            if ( !is_null($resSeparation->getCategorie()) ) {
                                if ( $resSeparation->getCategorie()->getId() == 16 ||
                                     $resSeparation->getCategorie()->getCode() == 'CODE_BANQUE' ) {
                                    if ( !is_null($resSeparation->getSouscategorie()) ) {
                                        $listeSousCategorie[] = $resSeparation->getSouscategorie()->getLibelleNew();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ( $listeSousCategorie as $value ) {
            if ( !in_array($value, $sousCatExist) ) {
                $listes[$value] = 1;
                $sousCatExist[] = $value;
            } else
                $listes[$value]++;
        }
        return $listes;
    }

    public function getNbImageEncours( $param, $detail = false )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        switch ( $param['cas'] ) {
            case 1:
                $periode = "AND l.date_scan = :periode ";
                break;
            case 4:
                $periode = " ";

                break;
            default:
                $periode = "AND l.date_scan >= ";
                $periode .= ":dateDeb";
                $periode .= " AND l.date_scan <= ";
                $periode .= ":dateFin ";
        }

        if ( $param['dossier'] == 0 ) {
            $dossier = " ";
        } else {
            $dossier = "AND d.id = ";
            $dossier .= $param['dossier'] . " ";
        }

        if($detail){
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, cat.libelle_new, cat.id, pi.image_id as prioriteImageId ";
        }else{
            $query = "SELECT count(i.id) as nb_img_cours ";
        }

        $query .= "FROM image i 
                INNER JOIN lot l on l.id=i.lot_id 
                INNER JOIN dossier d on d.id=l.dossier_id
                left join separation sep on (sep.image_id = i.id)
                left join imputation_controle ic on (ic.image_id = i.id) 
                left join categorie cat on (cat.id = sep.categorie_id)
                left join saisie_controle sc on (sc.image_id = i.id) 
                left join priorite_image pi on pi.image_id = i.id
                WHERE i.exercice = " . $param['exercice'] . "
                " . $dossier . "
                " . $periode . " 
                AND i.saisie2 <= 1 
                AND i.saisie1 <= 1
                AND i.id NOT IN (SELECT img.id FROM separation sep INNER JOIN image img on img.id=sep.image_id)
                AND i.decouper=0 AND ucase(i.ext_image) = 'PDF' AND i.supprimer=0
                AND NOT (l.date_scan BETWEEN CAST('2010-01-01' AS DATE) AND CAST('2019-03-31' AS DATE))";
        $prep = $pdo->prepare($query);
        switch ( $param['cas'] ) {
            case 1:
                $now = $param['aujourdhui'];
                $prep->execute(array(
                    'periode' => $now,
                ));
                break;
            case 4:
                $prep->execute();
                break;
            default:
                $dateDeb = $param['dateDeb'];
                $dateFin = $param['dateFin'];
                $prep->execute(array(
                    'dateDeb' => $dateDeb,
                    'dateFin' => $dateFin,
                ));
        }
        $nbImagesEncours = $prep->fetchAll();
        if($detail){
            return $nbImagesEncours;
        }else{
            return (count($nbImagesEncours) == 1) ? $nbImagesEncours[0]->nb_img_cours : 0;
        }
    }

    public function getMoisManquantByParam( $param )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if ( $param['client'] == 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = " ";
            $clientOrDossierMois = " ";
        } else if ( $param['client'] == 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";

            $clientOrDossierMois = "AND rmm.dossier_id = ";
            $clientOrDossierMois .= $param['dossier'] . " ";
        } else if ( $param['client'] != 0 && $param['dossier'] == 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";

            $clientOrDossierMois = "AND cm.id IN ( '" . implode("', '", $param['client']) . "' )";
        } else if ( $param['client'] != 0 && $param['dossier'] != 0 ) {
            $clientOrDossier = "AND c.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossier .= " AND d.id = ";
            $clientOrDossier .= $param['dossier'] . " ";

            $clientOrDossierMois = "AND cm.id IN ( '" . implode("', '", $param['client']) . "' )";
            $clientOrDossierMois .= " AND rmm.dossier_id = ";
            $clientOrDossierMois .= $param['dossier'] . " ";
        }
        $query = "select d.cloture, bc.id as banque_compte_id, bc.numcompte, d.nom as dossier_nom, c.nom as client_nom, d.status,
            (case
                when length(bc.numcompte) >= 11 then substring(bc.numcompte, length(bc.numcompte)-10, length(bc.numcompte))
                else bc.numcompte
            end) as comptes,
            (select rmm.mois
            from releve rm
            inner join releve_manquant rmm on rmm.banque_compte_id = rm.banque_compte_id
            inner join image im on im.id = rm.image_id
            inner join lot lm on (lm.id = im.lot_id)
            inner join dossier dm on (dm.id = lm.dossier_id)
            inner join site sm on sm.id=dm.site_id
            inner join client cm on cm.id=sm.client_id
            inner join banque_compte bcm on (bcm.id = rm.banque_compte_id and bcm.dossier_id = dm.id)
            inner join banque bm on (bm.id=bcm.banque_id)
            where rmm.exercice = " . $param['exercice'] . "
            and rm.banque_compte_id=r.banque_compte_id 
            and cm.status = 1
            and rm.operateur_id is null
            " . $clientOrDossierMois . "
            group by bcm.numcompte) as mois
            from image i
            left join releve r on (r.image_id = i.id)
            inner join lot l on (l.id = i.lot_id)
            inner join dossier d on (l.dossier_id = d.id)
            inner join site s on (s.id = d.site_id)
            inner join client c on (c.id = s.client_id)
            inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
            inner join banque b on (b.id = bc.banque_id)
            where i.exercice = " . $param['exercice'] . " and i.supprimer = 0
            and c.status = 1
            and r.operateur_id is null
            " . $clientOrDossier . "
            group by bc.numcompte";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getListImageDetailByDateScan($dossier, $exercice, $categorie, $debPeriode, $finPeriode, $souscategorieLib = null )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $queryScan = " and l.date_scan >= :dateDeb";
        $queryScan .= " and l.date_scan <= :dateFin";
        if($souscategorieLib == null){
            $souscategorieLib = " ";
        }else{
            $souscategorieLib = " and scat.libelle_new = '".$souscategorieLib."' ";
        }
        $query = "SELECT i.id as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, tic.image_flague_id, tic.id as rel_id, -1 as prioriteImageId
                  from image i
                  inner join lot l on (i.lot_id = l.id)
                  inner join dossier d on d.id=l.dossier_id
                  left join saisie_controle sc on (sc.image_id = i.id)
                  inner join separation sep on (sep.image_id = i.id)
                  inner join categorie cat on (cat.id = sep.categorie_id)
                  left join imputation_controle ic on (ic.image_id = i.id)
                  left join tva_imputation_controle tic on (i.id = tic.image_id)
                  
                  left join souscategorie sc on sep.souscategorie_id = sc.id
                  left join souscategorie ssc on sep.soussouscategorie_id = ssc.id
                  
                  where i.exercice = " . $exercice . "
                  and l.dossier_id = " . $dossier . "
                  and i.supprimer = 0
                  and cat.id = " . $categorie . "
                  " . $queryScan . "
                  " . $souscategorieLib . "
                  and i.decouper = 0
                  
                  and (sep.souscategorie_id IS NULL OR (sc.libelle_new NOT LIKE '%doublon%' )) 
                  and (sep.soussouscategorie_id IS NULL OR (ssc.libelle_new NOT LIKE '%doublon%' ))
                
                  
                  group by i.id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'dateDeb' => $debPeriode,
            'dateFin' => $finPeriode
        ));
        return $prep->fetchAll();
    }

    public function getListImageNonLettreByCategorie($releve, $dossierId, $exercice, $debPeriode = null, $finPeriode = null, $detail = false, $categorieId = null){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }

        if(!$detail){
            $query = "SELECT count(distinct(tic.image_id)) as nb_non_lettre, cat.libelle_new, cat.id, ic.type_piece_id ";
            $groupby = " GROUP BY sep.categorie_id ";
            $categorie = " ";
        }else{
            $query = "SELECT distinct(tic.image_id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, cat.libelle_new, cat.id, ic.type_piece_id, sep.categorie_id, tic.image_flague_id, tic.id as rel_id ";
            $groupby = " GROUP BY tic.image_id ";
            $categorie = " AND cat.id = ".$categorieId." ";
        }
        $query .= "FROM tva_imputation_controle tic
                inner join image i ON (i.id = tic.image_id)
                inner join lot l on (l.id = i.lot_id)
                inner join separation sep on (sep.image_id = i.id)
                inner join imputation_controle ic on (tic.image_id = ic.image_id) 
                left join categorie cat on (cat.id = sep.categorie_id)
                left join saisie_controle sc on (sc.image_id = i.id) 
                WHERE i.exercice = ".$exercice."
                AND i.supprimer = 0
                AND i.decouper = 0
                AND l.dossier_id = ".$dossierId." AND tic.image_flague_id IS NULL
                ".$queryScan."
                ".$categorie."
                ".$groupby."
                HAVING 
                (
                    ((sep.categorie_id not in (10,12) and ic.type_piece_id <> 1) OR (sep.categorie_id in (9,13) and ic.type_piece_id = 1)) OR 
                    ((sep.categorie_id in (10,12) and ic.type_piece_id <> 1) OR (sep.categorie_id in (9,13) and ic.type_piece_id = 1)) OR  
                    sep.categorie_id not in (16)
                )";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getListImageNonLettreByBanque($dossierId, $exercice, $debPeriode = null, $finPeriode = null, $detail = false, $souscategorieLib = null, $isLettre = false, $imageId = null){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }

        if($souscategorieLib === null){
            $souscategorieLib = " ";
        }else{
            $souscategorieLib = " and scat.libelle_new = '".$souscategorieLib."' ";
        }

        if($isLettre){
            $imageValider = " and i.valider = 100 ";
        }else{
            $imageValider = " and i.valider <> 100 ";
        }

        if($imageId === null){
            $imageId = " ";
        }else{
            $imageId = " and i.id = ".$imageId." ";
        }

        if(!$detail){
            $query = "select count(distinct i.id) as nb, scat.libelle_new ";
            $groupby = " group by scat.id ";
        }else{
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, sep.categorie_id, i.valider ";
            $groupby = " group by i.id ";
        }
        $query .= "from image i
                left join releve r on (i.id = r.image_id)
                inner join lot l on (l.id = i.lot_id)
                inner join banque_compte bc on (bc.id = r.banque_compte_id and bc.dossier_id = l.dossier_id)
                inner join banque b on (b.id=bc.banque_id)
                inner join separation sep on (sep.image_id = i.id)
                inner join souscategorie scat on (scat.id = sep.souscategorie_id)
                left join imputation_controle ic on (i.id = ic.image_id) 
                left join saisie_controle sc on (sc.image_id = i.id) 
                where i.supprimer = 0 
                ".$imageValider."
                and i.exercice = ".$exercice."
                and l.dossier_id = ".$dossierId."
                ".$imageId."
                and sep.categorie_id = 16
                and sep.souscategorie_id = 10
                and r.operateur_id is null
                ".$queryScan."
                ".$souscategorieLib."
                ".$groupby."";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $releveBanc = $prep->fetchAll();
        if(!$detail){
            $query = "select count(distinct i.id) as nb, scat.libelle_new ";
            $groupby = " group by bscat.sous_categorie_id ";
        }else{
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, sep.categorie_id, scat.libelle_new, i.valider ";
            $groupby = " ";
        }
        $query .= "from image i
                inner join banque_sous_categorie_autre bscat on (i.id = bscat.image_id)
                inner join lot l on (l.id = i.lot_id)
                inner join banque_compte bc on (bc.dossier_id = l.dossier_id)
                inner join banque b on (b.id=bc.banque_id)
                inner join separation sep on (sep.image_id = i.id)
                inner join categorie cat on (cat.id = sep.categorie_id)
                inner join souscategorie scat on (scat.id = bscat.sous_categorie_id)
                left join imputation_controle ic on (i.id = ic.image_id) 
                left join saisie_controle sc on (sc.image_id = i.id) 
                where i.supprimer = 0 
                ".$imageValider."
                and i.exercice = ".$exercice."
                and l.dossier_id = ".$dossierId."
                ".$imageId."
                and bscat.sous_categorie_id <> 10
                and cat.id = 16
                ".$queryScan."
                ".$souscategorieLib."
                ".$groupby."";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $autres = $prep->fetchAll();
        return array_merge($releveBanc, $autres);
    }

    public function getListImageBanqueBySeparation($dossierId, $exercice, $debPeriode = null, $finPeriode = null, $souscategorie = null){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }

        if($souscategorie == null){
            $souscategorie = " ";
            $query = "SELECT count(sep.image_id) as nb_image, scat.libelle_new ";
            $groupby = " group by sep.souscategorie_id ";
        }else{
            $souscategorie = " and scat.libelle_new = '".$souscategorie."' ";
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, sep.categorie_id, scat.libelle_new, -1 as prioriteImageId ";
            $groupby = " ";
        }
        $query .= "FROM separation sep
                inner join image i on sep.image_id=i.id
                inner join lot l on i.lot_id=l.id
                inner join categorie cat on cat.id=sep.categorie_id
                left join souscategorie scat on scat.id=sep.souscategorie_id
                left join soussouscategorie sscat on sscat.id=sep.soussouscategorie_id
                left join imputation_controle ic on (i.id = ic.image_id) 
                left join saisie_controle sc on (sc.image_id = i.id)                 
                where cat.code='CODE_BANQUE'
                and i.exercice = " .$exercice . "
                and l.dossier_id = " .$dossierId. "
                and i.saisie1 <= 1
                and i.saisie2 <= 1
                and i.ctrl_saisie <= 1
                and i.imputation <= 1
                and i.ctrl_imputation <= 1                
                and (sep.souscategorie_id IS NULL OR (scat.libelle_new NOT LIKE '%doublon%' AND scat.libelle_new NOT LIKE '%ANPC%')) 
                and (sep.soussouscategorie_id IS NULL OR (sscat.libelle_new NOT LIKE '%doublon%' AND sscat.libelle_new NOT LIKE '%ANPC%'))
                                
                ".$queryScan."
                ".$souscategorie."
                ".$groupby."
                order by scat.libelle_new";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getListeImagesByCategorie( $dossierId, $exercice, $categorieId, $categorieCode, $debPeriode = null, $finPeriode = null, $souscategorieLib = null )
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }
        $query = "select i.id as imageId, i.nom as image, i.ctrl_imputation, i.imputation, i.ctrl_saisie, i.saisie2, i.saisie1, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, -1 as prioriteImageId
            from image i 
            left join separation sep on sep.image_id = i.id
            left join souscategorie souscat on souscat.id = sep.souscategorie_id
            left join soussouscategorie ssc on ssc.id = sep.soussouscategorie_id
            left join lot l on i.lot_id=l.id
            left join imputation_controle ic on (i.id = ic.image_id) 
            left join saisie_controle sc on (sc.image_id = i.id) 
            where i.supprimer = 0
            and l.dossier_id = ".$dossierId."
            and (i.saisie1 > 1 OR i.saisie2 > 1)
            
            ".$queryScan."
            
            and (sep.souscategorie_id IS NULL OR (souscat.libelle_new NOT LIKE '%doublon%' )) 
            and (sep.soussouscategorie_id IS NULL OR (ssc.libelle_new NOT LIKE '%doublon%' ))
            
            and i.exercice = ".$exercice."";

        $prep = $pdo->prepare($query);
        $prep->execute();
        $images = $prep->fetchAll();
        $listes = array();
        $listeSousCategorie = array();
        $listeImages = array();
        $sousCatExist = array();
        $libelleScat = '';
        foreach ( $images as $img ) {
            if ( $img->ctrl_imputation > 1 ) {

                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaImputationControle t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->imageId)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:ImputationControle t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->imageId)
                                  ->getResult();

            } else if ( $img->imputation > 1 ) {
                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaImputation t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->imageId)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:Imputation t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->imageId)
                                  ->getResult();

            } else if ( $img->ctrl_saisie > 1 ) {
                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaSaisieControle t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->imageId)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:SaisieControle t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->imageId)
                                  ->getResult();


            } else if ( $img->saisie2 > 1 ) {
                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaSaisie2 t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->imageId)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:Saisie2 t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->imageId)
                                  ->getResult();

            } else if ( $img->saisie1 > 1 ) {

                $resTvaSaisie = $this->getEntityManager()
                                     ->createQuery('SELECT t FROM AppBundle:TvaSaisie1 t WHERE t.image = :image_id')
                                     ->setParameter('image_id', $img->imageId)
                                     ->getResult();

                $resSaisie = $this->getEntityManager()
                                  ->createQuery('SELECT t FROM AppBundle:Saisie1 t WHERE t.image = :image_id')
                                  ->setParameter('image_id', $img->imageId)
                                  ->getResult();

            }
            $trouveCategorie = false;
            //Raha efa any @ imputation dia ny tvaSaisie no ijerena ny soussouscategorie
            if ( !is_null($resTvaSaisie) ) {
                //Mety misy categorie betsaka nefa image 1 ihany ny any @ TVA SAISIE
                //Raha tsy mahita categorie ao @tvaSaisie dia mijery ny any @ Saisie

                foreach ( $resTvaSaisie as $res ) {

                    if ( !is_null($res->getSoussouscategorie()) ) {
                        $trouveCategorie = true;

                        if ( $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getId() == $categorieId ||
                             $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getCode() == $categorieCode ) {
                            $libelleScat = $res->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                            if($libelleScat != 'Doublon'){
                                $listeSousCategorie [] = $libelleScat;
                                if (!in_array($img, $listeImages, true)) {
                                    if( $souscategorieLib != null && $res->getSoussouscategorie()->getSouscategorie() != null && $souscategorieLib == $res->getSoussouscategorie()->getSouscategorie()->getLibelleNew()){
                                        $listeImages[] = $img;
                                    }

                                    if( $souscategorieLib == null){
                                        $listeImages[] = $img;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //raha tsy nahita categorie any @ TVA dia any @ table saisie
            if ( $trouveCategorie == false && count($resSaisie) > 0 ) {
                $res = $resSaisie[0];

                if ( !is_null($res->getSoussouscategorie()) ) {
                    if ( $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getId() == $categorieId ||
                         $res->getSoussouscategorie()->getSouscategorie()->getCategorie()->getCode() == $categorieCode ) {
                        $libelleScat = $res->getSoussouscategorie()->getSouscategorie()->getLibelleNew();
                        if($libelleScat != 'Doublon'){
                            $listeSousCategorie[] = $libelleScat;
                            if (!in_array($img, $listeImages, true)) {
                                if (!in_array($img, $listeImages, true)) {
                                    if( $souscategorieLib != null && $res->getSoussouscategorie()->getSouscategorie() != null && $souscategorieLib == $res->getSoussouscategorie()->getSouscategorie()->getLibelleNew()){
                                        $listeImages[] = $img;
                                    }

                                    if( $souscategorieLib == null){
                                        $listeImages[] = $img;
                                    }
                                }
                            }
                        }
                    }
                } /*Raha tsy misy soussouscategorie dia jerena ny souscategorie any amin'ny imputation (raha imputation)*/
                else {
                    $trouveCategorieImputation = false;
                    if ( $img->imputation > 1 ) {
                        if ( !is_null($res->getSouscategorie()) ) {
                            if ( $res->getSouscategorie()->getCategorie()->getId() == $categorieId ||
                                 $res->getSouscategorie()->getCategorie()->getCode() == $categorieCode ) {
                                $libelleScat = $res->getSouscategorie()->getLibelleNew();
                                if($libelleScat != 'Doublon'){
                                    $listeSousCategorie[] = $libelleScat;
                                    if (!in_array($img, $listeImages, true)) {
                                        if( $souscategorieLib != null && $res->getSouscategorie() != null && $souscategorieLib == $res->getSouscategorie()->getLibelleNew()){
                                            $listeImages[] = $img;
                                        }

                                        if( $souscategorieLib == null){
                                            $listeImages[] = $img;
                                        }
                                    }
                                }
                            }
                            $trouveCategorieImputation = true;
                        }
                    }
                    /*Raha tsy imputation dia any @ separation no hijerena ny souscategorie*/
                    if ( !$trouveCategorieImputation ) {
                        $resSeparations = $this->getEntityManager()
                                               ->createQuery('SELECT s FROM AppBundle:Separation s WHERE s.image = :image_id')
                                               ->setParameter('image_id', $img->imageId)
                                               ->getResult();


                        if ( !is_null($resSeparations) && count($resSeparations) > 0 ) {
                            $resSeparation = $resSeparations[0];
                            if ( !is_null($resSeparation->getCategorie()) ) {
                                if ( $resSeparation->getCategorie()->getId() == $categorieId ||
                                     $resSeparation->getCategorie()->getCode() == $categorieCode ) {
                                    if ( !is_null($resSeparation->getCategorie()) ) {
                                        if (!in_array($img, $listeImages, true)) {
                                            if( $souscategorieLib != null && $resSeparation->getSouscategorie() != null && $souscategorieLib == $resSeparation->getSouscategorie()->getLibelleNew()){
                                                if($resSeparation->getSouscategorie()->getLibelleNew() != 'Doublon')
                                                    $listeImages[] = $img;
                                            }
                                            if( $souscategorieLib == null){
                                                $listeImages[] = $img;
                                            }
                                        }
                                    }
                                    if ( !is_null($resSeparation->getSouscategorie()) ) {
                                        if($resSeparation->getSouscategorie()->getLibelleNew() != 'Doublon')
                                            $listeSousCategorie[] = $resSeparation->getSouscategorie()->getLibelleNew();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ( $listeSousCategorie as $value ) {
            if ( !in_array($value, $sousCatExist) ) {
                $listes[$value] = 1;
                $sousCatExist[] = $value;
            } else
                $listes[$value]++;
        }
        $data = array();
        $data['images'] = $listeImages;
        $data['souscat'] = $listes;
        return $data;
    }

    public function getNbreImageParCategorie($dossierid, $exercice, $status)
    {


        $query = 'SELECT DISTINCT C.libelle AS categorie, C.id AS categorie_id, I.id AS image_id 
                        FROM image I INNER JOIN lot L ON L.id = I.lot_id 
                        LEFT JOIN separation SEP ON SEP.image_id = I.id  
                        LEFT JOIN souscategorie SC ON SC.id = SEP.souscategorie_id 
                        LEFT JOIN soussouscategorie SSC ON SSC.id = SEP.soussouscategorie_id ';

        $where = 'WHERE I.supprimer = 0 AND I.decouper = 0 AND I.exercice = :exercice AND L.dossier_id = :dossier_id 
                        AND (SEP.souscategorie_id IS NULL OR (SC.libelle_new NOT LIKE "%doublon%" )) 
                        AND (SEP.soussouscategorie_id IS NULL OR (SSC.libelle_new NOT LIKE "%doublon%" )) ';
        $param = [
            'dossier_id' => $dossierid,
            'exercice' => $exercice
        ];

        switch ($status){
            //Reception (mbola ao @ panier reception an'i Hezekia)
            case 'recue':
                $query .= ' INNER JOIN panier_reception PR ON PR.lot_id = L.id ';
                $where .= ' AND PR.etape_traitement_id = 1 AND PR.status = 0 ';
                break;

            //Séparation (ao @ panier reception an'i Fandresena)
            case 'categorisee':
                $query .= ' INNER JOIN panier_reception PR ON PR.lot_id = L.id ';
                $where .= ' AND ((PR.etape_traitement_id = 3 AND PR.status = 0)) ';
                break;

            //A la Saisie (mbola en cours de saisie)
            case 'saisie':
                $where .= ' AND ((I.saisie1 = 1 OR I.saisie2 = 1) OR 
                            (I.saisie1 = 0 AND I.saisie2 = 0 AND 
                            I.lot_id IN (SELECT lot_id FROM panier_reception WHERE lot_id = I.lot_id AND etape_traitement_id = 3 AND status = 1))) ';
                break;

            //A l'imputation
            case 'aimputer':
                $query .= ' LEFT JOIN tva_imputation_controle TVA ON TVA.image_id = I.id ';
                $where .= ' AND (I.saisie1 >= 2 OR I.saisie2 >= 2) 
                AND ((TVA.pcc_id IS NULL AND TVA.tiers_id IS NULL AND TVA.pcc_bilan_id IS NOT NULL) OR NOT EXISTS (SELECT 1 FROM tva_imputation WHERE tva_imputation.image_id = I.id)) 
                AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id)
                AND I.valider <> 100 ';
                break;

            case 'revisee':
                $query .= ' INNER JOIN ecriture E ON E.image_id = I.id ';
                $where .= ' AND I.imputation >= 2  ';
                break;

            case 'nonlettreerevisee':
                $where .= ' AND I.id IN ('.$this->getNonLettreesImageIds($dossierid, $exercice, 'revisee').')';
                $query .= ' INNER JOIN ecriture E ON E.image_id = I.id ';
                break;

            case 'imputee':
                $query .= ' INNER JOIN tva_imputation_controle IMP ON IMP.image_id = I.id ';
                $where .= ' AND (I.imputation >= 2 OR I.ctrl_imputation >= 2) 
                AND (IMP.pcc_id IS NOT NULL OR IMP.tiers_id IS NOT NULL OR IMP.pcc_bilan_id IS NOT NULL) 
                AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id)
                AND I.valider <> 100 ';
                break;

            case 'nonlettreeimputee':
                $where .= ' AND I.id IN ('.$this->getNonLettreesImageIds($dossierid, $exercice, 'imputee').')';
                break;

            case 'lettree':
                $query .= ' LEFT JOIN tva_imputation_controle IMP ON IMP.image_id = I.id 
                            LEFT JOIN releve R ON R.image_id = I.id 
                            LEFT JOIN banque_sous_categorie_autre B ON B.image_id = I.id ';
                $where .= ' AND (I.imputation >= 2 OR I.ctrl_imputation >= 2) 
                AND (IMP.pcc_id IS NOT NULL OR IMP.tiers_id IS NOT NULL OR IMP.pcc_bilan_id IS NOT NULL) AND EXISTS (SELECT 1 FROM tva_imputation WHERE tva_imputation.image_id = I.id)
                AND I.valider <> 100
                AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id)
                AND (IMP.image_flague_id IS NOT NULL OR R.image_flague_id IS NOT NULL OR B.image_flague_id IS NOT NULL) ';
                break;

            case 'revue':
                $where .= ' AND I.valider = 100 AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id) ';
                break;

        }

        $query .= ' INNER JOIN categorie C ON C.id = SEP.categorie_id '.$where ;

        $final = 'SELECT categorie_id AS id, categorie AS libelle, count(*) AS nbre FROM  ( '.$query.' ) AS TEMP GROUP BY categorie';

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($final);
        $prep->execute($param);

        return $prep->fetchAll();
    }

    public function getListeImageOrDateScanSU($dossierid,$exercice, $status, $categorieid,$souscategorieid,$soussouscategorieid, $datescan, $count = false )
    {

        $query = '';

        $select = 'SELECT DISTINCT I.* ';

        $query .= ' FROM image I INNER JOIN lot L ON L.id = I.lot_id 
                        LEFT JOIN separation SEP ON SEP.image_id = I.id  
                        LEFT JOIN souscategorie SC ON SC.id = SEP.souscategorie_id 
                        LEFT JOIN soussouscategorie SSC ON SSC.id = SEP.soussouscategorie_id ';

        $where = ' WHERE I.supprimer = 0 AND I.decouper = 0 AND I.exercice = :exercice AND L.dossier_id = :dossier_id 
                        AND (SEP.souscategorie_id IS NULL OR (SC.libelle_new NOT LIKE "%doublon%" )) 
                        AND (SEP.soussouscategorie_id IS NULL OR (SSC.libelle_new NOT LIKE "%doublon%" )) ';

        $param = [
            'dossier_id' => $dossierid,
            'exercice' => $exercice
            ];

        if (intval($soussouscategorieid) !== -1) {
            $where .= ' AND SEP.soussouscategorie_id = :soussouscategorie_id';
            $param['soussouscategorie_id'] = $soussouscategorieid;
        } elseif (intval($souscategorieid) !== -1) {
            $where .= ' AND SEP.souscategorie_id = :souscategorie_id';
            $param['souscategorie_id'] = $souscategorieid;
        } elseif (intval($categorieid) !== -1) {
            $where .= ' AND SEP.categorie_id = :categorie_id';
            $param['categorie_id'] = $categorieid;
        }

        switch ($status){
            //Reception (mbola ao @ panier reception an'i Hezekia)
            case 'recue':
                $query .= ' INNER JOIN panier_reception PR ON PR.lot_id = L.id ';
                $where .= ' AND PR.etape_traitement_id = 1 AND PR.status = 0 ';
                break;

            //Séparation (ao @ panier reception an'i Fandresena)
            case 'categorisee':
                $query .= ' INNER JOIN panier_reception PR ON PR.lot_id = L.id ';
                $where .= ' AND ((PR.etape_traitement_id = 3 AND PR.status = 0)) ';
                break;

            //A la Saisie (mbola en cours de saisie)
            case 'saisie':
                $where .= ' AND ((I.saisie1 = 1 OR I.saisie2 = 1) OR 
                            (I.saisie1 = 0 AND I.saisie2 = 0 AND 
                            I.lot_id IN (SELECT lot_id FROM panier_reception WHERE lot_id = I.lot_id AND etape_traitement_id = 3 AND status = 1))) ';
                break;

            //A l'imputation
            case 'aimputer':
                $query .= ' LEFT JOIN tva_imputation_controle TVA ON TVA.image_id = I.id ';
                $where .= ' AND (I.saisie1 >= 2 OR I.saisie2 >= 2) 
                AND ((TVA.pcc_id IS NULL AND TVA.tiers_id IS NULL AND TVA.pcc_bilan_id IS NOT NULL) OR NOT EXISTS (SELECT 1 FROM tva_imputation WHERE tva_imputation.image_id = I.id)) 
                AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id)
                AND I.valider <> 100 ';
                break;

            case 'imputee':
                $query .= ' INNER JOIN tva_imputation_controle IMP ON IMP.image_id = I.id ';
                $where .= ' AND (I.imputation >= 2 OR I.ctrl_imputation >= 2) 
                AND (IMP.pcc_id IS NOT NULL OR IMP.tiers_id IS NOT NULL OR IMP.pcc_bilan_id IS NOT NULL) 
                AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id)
                AND I.valider <> 100 ';
                break;

            case 'nonlettreeimputee':
                $where .= ' AND I.id IN ('.$this->getNonLettreesImageIds($dossierid, $exercice, 'imputee').') ';
                break;

            case 'nonlettreerevisee':
                $select .= ', CASE WHEN E.id IS NOT NULL THEN "E" ELSE "" END AS ecriture ';
                $where .= ' AND I.id IN ('.$this->getNonLettreesImageIds($dossierid, $exercice, 'revisee').') ';
                $query .= ' INNER JOIN ecriture E ON E.image_id = I.id ';
                break;

            case 'revisee':
                $select .= ', CASE WHEN E.id IS NOT NULL THEN "E" ELSE "" END AS ecriture ';
                $query .= ' INNER JOIN ecriture E ON E.image_id = I.id ';
                $where .= ' AND I.imputation >= 2  ';
                break;

            case 'revue':
                $where .= ' AND I.valider = 100 AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = I.id) ';
                break;

            default:
                $select .= ', CASE WHEN E.id IS NOT NULL THEN "E" ELSE "" END AS ecriture ';
                $query .= ' LEFT JOIN ecriture E ON E.image_id = I.id ';
                break;

        }


        if($datescan !== false){
            $where .= ' AND L.date_scan = :date_scan';
            $param['date_scan'] = $datescan->format('Y-m-d');
        }

        $query = $select . ' '. $query.' '.$where . ' ORDER BY L.date_scan';

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute($param);

        $result =  $prep->fetchAll();


        if($count === true){
            return count($result);
        }

        return $result;

    }

    public function getImageSu($imageid){

        $query = 'SELECT I.*  FROM image I INNER JOIN lot L ON L.id = I.lot_id WHERE I.id= :image_id';

        $param = [
            'image_id' => $imageid
        ];

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute($param);

        $res = $prep->fetchAll();

        if(count($res) > 0){
            return $res[0];
        }

        return null;
    }

    public function getListImageBanqueGestionTache( $did, $exercice, $dscan, $souscat, $soussouscat, $etape, $banquecompteid )
    {
        if ( $exercice == '' ) {
            $now = new \DateTime();
            $exercice = $now->format('Y');
        }

        if ( $etape == 1 ) {
            $where = "AND I.saisie1 > 0 ";
        } else {
            $where = "AND I.ctrl_saisie >= 2 ";
        }

        //releve bancaire
        $banquecompte = $this->getEntityManager()
                             ->getRepository('AppBundle:BanqueCompte')
                             ->find($banquecompteid);


        if ( !$banquecompte ) {
            $query = "SELECT I.*, S.souscategorie_id, '' AS avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTRL ON SCTRL.image_id = I.id  
                        WHERE L.dossier_id =" . $did . " AND I.exercice=" . $exercice . " AND I.supprimer = 0 " . $where;
        } else {
            $query = "SELECT I.*,S.souscategorie_id, '' AS avec_releve FROM  image I 
                        INNER JOIN lot L ON I.lot_id = L.id 
                        INNER JOIN dossier D ON L.dossier_id = D.id 
                        INNER JOIN separation S ON S.image_id = I.id 
                        INNER JOIN categorie C ON S.categorie_id = C.id 
                        INNER JOIN souscategorie SC ON S.souscategorie_id = SC.id 
                        LEFT JOIN soussouscategorie SSC ON S.soussouscategorie_id = SSC.id 
                        LEFT JOIN  saisie_controle SCTRL ON SCTRL.image_id = I.id  
                        WHERE L.dossier_id =" . $did . " AND I.exercice=" . $exercice . " AND I.supprimer = 0 AND 
                        SCTRL.banque_compte_id =" . $banquecompteid;
        }


        if ( $dscan <> 0 ) {
            $query .= " AND L.date_scan ='" . $dscan . "'";
        }

        if ( $soussouscat != -1 && isset($soussouscat) ) {
            $query .= " AND SSC.id =" . $soussouscat;
        } else {
            $query .= " AND SC.id =" . $souscat;
        }

        $query .= " ORDER BY SCTRL.periode_d1, SCTRL.periode_f1, I.nom";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute();

        $res = $prep->fetchAll();
        return $res;
    }

    public function getListImageNonValiderImputaion($montant, $dossierId, $exercice, $debPeriode = null, $finPeriode = null, $detail = false, $categorieId = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $exercices = '' . ($exercice - 1);
        $exercices .= ','. $exercice;
        $exercices .= ',' . ($exercice + 1);

        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }
        $params = [
            'DOSSIER_ID' => $dossierId,
            'montant' => $montant,
            'montant_' => $montant
        ];
        $categorie = " ";
        if(!$detail){
            $query = "SELECT count(distinct(tic.image_id)) as nb_non_lettre, cat.libelle_new, cat.id, ic.type_piece_id, i.nom, sep.categorie_id ";
        }else{
            $query = "SELECT distinct(tic.image_id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, cat.libelle_new, cat.id, ic.type_piece_id, sep.categorie_id, tic.image_flague_id, tic.id as rel_id, ".$montant." as montant, tic.libelle, -1 as prioriteImageId ";
            if($categorieId != -1 && $categorieId != null) 
                $categorie = " AND cat.id = ".$categorieId." ";
        }
        
        $query .= " FROM tva_imputation_controle tic
                JOIN image i ON (i.id = tic.image_id)
                JOIN lot l on (l.id = i.lot_id)
                JOIN separation sep on (sep.image_id = i.id)
                JOIN imputation_controle ic on (tic.image_id = ic.image_id) 
                JOIN categorie cat on (cat.id = sep.categorie_id) 
                JOIN souscategorie scat on (scat.id = sep.souscategorie_id) 
                left join saisie_controle sc on (sc.image_id = i.id) 
              
                left join soussouscategorie ssc on ssc.id = sep.soussouscategorie_id
                
                WHERE i.exercice in (".$exercices.") 
                AND l.dossier_id = :DOSSIER_ID AND tic.image_flague_id IS NULL 
                
                and (sep.souscategorie_id IS NULL OR (scat.libelle_new NOT LIKE '%doublon%' )) 
                and (sep.soussouscategorie_id IS NULL OR (ssc.libelle_new NOT LIKE '%doublon%' ))
                
                ".$queryScan."  
                ".$categorie."             
                GROUP BY tic.image_id, sep.categorie_id, ic.type_piece_id 
                HAVING 
                (
                    ROUND(sum(tic.montant_ttc),2) = ROUND(-(:montant),2) and ((sep.categorie_id in (10,12) and ic.type_piece_id <> 1) OR (sep.categorie_id in (9,13) and ic.type_piece_id = 1)) OR 
                    ROUND(sum(tic.montant_ttc),2) = -ROUND(-(:montant_),2) and not((sep.categorie_id in (10,12) and ic.type_piece_id <> 1) OR (sep.categorie_id in (9,13) and ic.type_piece_id = 1))
                )";
        $prep = $pdo->prepare($query);
        $prep->execute($params);
        $res = $prep->fetchAll();
        return $res;
    }

    public function getListImageNonValiderSousBanque($montant, $dossierId, $exercice, $debPeriode = null, $finPeriode = null, $detail = false, $souscategorieLib = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $exercices = '' . ($exercice - 1);
        $exercices .= ','. $exercice;
        $exercices .= ',' . ($exercice + 1);

        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }
        $params = [
            'DOSSIER_ID' => $dossierId,
            'montant' => $montant,
            'montant_' => $montant
        ];
        $souscategoLib = " ";
        if($souscategorieLib != -1 && $souscategorieLib != null) 
            $souscategoLib = " and scat.libelle_new = '".$souscategorieLib."' ";
        if(!$detail){
            $query = "SELECT count(distinct(bsca.image_id)) as nb_non_lettre, scat.libelle_new, cat.id, -1 as type_piece_id, i.nom, scat.id, ssc.id ";
        }else{
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, scat.libelle_new, sep.categorie_id, i.valider, ".$montant." as montant, bsca.libelle, -1 as prioriteImageId, scat.id, ssc.id ";
        }
        
        $query .= " FROM banque_sous_categorie_autre bsca
                JOIN image i ON (i.id = bsca.image_id)
                JOIN lot l on (l.id = i.lot_id)
                JOIN separation sep ON (sep.image_id = i.id)
                JOIN souscategorie scat ON (sep.souscategorie_id = scat.id)
                JOIN soussouscategorie ssc on (sep.soussouscategorie_id = ssc.id)
                left join categorie cat on (sep.categorie_id = cat.id)
                left join imputation_controle ic on (i.id = ic.image_id) 
                left join saisie_controle sc on (sc.image_id = i.id) 
                WHERE i.exercice in (".$exercices.") 
                AND l.dossier_id = :DOSSIER_ID AND bsca.image_flague_id IS NULL   
                ".$queryScan."  
                ".$souscategoLib."             
                GROUP BY bsca.image_id, scat.id, ssc.id             
                HAVING 
                (
                    ABS(ROUND(sum(bsca.montant),2)) = ROUND(:montant,2) AND (scat.id = 7 OR ssc.id = 2791) OR 
                    -ABS(ROUND(sum(bsca.montant),2)) = ROUND(:montant_,2) AND (scat.id <> 7 and ssc.id <> 2791)
                )";
        $prep = $pdo->prepare($query);
        $prep->execute($params);
        return $prep->fetchAll();
    }

    public function getDerniereDemandeDrt($dossierId, $exercice)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $chrono = "AND e.date_envoi >= ";
        $chrono .= ":dateDeb";
        $chrono .= " AND e.date_envoi <= ";
        $chrono .= ":dateFin ";
        $query = "SELECT ABS(ei.numero) as rang_ei, date_format(ei.date_creation,'%d/%m/%Y') as date_envoi
            FROM echange e
            INNER JOIN echange_type et on (et.id = e.echange_type_id)
            INNER JOIN echange_item ei on (ei.echange_id = e.id)
            INNER JOIN dossier d on (d.id = e.dossier_id)
            LEFT JOIN echange_reponse ep on (ep.echange_item_id = ei.id)
            WHERE e.exercice = ".$exercice."
            AND (et.id = 1 OR et.id = 2)
            AND ei.status = 0
            AND d.id = ".$dossierId."
            ".$chrono."
            AND ei.supprimer = 0
            ORDER BY d.nom, rang_ei, ep.numero DESC
            LIMIT 1";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'dateDeb' => $exercice.'-01-01',
            'dateFin' => $exercice.'-12-31',
        ));
        return $prep->fetchAll();
    }

    public function getListeImageByDossierCategorie($dossier, $exercice, $categorieId = 16, $dateScanSearch = false, $dateDebut = null, $dateFin = null)
    {
         $qb = $this
            ->createQueryBuilder('i')
            ->leftJoin('i.lot', 'lot')
            ->leftJoin('lot.dossier', 'dossier')
            ->innerJoin('AppBundle:Separation', 'sep', 'WITH', 'sep.image = i')
            ->where('lot.dossier = :the_dossier')
            ->setParameter('the_dossier', $dossier)
            ->andWhere('i.saisie1 > 1 OR i.saisie2 > 1')
            ->andWhere('i.exercice = :exercice')
            ->andWhere('i.supprimer = 0')
            ->andWhere('i.decouper = 0')
            ->setParameter(':exercice', $exercice);
        if($dateScanSearch) {
            if($dateDebut != null && $dateFin != null){
                $qb ->andWhere('lot.dateScan >= :dateDebut')
                    ->setParameter(':dateDebut', $dateDebut)
                    ->andWhere('lot.dateScan <= :dateFin')
                    ->setParameter(':dateFin', $dateFin);
            }
        }
        $categorie = $this->getEntityManager()
                          ->getRepository('AppBundle:Categorie')
                          ->find($categorieId);

        $qb->andWhere('sep.categorie = :categorie')
            ->setParameter('categorie', $categorie);

        return $qb->getQuery()->getResult();
    }

    public function getListeImageSeparationByDossierCategorie($dossierId, $exercice, $categorieId = 16, $dateScanSearch = false, $dateDebut = null, $dateFin = nul)
    {
        $qb = $this->getEntityManager()
                ->getRepository('AppBundle:Separation')
                ->createQueryBuilder('s')
                ->innerJoin('s.image', 'image')
                ->addSelect('image')
                ->leftJoin('s.categorie', 'categorie')
                ->addSelect('categorie')
                ->leftJoin('s.soussouscategorie', 'soussouscategorie')
                ->addSelect('soussouscategorie')
                ->leftJoin('image.lot', 'lot')
                ->addSelect('lot')
                ->innerJoin('lot.dossier', 'dossier')
                ->where('dossier.id = :dossierID')
                ->andWhere('image.exercice = :exercice')
                ->andWhere('image.saisie1 <= 1')
                ->andWhere('image.saisie2 <= 1')
                ->andWhere('image.ctrlSaisie <= 1')
                ->andWhere('image.imputation <= 1')
                ->andWhere('image.ctrlImputation <= 1')
                ->andWhere('image.supprimer = 0')
                ->andWhere('image.decouper = 0')
                ->andWhere('s.categorie = :categorie')
                ->setParameter('exercice', $exercice)
                ->setParameter('dossierID', $dossierId)
                ->setParameter('categorie', $categorieId);
        if($dateScanSearch) {
            if($dateDebut != null && $dateFin != null){
                $qb ->andWhere('lot.dateScan >= :dateDebut')
                    ->setParameter(':dateDebut', $dateDebut)
                    ->andWhere('lot.dateScan <= :dateFin')
                    ->setParameter(':dateFin', $dateFin);
            }
        }
        return $qb->getQuery()->getResult();
    }

    public function getInfosImageByImageId($imageId)
    {
        $qb = $this->createQueryBuilder('i');

        $qb->where('i.id= :imageId')
            ->setParameter('imageId', $imageId)
            ->select('i.id', 'i.saisie1', 'i.saisie2', 'i.ctrlSaisie', 'i.imputation', 'i.ctrlImputation');

        $images = $qb->getQuery()->getResult();

        $res = null;
        $resTable = '';
        $resSeparation = null;
        if (null !== $images) {
            foreach ($images as $img) {
                 $resSeparations = $this->getEntityManager()
                    ->createQuery('SELECT s FROM AppBundle:Separation s WHERE s.image = :image_id')
                    ->setParameter('image_id', $img['id'])
                    ->getResult();

                if(count($resSeparations) > 0){
                    $resSeparation = $resSeparations[0];
                }
                if ($img['ctrlImputation'] > 1) {
                    $resSaisie = $this->getEntityManager()
                        ->createQuery('SELECT t FROM AppBundle:ImputationControle t WHERE t.image = :image_id')
                        ->setParameter('image_id', $img['id'])
                        ->getResult();

                    $resTable = 'Controle Imputation';
                } else if ($img['imputation'] > 1) {
                    $resSaisie = $this->getEntityManager()
                        ->createQuery('SELECT t FROM AppBundle:Imputation t WHERE t.image = :image_id')
                        ->setParameter('image_id', $img['id'])
                        ->getResult();

                    $resTable = 'Imputation';
                } else if ($img['ctrlSaisie'] > 1) {
                    $resSaisie = $this->getEntityManager()
                        ->createQuery('SELECT t FROM AppBundle:SaisieControle t WHERE t.image = :image_id')
                        ->setParameter('image_id', $img['id'])
                        ->getResult();

                    $resTable = 'Controle Saisie';
                } else if ($img['saisie2'] > 1) {
                    $resSaisie = $this->getEntityManager()
                        ->createQuery('SELECT t FROM AppBundle:Saisie2 t WHERE t.image = :image_id')
                        ->setParameter('image_id', $img['id'])
                        ->getResult();

                    $resTable = 'Saisie 2';
                } else if ($img['saisie1'] > 1) {
                    $resSaisie = $this->getEntityManager()
                        ->createQuery('SELECT t FROM AppBundle:Saisie1 t WHERE t.image = :image_id')
                        ->setParameter('image_id', $img['id'])
                        ->getResult();

                    $resTable = 'Saisie 1';
                }
                if (count($resSaisie) > 0) {
                    $res = array(
                        'saisie' => $resSaisie,
                        'separation'=> $resSeparation,
                        'tableSaisie' => $resTable
                    );
                }
            }
        }

        return $res;
    }


    public function getImageSuppr($dossier, $exercice, $datescan, $lot){


        return $this->createQueryBuilder('i')
            ->innerJoin('i.lot', 'lot')
            ->where('lot.dossier = :dossier')
            ->andWhere('lot.dateScan = :datescan')
            ->andWhere('i.exercice = :exercice')
            ->andWhere('lot.lot = :lot')
            ->andWhere('i.supprimer = 0')
            ->setParameter('dossier', $dossier)
            ->setParameter('exercice', $exercice)
            ->setParameter('datescan', $datescan)
            ->setParameter('lot', $lot)
            ->select('i')
            ->orderBy('i.nom')
            ->getQuery()
            ->getResult();
    }


    public function getDateScanImageSuppr($dossierid, $exercice)
    {

        $query = 'SELECT DISTINCT L.date_scan FROM image I INNER JOIN lot L ON L.id = I.lot_id  
                        WHERE I.supprimer = 0 AND I.decouper = 0 AND I.exercice = :exercice AND L.dossier_id = :dossier_id 
                        ORDER BY L.date_scan';

        $param = [
            'dossier_id' => $dossierid,
            'exercice' => $exercice
        ];

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute($param);

        $result = $prep->fetchAll();

        return $result;
    }

    private function getNonLettreesImageIds($dossierid, $exercice, $etape){

        $imageIds = '';

        $exercices = ($exercice-1).','.$exercice.','.($exercice+1);


        $query = 'select abs(round((debit+ credit),2)) as montant from
                            releve r
                            inner join image i on i.id = r.image_id
                            inner join lot l on l.id = i.lot_id
                            where l.dossier_id = :dossier_id
                            and i.exercice in ('.$exercices.')
                            and r.image_flague_id is null group by r.id ';

        $param = [
            'dossier_id' => $dossierid
        ];

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute($param);

        $montantReleves = $prep->fetchAll();


        $imageNonLettrees = [];

        //jerena raha misy mitovy ny total an'ny ligne ao @ tva imputation & ny releve

        if($etape === 'imputee') {
            $query = 'select i.*, abs(round(sum(montant_ttc),2)) as montant_ttc from tva_imputation_controle t
                    inner join image i on t.image_id = i.id
                    inner join lot l on l.id = i.lot_id                   
                    where i.exercice IN (' . $exercices . ')
                    and l.dossier_id = :dossier_id
                    and i.decouper = 0
                    and i.supprimer = 0
                    and t.image_flague_id is null             
                    AND NOT EXISTS (SELECT 1 FROM ecriture WHERE ecriture.image_id = i.id)  
                    group by i.id                      
                     ';


            $param = [
                'dossier_id' => $dossierid
            ];

            $prep = $pdo->prepare($query);
            $prep->execute($param);

            $images = $prep->fetchAll();

            foreach ($montantReleves as $montantReleve){
                $montantRef = $montantReleve->montant;

                foreach ($images as $image){
                    if(($image->montant_ttc - $montantRef) == 0){
                        $imageNonLettrees[] = $image;
                        if($imageIds === ''){
                            $imageIds .= $image->id;
                        }
                        else{
                            $imageIds .= ','.$image->id;
                        }
                    }
                }
            }
        }
        else if($etape == 'revisee') {
            $query = '
             select distinct image_id from ecriture e inner join image i on i.id = e.image_id
                    inner join lot l on l.id = i.lot_id
                    where l.dossier_id = :dossier_id and i.exercice = :exercice and image_id not in (select distinct image_id from ecriture e inner join image i on i.id = e.image_id
                    inner join lot l on l.id = i.lot_id
                    where l.dossier_id = :dossier_id2 and i.exercice = :exercice2 and lettrage <> "" and lettrage is not null)';

            $param = [
                'dossier_id' => $dossierid,
                'dossier_id2' => $dossierid,
                'exercice' => $exercice,
                'exercice2' => $exercice
            ];

            $prep = $pdo->prepare($query);
            $prep->execute($param);

            $ecritures = $prep->fetchAll();


            foreach ($ecritures as $ecriture) {
                if($imageIds === ''){
                    $imageIds .= $ecriture->image_id;
                }
                else{
                    $imageIds .= ','.$ecriture->image_id;
                }
            }

        }

        if($imageIds === '')
            $imageIds = -1;

        return $imageIds;
    }


}