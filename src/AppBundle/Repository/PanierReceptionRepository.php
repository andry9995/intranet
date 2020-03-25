<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 12/07/2016
 * Time: 10:52
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Site;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class PanierReceptionRepository extends EntityRepository
{
    public function getPanierNiv1()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

//        $query = "SELECT P.*,L.date_scan,D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,
//                S.nom AS site,C.id AS client_id,C.nom AS client,COUNT(I.id) AS nb_image
//                FROM panier_reception P
//                INNER JOIN operateur O ON(P.operateur_id=O.id)
//                INNER JOIN etape_traitement etape ON(P.etape_traitement_id=etape.id)
//                INNER JOIN lot L ON(P.lot_id=L.id)
//                INNER JOIN image I ON(I.lot_id=L.id)
//                INNER JOIN image_a_traiter A ON(A.image_id=I.id)
//                INNER JOIN dossier D ON(L.dossier_id=D.id)
//                INNER JOIN site S ON(D.site_id=S.id)
//                INNER JOIN client C ON(S.client_id=C.id)
//                WHERE A.status = :status AND L.status = :lstatus AND P.status = :pstatus AND etape.code = :code_etape
//                GROUP BY P.id,L.id
//                ORDER BY P.operateur_id,P.date_panier";


        $query = "SELECT P.*,L.date_scan,L.dossier_id,'dossier','cloture','site_id',
                'site','client_id','client',COUNT(I.id) AS nb_image 
                FROM panier_reception P
                INNER JOIN operateur O ON(P.operateur_id=O.id)
                INNER JOIN etape_traitement etape ON(P.etape_traitement_id=etape.id)
                INNER JOIN lot L ON(P.lot_id=L.id)
                INNER JOIN image I ON(I.lot_id=L.id)
                INNER JOIN image_a_traiter A ON(A.image_id=I.id)
                WHERE A.status = :status AND L.status = :lstatus AND P.status = :pstatus AND etape.code = :code_etape
                GROUP BY P.id,L.id
                ORDER BY P.operateur_id,P.date_panier";


        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 1,
            'lstatus' => 1,
            'pstatus' => 0,
            'code_etape' => 'DEC_NIV_1',
        ));
        $lots = $prep->fetchAll();

        for ($i = 0; $i < count($lots); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($lots[$i]->dossier_id);
            if($dossier) {
                /** @var Site $site */
                $site = $dossier->getSite();
                /** @var Client $client */
                $client = $site->getClient();

                $lots[$i]->dossier = $dossier->getNom();
                $lots[$i]->site_id = $site->getId();
                $lots[$i]->site = $site->getNom();
                $lots[$i]->client_id = $client->getId();
                $lots[$i]->client = $client->getNom();
                $lots[$i]->cloture = $dossier->getCloture();

            }
            $lots[$i]->date_panier = new  \DateTime($lots[$i]->date_panier);
            $lots[$i]->date_scan = new  \DateTime($lots[$i]->date_scan);
            if (!$lots[$i]->cloture || $lots[$i]->cloture == 0) {
                $lots[$i]->cloture = 12;
            }

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$i]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$i]->priorite = NULL;
                $lots[$i]->tache = '';
                $lots[$i]->color = '#696dcb';
                $lots[$i]->order = 9000;
            }
        }

        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });

        return $lots;
    }

    public function getPanierNiv2()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

//        $query = "SELECT P.*,L.date_scan,D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,
//                S.nom AS site,C.id AS client_id,C.nom AS client,COUNT(I.id) AS nb_image
//                FROM panier_reception P
//                INNER JOIN operateur O ON(P.operateur_id=O.id)
//                INNER JOIN etape_traitement etape ON(P.etape_traitement_id=etape.id)
//                INNER JOIN lot L ON(P.lot_id=L.id)
//                INNER JOIN image I ON(I.lot_id=L.id)
//                INNER JOIN image_a_traiter A ON(A.image_id=I.id)
//                INNER JOIN dossier D ON(L.dossier_id=D.id)
//                INNER JOIN site S ON(D.site_id=S.id)
//                INNER JOIN client C ON(S.client_id=C.id)
//                WHERE A.status = :status AND L.status = :lstatus AND P.status = :pstatus AND etape.code = :code_etape
//                GROUP BY P.id,L.id
//                ORDER BY P.operateur_id,P.date_panier";


        $query = "SELECT P.*,L.date_scan,L.dossier_id,'dossier','cloture','site_id',
                'site','client_id','client',((select count(distinct image_id) as nb from decoupage_niveau2 where lot_id=L.id) 
                + (select count(nomdecoupee) as nbfille from decoupage_niveau2 where lot_id=L.id and nomdecoupee!='')) as nb_image
                FROM panier_reception P
                INNER JOIN operateur O ON(P.operateur_id=O.id)
                INNER JOIN etape_traitement etape ON(P.etape_traitement_id=etape.id)
                INNER JOIN lot L ON(P.lot_id=L.id)
                INNER JOIN image I ON(I.lot_id=L.id)
                INNER JOIN image_a_traiter A ON(A.image_id=I.id)
                WHERE A.status = :status AND L.status = :lstatus AND P.status = :pstatus AND etape.code = :code_etape
                GROUP BY P.id,L.id
                ORDER BY P.operateur_id,P.date_panier";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 3,
            'lstatus' => 3,
            'pstatus' => 0,
            'code_etape' => 'DEC_NIV_2',
        ));
        $lots = $prep->fetchAll();

        for ($i = 0; $i < count($lots); $i++) {

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($lots[$i]->dossier_id);
            if($dossier) {
                /** @var Site $site */
                $site = $dossier->getSite();
                /** @var Client $client */
                $client = $site->getClient();

                $lots[$i]->dossier = $dossier->getNom();
                $lots[$i]->site_id = $site->getId();
                $lots[$i]->site = $site->getNom();
                $lots[$i]->client_id = $client->getId();
                $lots[$i]->client = $client->getNom();
                $lots[$i]->cloture = $dossier->getCloture();
            }


            $lots[$i]->date_panier = new  \DateTime($lots[$i]->date_panier);
            $lots[$i]->date_scan = new  \DateTime($lots[$i]->date_scan);
            if (!$lots[$i]->cloture || $lots[$i]->cloture == 0) {
                $lots[$i]->cloture = 12;
            }

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$i]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$i]->priorite = NULL;
                $lots[$i]->tache = '';
                $lots[$i]->color = '#696dcb';
                $lots[$i]->order = 9000;
            }
        }

        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });

        return $lots;
    }

    public function nettoyerUserAControler()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "DELETE FROM user_a_controler WHERE operateur_id IN (SELECT id FROM operateur WHERE supprimer=:supprimer)";
        $prep = $pdo->prepare($query);
        $prep->execute(array('supprimer'=>1));
        return true;
    }

    public function getPanierPerUser(Operateur $operateur, EtapeTraitement $etapeTraitement, &$image)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT P.*,count(I.id) as nbimage, ETAPE.libelle AS etape,L.date_scan AS datescan,L.lot,I.exercice,
                D.id AS dossier_id,D.nom AS dossier,S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client  
                FROM panier_reception P INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                INNER JOIN etape_traitement ETAPE ON(P.etape_traitement_id = ETAPE.id)
                INNER JOIN lot L ON(P.lot_id = L.id)
                INNER JOIN image I ON(I.lot_id = L.id)
                INNER JOIN dossier D ON(L.dossier_id = D.id)
                INNER JOIN site S ON(D.site_id = S.id)
                INNER JOIN client C ON(S.client_id = C.id)
                WHERE OP.id = :operateur_id AND ETAPE.id = :etape_id AND P.status = 0
                GROUP BY C.id,C.id,L.id,I.exercice
                ORDER BY P.date_panier";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'operateur_id' => $operateur->getId(),
            'etape_id' => $etapeTraitement->getId(),
        ));
        $res = $prep->fetchAll();
        $image = 0;
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
            $image += intval($res[$i]->nbimage);
        }
        return $res;
    }

    public function getPanierPerUser2(Operateur $operateur, EtapeTraitement $etapeTraitement, &$image)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT P.*,((select count(distinct image_id) as nb from decoupage_niveau2 where lot_id=L.id) 
                + (select count(nomdecoupee) as nbfille from decoupage_niveau2 where lot_id=L.id and nomdecoupee!='')) as nbimage, ETAPE.libelle AS etape,L.date_scan AS datescan,L.lot,I.exercice,
                D.id AS dossier_id,D.nom AS dossier,S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client  
                FROM panier_reception P INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                INNER JOIN etape_traitement ETAPE ON(P.etape_traitement_id = ETAPE.id)
                INNER JOIN lot L ON(P.lot_id = L.id)
                INNER JOIN image I ON(I.lot_id = L.id)
                INNER JOIN dossier D ON(L.dossier_id = D.id)
                INNER JOIN site S ON(D.site_id = S.id)
                INNER JOIN client C ON(S.client_id = C.id)
                WHERE OP.id = :operateur_id AND ETAPE.id = :etape_id AND P.status = 0
                GROUP BY C.id,C.id,L.id,I.exercice
                ORDER BY P.date_panier";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'operateur_id' => $operateur->getId(),
            'etape_id' => $etapeTraitement->getId(),
        ));
        $res = $prep->fetchAll();
        $image = 0;
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
            $image += intval($res[$i]->nbimage);
        }
        return $res;
    }

    public function getPanierFiniPerUserToday_1(Operateur $operateur, EtapeTraitement $etapeTraitement, &$image)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $now = new \DateTime();
        $query = "SELECT P.*,((select count(distinct image_id) as nb from decoupage_niveau1 where lot_id=L.id) 
                + (select count(nomdecoupee) as nbfille from decoupage_niveau1 where lot_id=L.id and nomdecoupee!='')) as nbimage, ETAPE.libelle AS etape,L.date_scan AS datescan,L.lot,I.exercice,
                D.id AS dossier_id,D.nom AS dossier,S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client  
                FROM panier_reception P INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                INNER JOIN etape_traitement ETAPE ON(P.etape_traitement_id = ETAPE.id)
                INNER JOIN lot L ON(P.lot_id = L.id)
                INNER JOIN image I ON(I.lot_id = L.id)
                INNER JOIN dossier D ON(L.dossier_id = D.id)
                INNER JOIN site S ON(D.site_id = S.id)
                INNER JOIN client C ON(S.client_id = C.id)
                INNER JOIN logs Lo ON Lo.lot_id=L.id
                WHERE Date(Lo.date_fin) = :date_panier AND OP.id = :operateur_id AND ETAPE.id = :etape_id AND P.status = 1
                GROUP BY C.id,C.id,L.id,I.exercice
                ORDER BY P.date_panier desc, P.id "; // C.nom,D.nom,L.date_scan";

        $prep = $pdo->prepare($query);
        //$daty = date('Y-m-d', strtotime('-7 day'));
        $prep->execute(array(
            //'date_panier' => date('Y-m-d', strtotime('-3 day')),
            'date_panier' => $now->format('Y-m-d'),
//$now->format('Y-m-d'),
            'operateur_id' => $operateur->getId(),
            'etape_id' => $etapeTraitement->getId(),
        ));
        $res = $prep->fetchAll();
        $image = 0;
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
            $image += intval($res[$i]->nbimage);
        }
        return $res;
    }

    public function getPanierFiniPerUserToday_2(Operateur $operateur, EtapeTraitement $etapeTraitement, &$image)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $now = new \DateTime();
        $query = "SELECT P.*,((select count(distinct image_id) as nb from decoupage_niveau2 where lot_id=L.id) 
                + (select count(nomdecoupee) as nbfille from decoupage_niveau2 where lot_id=L.id and nomdecoupee!='')) as nbimage, ETAPE.libelle AS etape,L.date_scan AS datescan,L.lot,I.exercice,
                D.id AS dossier_id,D.nom AS dossier,S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client  
                FROM panier_reception P INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                INNER JOIN etape_traitement ETAPE ON(P.etape_traitement_id = ETAPE.id)
                INNER JOIN lot L ON(P.lot_id = L.id)
                INNER JOIN image I ON(I.lot_id = L.id)
                INNER JOIN dossier D ON(L.dossier_id = D.id)
                INNER JOIN site S ON(D.site_id = S.id)
                INNER JOIN client C ON(S.client_id = C.id)
                INNER JOIN logs Lo ON Lo.lot_id=L.id
                WHERE Date(Lo.date_fin) >= :date_panier AND OP.id = :operateur_id AND ETAPE.id = :etape_id AND P.status = 1
                GROUP BY C.id,C.id,L.id,I.exercice
                ORDER BY P.date_panier desc, P.id "; // C.nom,D.nom,L.date_scan";

        $prep = $pdo->prepare($query);

        $prep->execute(array(
            'date_panier' => $now->format('Y-m-d'),
            'operateur_id' => $operateur->getId(),
            'etape_id' => $etapeTraitement->getId(),
        ));
        $res = $prep->fetchAll();
        $image = 0;
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
            $image += intval($res[$i]->nbimage);
        }
        return $res;
    }

    public function getPanierFiniPerUser(Operateur $operateur, EtapeTraitement $etapeTraitement, &$image)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $now = new \DateTime();
        $query = "SELECT P.*,((select count(distinct image_id) as nb from decoupage_niveau1 where lot_id=L.id) 
                + (select count(nomdecoupee) as nbfille from decoupage_niveau1 where lot_id=L.id and nomdecoupee!='')) as nbimage, ETAPE.libelle AS etape,L.date_scan AS datescan,L.lot,I.exercice,
                D.id AS dossier_id,D.nom AS dossier,S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client  
                FROM panier_reception P INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                INNER JOIN etape_traitement ETAPE ON(P.etape_traitement_id = ETAPE.id)
                INNER JOIN lot L ON(P.lot_id = L.id)
                INNER JOIN image I ON(I.lot_id = L.id)
                INNER JOIN dossier D ON(L.dossier_id = D.id)
                INNER JOIN site S ON(D.site_id = S.id)
                INNER JOIN client C ON(S.client_id = C.id)
                INNER JOIN logs lo ON L.id=lo.lot_id
                WHERE Date(lo.date_fin) >= :date_panier AND OP.id = :operateur_id AND ETAPE.id = :etape_id AND P.status = 1
                GROUP BY C.id,C.id,L.id,I.exercice
                ORDER BY P.date_panier desc, P.id "; // C.nom,D.nom,L.date_scan";

        $prep = $pdo->prepare($query);
        $daty = date('Y-m-d', strtotime('-7 day'));
        $prep->execute(array(
            'date_panier' => date('Y-m-d', strtotime('-3 day')),
//$now->format('Y-m-d'),
            'operateur_id' => $operateur->getId(),
            'etape_id' => $etapeTraitement->getId(),
        ));
        $res = $prep->fetchAll();
        $image = 0;
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
            $image += intval($res[$i]->nbimage);
        }
        return $res;
    }

    public function getPanierFiniPerUser2(Operateur $operateur, EtapeTraitement $etapeTraitement, &$image)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $now = new \DateTime();
        $query = "SELECT P.*,((select count(distinct image_id) as nb from decoupage_niveau2 where lot_id=L.id) 
                + (select count(nomdecoupee) as nbfille from decoupage_niveau2 where lot_id=L.id and nomdecoupee!='')) as nbimage, ETAPE.libelle AS etape,L.date_scan AS datescan,L.lot,I.exercice,
                D.id AS dossier_id,D.nom AS dossier,S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client  
                FROM panier_reception P INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                INNER JOIN etape_traitement ETAPE ON(P.etape_traitement_id = ETAPE.id)
                INNER JOIN lot L ON(P.lot_id = L.id)
                INNER JOIN image I ON(I.lot_id = L.id)
                INNER JOIN dossier D ON(L.dossier_id = D.id)
                INNER JOIN site S ON(D.site_id = S.id)
                INNER JOIN client C ON(S.client_id = C.id)
                INNER JOIN logs lo ON L.id=lo.lot_id
                WHERE Date(lo.date_fin) >= :date_panier  AND OP.id = :operateur_id AND ETAPE.id = :etape_id AND P.status = 1
                GROUP BY C.id,C.id,L.id,I.exercice
                ORDER BY P.date_panier desc, P.id "; // C.nom,D.nom,L.date_scan";

        $prep = $pdo->prepare($query);
        $daty = date('Y-m-d', strtotime('-7 day'));
        $prep->execute(array(
            'date_panier' => date('Y-m-d', strtotime('-3 day')),
//$now->format('Y-m-d'),
            'operateur_id' => $operateur->getId(),
            'etape_id' => $etapeTraitement->getId(),
        ));
        $res = $prep->fetchAll();
        $image = 0;
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
            $image += intval($res[$i]->nbimage);
        }
        return $res;
    }
}