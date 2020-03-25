<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 06/06/2016
 * Time: 14:31
 */

namespace AppBundle\Repository;

use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Image;
use AppBundle\Entity\Lot;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Site;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class PanierRepository extends EntityRepository
{
    public function getLotGroupeSaisie1($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  (SELECT id FROM operateur WHERE id IN (SELECT operateur_rat_id FROM rattachement WHERE operateur_id IN
                  (SELECT operateur_id FROM rattachement WHERE operateur_rat_id=OP.id)) AND organisation_id=134) as userCtrl_id
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0 AND OP.id IN 
                  (SELECT operateur_rat_id FROM rattachement r JOIN operateur o ON r.operateur_rat_id=o.id WHERE o.organisation_id=167)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 1,
            'decouper' => 0,
            'code_etape' => 'OS_1',
            'userId' => $userId,

        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;

    }


    /**
     * Panier Saisie 1 de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierTenueSaisie2($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                   
                   (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where o.id!=:currentUserId AND r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId)
                   
                   as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  0 as affect_tenue
                  
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0 AND CAT.id != :categorieId AND C.id in
                    (SELECT client FROM responsable_client WHERE responsable in
                        (SELECT operateur_id from rattachement  where  operateur_rat_id!=:userId and operateur_rat_id in 
                        (select id from operateur where organisation_id=:organisationId))) AND I.supprimer=:supprimer
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'currentUserId' => $userId,
            'userId' => $userId,
            'status' => 4,
            'saisie2' => 1,
            'decouper' => 0,
            'categorieId' => 16,
            'code_etape'=>'OS_2',
            'organisationId'=>208,
            'chefTenueId'=>208,
            'manager'=>109,
            'supprimer'=> 0,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                   
                   (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where o.id!=:currentUserId AND r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId)
                   
                   as operateur_id,P.id AS panier_id, CURRENT_DATE() as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  1 as affect_tenue
                  
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE CAT.id!=:categorieId AND P.fini=:panierFini AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE code=:code_etape AND fini=:fini AND operateur_id!=:userId)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'currentUserId' => $userId,
            'userId' => $userId,
            'code_etape'=>'OS_2',
            'chefTenueId'=>208,
            'manager'=>109,
            'fini' =>0,
            'panierFini'=>0,
            'categorieId' => 16,
        ));
        $res = $prep->fetchAll();

        
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
            $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
            $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
                //->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }

        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        //$value3->date_panier = new \DateTime($value3->date_panier);
                        $value3->date_panier = $value3->date_panier;
                        $lots[] = $value3;
                    }
                }
            }
        }

        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier Saisie 2 de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierGroupeSaisie2($to_array = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  (SELECT id FROM operateur WHERE id IN (SELECT operateur_rat_id FROM rattachement WHERE operateur_id IN
                  (SELECT operateur_id FROM rattachement WHERE operateur_rat_id=OP.id)) AND organisation_id=134) as userCtrl_id
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0 AND OP.id IN 
                  (SELECT operateur_rat_id FROM rattachement r JOIN operateur o ON r.operateur_rat_id=o.id WHERE o.organisation_id=167)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 1,
            'decouper' => 0,
            'code_etape' => 'OS_2',

        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    /**
     * Panier Contrôle Saisie de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierTenueCtrl($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                  (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId) as operateur_id,
                    P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  0 as affect_tenue
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND A.decouper = :decouper AND etape.code = :code_etape AND P.fini = 0
                  AND OP.id != :tenueId AND
                  C.id in
                    (SELECT client FROM responsable_client WHERE responsable in
                        (SELECT operateur_id from rattachement  where  operateur_rat_id!=:userId and operateur_rat_id in 
                        (select id from operateur where organisation_id=:organisationId)))
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'tenueId' => $userId,
            'userId' => $userId,
            'status' => 5,
            'saisie1' => 2,
            'saisie2' => 2,
            'decouper' => 0,
            'code_etape' => 'CTRL_OS',
            'organisationId'=>208,
            'manager'=>109,
            'chefTenueId'=>208,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }



        /** Récupérer lot dans affectation tenue*/
        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                  (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId) as operateur_id,
                    P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  1 as affect_tenue
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE CAT.id!=:categorieId AND P.fini=:panierFini AND L.id IN 
                  (SELECT lot_id FROM affectation_panier_tenue WHERE code=:code_etape AND fini=:fini AND operateur_id!=:userId)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId' => $userId,
            'code_etape' => 'CTRL_OS',
            'manager'=>109,
            'chefTenueId'=>208,
            'categorieId'=> 16,
            'panierFini' => 0,
            'fini' =>0,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }

        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    /**
     * Panier Imputation Saisie de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierTenueImp($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                  (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId) as operateur_id,
                    P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  0 as affect_tenue
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND A.decouper = :decouper AND etape.code = :code_etape AND P.fini = 0
                  AND OP.id != :tenueId AND
                  C.id in
                    (SELECT client FROM responsable_client WHERE responsable in
                        (SELECT operateur_id from rattachement  where  operateur_rat_id!=:userId and operateur_rat_id in 
                        (select id from operateur where organisation_id=:organisationId)))
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'tenueId' => $userId,
            'userId' => $userId,
            'status' => 5,
            'saisie1' => 2,
            'saisie2' => 2,
            'decouper' => 0,
            'code_etape' => 'CTRL_OS',
            'organisationId'=>208,
            'manager'=>109,
            'chefTenueId'=>208,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }



        /** Récupérer lot dans affectation tenue*/
        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                  (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId) as operateur_id,
                    P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  1 as affect_tenue
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE CAT.id!=:categorieId AND P.fini=:panierFini AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE code=:code_etape AND fini=:fini AND operateur_id!=:userId)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId' => $userId,
            'code_etape' => 'CTRL_OS',
            'manager'=>109,
            'chefTenueId'=>208,
            'categorieId'=> 16,
            'panierFini' => 0,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }

        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier Saisie 1 de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierTenueSaisie1($userId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                   
                   (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where o.id!=:currentUserId AND r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId)
                   
                   as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client, 0 as affect_tenue
                  
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0 AND CAT.id != :categorieId AND C.id in
                    (SELECT client FROM responsable_client WHERE responsable in
                        (SELECT operateur_id from rattachement  where  operateur_rat_id!=:userId and operateur_rat_id in 
                        (select id from operateur where organisation_id=:organisationId))) AND I.supprimer=:supprimer
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'currentUserId'=> $userId,
            'userId' => $userId,
            'status' => 4,
            'saisie1' => 1,
            'decouper' => 0,
            'categorieId' => 16,
            'code_etape'=>'OS_1',
            'organisationId'=>208,
            'chefTenueId'=>208,
            'manager'=>109,
            'supprimer'=> 0,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
            $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
            $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
                //->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }


        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,
                   (select o.id from operateur o JOIN rattachement r on o.id=r.operateur_rat_id 
                     where o.id!=:currentUserId AND r.operateur_id in 
                     (select id from operateur  where id in (
                    select responsable from responsable_client where client=C.id) and organisation_id=:manager) and organisation_id=:chefTenueId)
                   
                   as operateur_id,P.id AS panier_id, CURRENT_DATE() as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  1 as affect_tenue
                  
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE CAT.id!=:categorieId AND P.fini=:panierFini AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE code=:code_etape AND fini=:fini AND operateur_id!=:userId)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'currentUserId' => $userId,
            'userId' => $userId,
            'code_etape'=>'OS_1',
            'chefTenueId'=>208,
            'manager'=>109,
            'fini' =>0,
            'panierFini'=>0,
            'categorieId' => 16,
        ));
        $res = $prep->fetchAll();

        
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
            $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
            $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
                //->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        //$value3->date_panier = new \DateTime($value3->date_panier);
                      $value3->date_panier = $value3->date_panier;
                        $lots[] = $value3;
                    }
                }
            }
        }

        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier Saisie 1 de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierGroupeSaisie1($to_array = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  (SELECT id FROM operateur WHERE id IN (SELECT operateur_rat_id FROM rattachement WHERE operateur_id IN
                  (SELECT operateur_id FROM rattachement WHERE operateur_rat_id=OP.id)) AND organisation_id=134) as userCtrl_id
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0 AND OP.id IN 
                  (SELECT operateur_rat_id FROM rattachement r JOIN operateur o ON r.operateur_rat_id=o.id WHERE o.organisation_id=167)
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 1,
            'decouper' => 0,
            'code_etape' => 'OS_1',

        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    /**
     * Panier Saisie 1 de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierSaisie1($userId, $to_array = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE S.id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_id in 
                    (select operateur_id from rattachement rat WHERE rat.operateur_rat_id in
                    (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId))))  AND
                  A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 1,
            'decouper' => 0,
            'code_etape' => 'OS_1',
            'userId' => $userId,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    public function getPanierSaisie2Banque($exercice,$operateur = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(I.id) AS nbimage, SEP.categorie_id AS categorie_id,I.exercice,
                  P.operateur_id, P.date_panier, SC.banque_compte_id,
                  SEP.souscategorie_id as souscategorie_id, 'categorie','souscategorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client',
                  SC.banque_compte_id, 'banque', 'num_compte'
                  FROM image I 
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id) 
                  INNER JOIN lot L ON(I.lot_id=L.id)                 
                  INNER JOIN saisie_controle SC on I.id = SC.image_id AND SC.banque_compte_id IS NOT NULL 
                  WHERE I.ctrl_saisie >= :ctrl_saisie AND I.decouper = :decouper AND I.supprimer = :supprimer
                  AND SEP.categorie_id = :categorie AND SEP.souscategorie_id = :souscategorie_id                             
                  AND P.etape_traitement_id = :etape_traitement_id AND P.fini = :fini
                  AND I.exercice >= :exercice AND I.valider <> :valider ";

        if ($operateur !== null) {
            $query .= " AND OP.id = :operateur";
        }
        $query .=" GROUP BY SC.banque_compte_id,I.exercice LIMIT 10000";


        $prep = $pdo->prepare($query);

        $param = [
            'ctrl_saisie' => 3,
            'decouper' => 0,
            'supprimer' => 0,
            'categorie' => 16,
            'souscategorie_id' => 10,
            'etape_traitement_id' => 26,
            'fini' => 0,
            'exercice' => $exercice,
            'valider' => 100
        ];


        if ($operateur !== null)
            $param['operateur'] = $operateur;

        $prep->execute($param);

        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {

            if($res[$i]->banque_compte_id != ''){
                /** @var BanqueCompte $banquecompte */
                $banquecompte = $this->getEntityManager()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->find($res[$i]->banque_compte_id);

                if($banquecompte){
                    $res[$i]->num_compte = $banquecompte->getNumcompte();
                    $res[$i]->banque = $banquecompte->getBanque()->getNom();
                }
            }

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            /** @var Site $site */
            $site = $dossier->getSite();

            $res[$i]->dossier = $dossier->getNom();
            $res[$i]->cloture = $dossier->getCloture();

            $res[$i]->categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id)
                ->getLibelleNew();

            $res[$i]->souscategorie = $this->getEntityManager()
                ->getRepository('AppBundle:Souscategorie')
                ->find($res[$i]->souscategorie_id)
                ->getLibelleNew();

            $res[$i]->site_id = $site->getId();
            $res[$i]->site = $site->getNom();

            $res[$i]->client_id = $site->getClient()->getId();
            $res[$i]->client = $site->getClient()->getNom();

            if (!isset($items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);

                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->banque_compte_id.'-'.$res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function ($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    public function getPanierBanque($releve = null, $operateur = null, $souscategorie = null, $soussouscategorie = null, $etape = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

//        $obIds =[1,5,6,7,8, 937, 939, 941];
        $obIds =[5,6,7,8, 937, 939, 941];

        if($etape !== 'BQ_DETAILS') {

            $query = "SELECT COUNT(A.image_id) AS nbimage, SEP.categorie_id AS categorie_id,
                  'categorie', SEP.souscategorie_id AS souscategorie_id, 'souscategorie',SEP.soussouscategorie_id,
                  OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,
                  S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client, I.exercice                
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id) ";

            if ($operateur !== null)
                $query .= " AND OP.id = :operateur";

            $query .= " INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON (SEP.image_id=I.id) AND SEP.categorie_id = 16";

            if ($souscategorie !== null) {
                $query .= " AND SEP.souscategorie_id = :souscategorie";

                if($souscategorie == 153){
                    $soussouscategorie = 1905;
                }

                if ($soussouscategorie !== null) {
                    $query .= " AND SEP.soussouscategorie_id = :soussouscategorie";
                }

            } else if ($releve === true) {
                $query .= " AND SEP.souscategorie_id = 10";
            } else if ($releve === false) {
//                $query .= " AND SEP.souscategorie_id <> 10";
                $query .= " AND (SEP.souscategorie_id IN (".implode(',', $obIds).") 
                OR (SEP.souscategorie_id = 153 AND SEP.soussouscategorie_id = 1905))";
            }

//            $query .= " WHERE A.status = :status AND I.supprimer = :supprimer AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
//                  AND P.fini = 0
//                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";


            $query .= " WHERE A.status = :status AND I.supprimer = :supprimer 
                            AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                            AND P.fini = 0
                            GROUP BY client_id,site_id,dossier_id,operateur_id,categorie_id, exercice";
//                            GROUP BY client_id,site_id,dossier_id,operateur_id,date_panier,categorie_id, exercice";


            $prep = $pdo->prepare($query);

            $param = ['status' => 4, 'saisie1' => 1, 'decouper' => 0, 'supprimer' => 0,'code_etape' => 'OS_1'];

            if ($operateur !== null)
                $param['operateur'] = $operateur;

            if ($souscategorie !== null) {
                $param['souscategorie'] = $souscategorie;

                if ($soussouscategorie !== null) {
                    $param['soussouscategorie'] = $soussouscategorie;
                }
            }

            $prep->execute($param);

            $res = $prep->fetchAll();
        }
        else{
            $query = "SELECT COUNT(I.id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', SEP.souscategorie_id AS souscategorie_id, 'souscategorie',SEP.soussouscategorie_id, 
                  OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,
                  S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client,
                  SC.banque_compte_id, 'banque', 'num_compte', I.exercice
                  FROM image I 
                  INNER JOIN saisie_controle SC on I.id = SC.image_id AND SC.banque_compte_id IS NOT NULL 
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id) ";

            if ($operateur !== null)
                $query .= " AND OP.id = :operateur";

            $query .= " INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON (SEP.image_id=I.id) AND SEP.categorie_id = 16";



//            $query .= " WHERE  I.decouper = :decouper AND I.supprimer = :supprimer AND etape.code = :code_etape
//                  AND P.fini = 0
//                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";


            $query .= " WHERE  I.decouper = :decouper AND I.supprimer = :supprimer AND etape.code = :code_etape
                  AND P.fini = 0
                    GROUP BY banque_compte_id,operateur_id,exercice";


            $prep = $pdo->prepare($query);

            $param = ['decouper' => 0, 'code_etape' => 'BQ_DETAILS', 'supprimer' => 0];

            if ($operateur !== null)
                $param['operateur'] = $operateur;



            $prep->execute($param);

            $res = $prep->fetchAll();
        }

        $items = [];
        for ($i = 0; $i < count($res); $i++) {

            if($etape === 'BQ_DETAILS') {
                if ($res[$i]->banque_compte_id != '') {
                    /** @var BanqueCompte $banquecompte */
                    $banquecompte = $this->getEntityManager()
                        ->getRepository('AppBundle:BanqueCompte')
                        ->find($res[$i]->banque_compte_id);

                    if ($banquecompte) {
                        $res[$i]->num_compte = $banquecompte->getNumcompte();
                        $res[$i]->banque = $banquecompte->getBanque()->getNom();
                    }
                }
            }

            $res[$i]->categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id)
                ->getLibelleNew();

            $res[$i]->souscategorie  = $this->getEntityManager()
                ->getRepository('AppBundle:Souscategorie')
                ->find($res[$i]->souscategorie_id)
                ->getLibelleNew();


            if($etape !== 'BQ_DETAILS') {
                if (!isset($items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
                } else {
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
                }

                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);
                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);

                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                    $items[$res[$i]->dossier_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
                }
            }
            else{
                if (!isset($items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
                } else {
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
                }

                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);

                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                    $items[$res[$i]->banque_compte_id . '-' . $res[$i]->exercice][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
                }

            }

        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    public function getPanierBanqueImputation($to_array = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.id AS panier_id, P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id) AND CAT.id = 16
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";


        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 2,
            'decouper' => 0,
            'code_etape' => 'IMP'
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    /**
     * Panier Saisie 2 de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierSaisie2($userId, $to_array = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE  S.id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_id in 
                    (select operateur_id from rattachement rat WHERE rat.operateur_rat_id in
                    (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId))))  AND A.status = :status AND A.saisie2 = :saisie2 AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie2' => 1,
            'decouper' => 0,
            'code_etape' => 'OS_2',
            'userId'=>$userId,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier Contrôle Saisie de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierCtrlSaisie($userId, $to_array = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE S.id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_id in 
                    (select operateur_id from rattachement rat WHERE rat.operateur_rat_id in
                    (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId))))  AND A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND A.decouper = :decouper AND etape.code = :code_etape AND P.fini = 0
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 5,
            'saisie1' => 2,
            'saisie2' => 2,
            'decouper' => 0,
            'code_etape' => 'CTRL_OS',
            'userId' => $userId,
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier Imputation de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierImputation()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 7,
            'decouper' => 0,
            'code_etape' => 'IMP'
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier Contrôle Imputation de Tous
     *
     * @param bool $to_array
     * @return array
     */
    public function getPanierCtrlImputation()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  CAT.libelle AS categorie,OP.id as operateur_id,P.date_panier as date_panier,
                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id = I.id)
                  INNER JOIN panier P ON(P.image_id = I.id)
                  INNER JOIN operateur OP ON(P.operateur_id = OP.id)
                  INNER JOIN etape_traitement etape ON (P.etape_traitement_id = etape.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier D ON(L.dossier_id = D.id)
                  INNER JOIN site S ON (D.site_id = S.id)
                  INNER JOIN client C ON (S.client_id = C.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
                  WHERE A.status = :status AND A.decouper = :decouper AND etape.code = :code_etape
                  AND P.fini = 0
                  GROUP BY client_id,site_id,dossier_id,lot_id,operateur_id,date_panier,categorie_id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 9,
            'decouper' => 0,
            'code_etape' => 'CTRL_IMP'
        ));
        $res = $prep->fetchAll();

        $items = [];
        for ($i = 0; $i < count($res); $i++) {
            if (!isset($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier])) {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier] = $res[$i];
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->nbimage += $res[$i]->nbimage;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->categorie .= "/" . $res[$i]->categorie;
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->priorite = NULL;
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->tache = '';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->color = '#696dcb';
                $items[$res[$i]->lot_id][$res[$i]->operateur_id][$res[$i]->date_panier]->order = 9000;
            }
        }
        $lots = [];
        if (count($items) > 0) {
            foreach ($items as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    foreach ($value2 as $key3 => $value3) {
                        $value3->date_panier = new \DateTime($value3->date_panier);
                        $lots[] = $value3;
                    }
                }
            }
        }
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Panier d'un lot d'un utilisateur
     * à une date panier donnée
     *
     * @param Operateur $operateur
     * @param EtapeTraitement $etapeTraitement
     * @param Lot $lot
     * @param \DateTime $date_panier
     * @param bool $to_array
     * @return array
     */
    public function getOneLot(Operateur $operateur, EtapeTraitement $etapeTraitement, Lot $lot, \DateTime $date_panier, $to_array = false)
    {
        $qb = $this->getEntityManager()
            ->getRepository('AppBundle:Panier')
            ->createQueryBuilder('panier');

        switch ($etapeTraitement->getCode()) {
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

        $panier = $qb
            ->select('panier')
            ->where('panier.operateur = :operateur')
            ->andWhere('panier.etapeTraitement = :etapeTraitement')
            ->andWhere('panier.datePanier = :datePanier');


        if($etapeTraitement->getCode() !== 'BQ_DETAILS'){
            $condition = "img.lot = :lot AND img.$col_etape = 1";
        }
        else{
            $condition = "img.lot = :lot";
        }
//            $panier = $panier->innerJoin('panier.image', 'img', 'WITH', "img.lot = :lot AND img.$col_etape = 1");


        $panier = $panier->innerJoin('panier.image', 'img', 'WITH', $condition);

        $panier = $panier->setParameters(array(
                'operateur' => $operateur,
                'etapeTraitement' => $etapeTraitement,
                'datePanier' => $date_panier,
                'lot' => $lot,
            ))
            ->getQuery();

        if ($to_array) {
            return $panier->getArrayResult();
        }
        return $panier->getResult();
    }



    public function getOneLotBanque(Operateur $operateur, EtapeTraitement $etapeTraitement, Dossier $dossier, \DateTime $date_panier, $to_array = false)
    {

        $panier = $this->createQueryBuilder('panier')
            ->innerJoin('panier.image','image')
            ->innerJoin('image.lot', 'lot')
            ->where('panier.operateur = :operateur')
            ->andWhere('panier.etapeTraitement = :etapeTraitement')
            ->andWhere('panier.datePanier = :datePanier')
            ->andWhere('lot.dossier = :dossier')
            ->select('panier');


        $panier = $panier->setParameters(array(
            'operateur' => $operateur,
            'etapeTraitement' => $etapeTraitement,
            'datePanier' => $date_panier,
            'dossier' => $dossier,
        ))
            ->getQuery();

        if ($to_array) {
            return $panier->getArrayResult();
        }
        return $panier->getResult();
    }


    public function getPanierFantome($categorie, $etapeTraitementOrg, $etapeTraitementPan){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select * from panier where etape_traitement_id = :etape_traitement_id_pan and categorie_id = :categorie_id and fini = 0 and operateur_id not in (SELECT o.id FROM operateur o JOIN organisation org ON o.organisation_id=org.id 
                WHERE (o.supprimer = 0 and org.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_id_org)) )";


        $prep = $pdo->prepare($query);
        $prep->execute([
            'etape_traitement_id_org'=> $etapeTraitementOrg,
            'etape_traitement_id_pan'=> $etapeTraitementPan,
            'categorie_id' => $categorie
        ]);
        $res = $prep->fetchAll();

        $paniers = [];

        foreach ($res as $p){
            $paniers [] = $this->find($p->id);
        }

        return $paniers;
    }


    public function getPanierSaisiePerUser(Operateur $operateur)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT P.*,I.exercice AS exercice,COUNT(I.id) as nbimage,L.id AS lot_id,
                  L.date_scan AS datescan,L.lot AS lot,CAT.id AS categorie_id,CAT.libelle AS categorie,
                  etape.id AS etape_id,etape.code AS etape_code,etape.libelle AS etape_libelle,
                  OP.id AS operateur_id, D.id AS dossier_id,D.cloture AS cloture,D.nom AS dossier,
                  S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client
                  FROM panier P
                  INNER JOIN operateur OP ON(P.operateur_id=OP.id)
                  INNER JOIN image I ON(P.image_id=I.id)
                  INNER JOIN categorie CAT ON(P.categorie_id=CAT.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN etape_traitement etape ON(P.etape_traitement_id=etape.id)
                  INNER JOIN dossier D ON(L.dossier_id=D.id)
                  INNER JOIN site S ON(D.site_id=S.id)
                  INNER JOIN client C ON(S.client_id=C.id)
                  WHERE OP.id = :operateur_id AND etape.code IN('OS_1','OS_2','CTRL_OS')
                  AND (I.saisie1=1 OR I.saisie2=1 OR I.ctrl_saisie=1)
                  AND P.fini = 0
                  GROUP BY L.id, CAT.id,etape.id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'operateur_id' => $operateur->getId(),
        ));
        $res = $prep->fetchAll();

        for ($i=0; $i<count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
        }
        return $res;
    }

    public function getNbImagePanier(Operateur $operateur, EtapeTraitement $etape)
    {
        switch ($etape->getCode()) {
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

        if (!isset($col_etape)) {
            return 0;
        }

        $images = $this->getEntityManager()
            ->getRepository('AppBundle:Panier')
            ->createQueryBuilder('p')
            ->select('COUNT(p.id) AS nbimage')
            ->innerJoin('p.image', 'image')
            ->innerJoin('p.operateur', 'operateur')
            ->innerJoin('p.etapeTraitement', 'etape')
            ->where("image.$col_etape = 1")
            ->andWhere('operateur = :operateur')
            ->andWhere('etape = :etape')
            ->setParameters(array(
                'operateur' => $operateur,
                'etape' => $etape,
            ))
            ->getQuery()
            ->getResult();
        if (count($images) > 0) {
            return $images[0]['nbimage'];
        }
        return 0;
    }

    public function getPanierImputationPerUser(Operateur $operateur)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT P.*,I.exercice AS exercice,COUNT(I.id) as nbimage,L.id AS lot_id,
                  L.date_scan AS datescan,L.lot AS lot,CAT.id AS categorie_id,CAT.libelle AS categorie,
                  etape.id AS etape_id,etape.code AS etape_code,etape.libelle AS etape_libelle,
                  OP.id AS operateur_id, D.id AS dossier_id,D.cloture AS cloture,D.nom AS dossier,
                  S.id AS site_id,S.nom AS site,C.id AS client_id,C.nom AS client
                  FROM panier P
                  INNER JOIN operateur OP ON(P.operateur_id=OP.id)
                  INNER JOIN image I ON(P.image_id=I.id)
                  INNER JOIN categorie CAT ON(P.categorie_id=CAT.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN etape_traitement etape ON(P.etape_traitement_id=etape.id)
                  INNER JOIN dossier D ON(L.dossier_id=D.id)
                  INNER JOIN site S ON(D.site_id=S.id)
                  INNER JOIN client C ON(S.client_id=C.id)
                  WHERE OP.id = :operateur_id AND etape.code IN('IMP','CTRL_IMP')
                  AND (I.imputation=1 OR I.ctrl_imputation=1)
                  AND P.fini = 0
                  GROUP BY CAT.id,etape.id";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'operateur_id' => $operateur->getId(),
        ));
        $res = $prep->fetchAll();

        for ($i=0; $i<count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
        }
        return $res;
    }


    public function getPanierNdfFacturette(Operateur $operateur){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select p.*,i.exercice,l.id as lot_id,l.date_scan as datescan, count(i.id) as nbimage
                from panier p 
                inner join image i on i.id = p.image_id
                inner join separation s on i.id = s.image_id  
                inner join lot l on l.id = i.lot_id
                where s.souscategorie_id = 133 and p.operateur_id = :operateur_id
                group by  p.etape_traitement_id,l.id
                order by p.date_panier";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'operateur_id' => $operateur->getId(),
        ));
        $res = $prep->fetchAll();

        for ($i=0; $i<count($res); $i++) {
            $res[$i]->datescan = new \DateTime($res[$i]->datescan);
        }

        return $res;
    }

    public function getPanierSaisieBanqueByImage(Image $image){

        $etapeSaisie = $this->getEntityManager()
            ->getRepository('AppBundle:EtapeTraitement')
            ->find(11);

        $etapeRb2 = $this->getEntityManager()
            ->getRepository('AppBundle:EtapeTraitement')
            ->find(26);

        return $this->createQueryBuilder('p')
            ->where('p.image = :image')
            ->andWhere('p.etapeTraitement = :etapeSaisie OR p.etapeTraitement = :etapeRb2')
            ->setParameter('image', $image)
            ->setParameter('etapeSaisie', $etapeSaisie)
            ->setParameter('etapeRb2', $etapeRb2)
            ->getQuery()
            ->getResult();
    }
}