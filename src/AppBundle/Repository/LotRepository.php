<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/06/2016
 * Time: 13:48
 */

namespace AppBundle\Repository;

use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Categorie;
use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Site;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class LotRepository extends EntityRepository
{


    private $maxline = 1000;


    /**
     * Liste des lots à traiter dans Banque
     * @return array
     * @throws \Exception
     */
    public function lotB()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT L.id, L.date_scan,D.id as dossier_id,D.nom as dossier,D.cloture,S.id as site_id,S.nom as site,
                C.id as client_id,C.nom as client,count(*) as nbimage
                FROM image_a_traiter A
                INNER JOIN image I ON(A.image_id=I.id)
                INNER JOIN lot L ON(I.lot_id=L.id)
                INNER JOIN dossier D ON(L.dossier_id=D.id)
                INNER JOIN site S ON(D.site_id=S.id)
                INNER JOIN client C ON(S.client_id=C.id)
                INNER JOIN separation SP ON(SP.image_id=I.id)
                WHERE A.status = :astatus 
                AND I.download IS NOT NULL 
                AND I.supprimer = :supprimer 
                AND A.saisie1 = :s1status 
                AND L.status = :lstatus 
                AND SP.categorie_id = :categorie
                GROUP BY L.id,L.date_scan ORDER BY L.id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'astatus' => 4,
            's1status' => 0,
            'lstatus' => 4,
            'supprimer' => 0,
            'categorie' => 16,
        ));


        $lots = $prep->fetchAll();

        for ($i = 0; $i < count($lots); $i++) {
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($lots[$i]->dossier_id);
            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$i]->priorite = isset($priorite['delai']) && $priorite['delai'] ? $priorite['delai']->format('Y-m-d') : NULL;
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

    /**
     * Liste des lots à traiter dans Réception Traitement Niv. 1
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotN1($encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if(!$encours) {
            $query = "SELECT L.id, L.date_scan,D.id as dossier_id,D.nom as dossier,D.cloture,S.id as site_id,S.nom as site,
                C.id as client_id,C.nom as client,count(*) as nbimage
                FROM image_a_traiter A
                INNER JOIN image I ON(A.image_id=I.id)
                INNER JOIN lot L ON(I.lot_id=L.id)
                INNER JOIN dossier D ON(L.dossier_id=D.id)
                INNER JOIN site S ON(D.site_id=S.id)
                INNER JOIN client C ON(S.client_id=C.id)
                WHERE A.status = :status AND L.status = :lstatus AND I.supprimer = :supprimer
                GROUP BY L.id,L.date_scan ORDER BY L.id LIMIT :maxline";


//        $query = "SELECT L.id, L.date_scan,L.dossier_id,'dossier','cloture','site_id','site',
//                'client_id','client',count(*) as nbimage
//                FROM image_a_traiter A
//                INNER JOIN image I ON(A.image_id=I.id)
//                INNER JOIN lot L ON(I.lot_id=L.id)
//                WHERE A.status = :status AND L.status = :lstatus AND I.supprimer = :supprimer
//                GROUP BY L.id,L.date_scan ORDER BY L.id LIMIT :maxline";
//
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 0,
                'lstatus' => 0,
                'supprimer' => 0,
                'maxline' => $this->maxline
            ));
        }
        else {
            $query = "SELECT L.id, L.date_scan,D.id as dossier_id,D.nom as dossier,D.cloture,S.id as site_id,S.nom as site,
                C.id as client_id,C.nom as client,count(*) as nbimage, P.operateur_id, OP.login as operateur
                FROM image_a_traiter A
                INNER JOIN image I ON(A.image_id=I.id)
                INNER JOIN lot L ON(I.lot_id=L.id)
                INNER JOIN dossier D ON(L.dossier_id=D.id)
                INNER JOIN site S ON(D.site_id=S.id)
                INNER JOIN client C ON(S.client_id=C.id)
                INNER JOIN panier_reception P ON (P.lot_id = L.id) 
                INNER JOIN operateur OP on P.operateur_id = OP.id
                INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                WHERE A.status = :status AND L.status = :lstatus AND I.supprimer = :supprimer 
                AND P.status = :pstatus AND P.etape_traitement_id = :etape_traitement_id
                AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                GROUP BY L.id,L.date_scan ORDER BY L.id LIMIT :maxline";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 1,
                'lstatus' => 1,
                'supprimer' => 0,
                'opsupprimer' => 0,
                'etape_traitement_id' => 1,
                'etape_traitement_org' => 1,
                'pstatus' => 0,
                'maxline' => $this->maxline
            ));

        }

        $lots = $prep->fetchAll();


        $prioriteDossier = [];
        $dossierPris = [];
        for ($i = 0; $i < count($lots); $i++){
            if(!in_array($lots[$i]->dossier_id, $dossierPris)){
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($lots[$i]->dossier_id);
                $dossierPris[] = $dossier->getId();

                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);

                $prioriteDossier[$dossier->getId()] = $priorite;
            }
        }

        for($i = 0; $i < count($lots); $i++){

            $priorite = $prioriteDossier[$lots[$i]->dossier_id];

            $lots[$i]->priorite = isset($priorite['delai']) && $priorite['delai'] ? $priorite['delai']->format('Y-m-d') : NULL;
            $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
            $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
            $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
        }

//        for ($i = 0; $i < count($lots); $i++) {
//            $dossier = $this->getEntityManager()
//                ->getRepository('AppBundle:Dossier')
//                ->find($lots[$i]->dossier_id);
//            if ($dossier) {
//                /** @var Site $site */
////                $site = $dossier->getSite();
////                /** @var Client $client */
////                $client = $site->getClient();
////
////                $lots[$i]->dossier = $dossier->getNom();
////                $lots[$i]->site = $site->getNom();
////                $lots[$i]->site_id = $site->getId();
////                $lots[$i]->client = $client->getId();
////                $lots[$i]->cloture = $dossier->getCloture();
//
//
//                $priorite = $this->getEntityManager()
//                    ->getRepository('AppBundle:PrioriteLot')
//                    ->getPrioriteDossier($dossier);
//
//
//                $lots[$i]->priorite = isset($priorite['delai']) && $priorite['delai'] ? $priorite['delai']->format('Y-m-d') : NULL;
//                $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
//                $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
//                $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
//            } else {
//                $lots[$i]->priorite = NULL;
//                $lots[$i]->tache = '';
//                $lots[$i]->color = '#696dcb';
//                $lots[$i]->order = 9000;
//            }
//        }

        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });

        return $lots;
    }

    /**
     * Liste des lots à traiter dans Réception Traitement Niv. 2
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotN2($encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if(!$encours) {
            $query = "SELECT L.id, L.date_scan,D.id as dossier_id,D.nom as dossier,D.cloture,S.id as site_id,S.nom as site,
                C.id as client_id,C.nom as client,((select count(distinct image_id) as nb from decoupage_niveau2 where lot_id=L.id)  + (select count(nomdecoupee) as nbfille from decoupage_niveau2 where lot_id=L.id AND nomdecoupee!=''))  as nbimage
                FROM image_a_traiter A
                INNER JOIN image I ON(A.image_id=I.id)
                INNER JOIN lot L ON(I.lot_id=L.id)
                INNER JOIN dossier D ON(L.dossier_id=D.id)
                INNER JOIN site S ON(D.site_id=S.id)
                INNER JOIN client C ON(S.client_id=C.id)
                WHERE A.status = :status AND L.status = :lstatus AND I.supprimer = :supprimer
                GROUP BY L.id,L.date_scan ORDER BY L.id LIMIT :maxline";


//        $query = "SELECT L.id, L.date_scan,L.dossier_id,'dossier','cloture','site_id','site', 'client_id','client',count(*) as nbimage
//                FROM image_a_traiter A
//                INNER JOIN image I ON(A.image_id=I.id)
//                INNER JOIN lot L ON(I.lot_id=L.id)
//                WHERE A.status = :status AND L.status = :lstatus AND I.supprimer = :supprimer
//                GROUP BY L.id,L.date_scan ORDER BY L.id LIMIT :maxline";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 2,
                'lstatus' => 2,
                'supprimer' => 0,
                'maxline' => $this->maxline
            ));
        }
        else{
            $query = "SELECT L.id, L.date_scan,D.id as dossier_id,D.nom as dossier,D.cloture,S.id as site_id,S.nom as site,
                C.id as client_id,C.nom as client,((select count(distinct image_id) as nb from decoupage_niveau2 where lot_id=L.id)  + (select count(nomdecoupee) as nbfille from decoupage_niveau2 where lot_id=L.id AND nomdecoupee!=''))  as nbimage, P.operateur_id, OP.login as operateur
                FROM image_a_traiter A
                INNER JOIN image I ON(A.image_id=I.id)
                INNER JOIN lot L ON(I.lot_id=L.id)
                INNER JOIN dossier D ON(L.dossier_id=D.id)
                INNER JOIN site S ON(D.site_id=S.id)
                INNER JOIN client C ON(S.client_id=C.id)
                INNER JOIN panier_reception P ON (P.lot_id = L.id) AND P.etape_traitement_id = :etape_traitement_id
                INNER JOIN operateur OP on P.operateur_id = OP.id
                INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                WHERE A.status = :status AND L.status = :lstatus AND I.supprimer = :supprimer AND P.status = :pstatus
                AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                
                GROUP BY L.id,L.date_scan ORDER BY L.id LIMIT :maxline";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 3,
                'lstatus' => 3,
                'supprimer' => 0,
                'opsupprimer' => 0,
                'etape_traitement_id' => 3,
                'etape_traitement_org' => 3,
                'pstatus' => 0,
                'maxline' => $this->maxline
            ));

        }
        $lots = $prep->fetchAll();


        $prioriteDossier = [];
        $dossierPris = [];
        for ($i = 0; $i < count($lots); $i++){
            if(!in_array($lots[$i]->dossier_id, $dossierPris)){
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($lots[$i]->dossier_id);
                $dossierPris[] = $dossier->getId();

                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);

                $prioriteDossier[$dossier->getId()] = $priorite;
            }
        }

        for($i = 0; $i < count($lots); $i++){

            $priorite = $prioriteDossier[$lots[$i]->dossier_id];

            $lots[$i]->priorite = isset($priorite['delai']) && $priorite['delai'] ? $priorite['delai']->format('Y-m-d') : NULL;
            $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
            $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
            $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
        }







//        for ($i = 0; $i < count($lots); $i++) {
//            $dossier = $this->getEntityManager()
//                ->getRepository('AppBundle:Dossier')
//                ->find($lots[$i]->dossier_id);
//            if ($dossier) {
//
//
////                /** @var Site $site */
////                $site = $dossier->getSite();
////                /** @var Client $client */
////                $client = $site->getClient();
////
////                $lots[$i]->dossier = $dossier->getNom();
////                $lots[$i]->site = $site->getNom();
////                $lots[$i]->site_id = $site->getId();
////                $lots[$i]->client = $client->getId();
////                $lots[$i]->cloture = $dossier->getCloture();
//
//
//                $priorite = $this->getEntityManager()
//                    ->getRepository('AppBundle:PrioriteLot')
//                    ->getPrioriteDossier($dossier);
//                $lots[$i]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
//                $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
//                $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
//                $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
//            } else {
//                $lots[$i]->priorite = NULL;
//                $lots[$i]->tache = '';
//                $lots[$i]->color = '#696dcb';
//                $lots[$i]->order = 9000;
//            }
//        }

        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });

        return $lots;
    }


    /**
     * Lot à traiter dans Saisie 2
     *
     * @param $userId
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotSaisie2($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        //Récupération client attaché à opérateur
        $query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userId))";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId
        ));
        $resDossierId = $prep->fetchAll();
        $listIdDossier = array();
        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        /* LISTE CLIENTS DANS AFFECTATION TENUE */
        $query = "SELECT distinct l.dossier_id AS id FROM lot l JOIN affectation_panier_tenue a ON l.id=a.lot_id WHERE a.operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId) AND a.code=:code AND a.fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'code'=>'OS_2',
            'fini'=> 0,
        ));

        $resDossierId = $prep->fetchAll();

        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        if(!$encours) {

            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 0,
                'isaisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
            ));
        }
        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'decouper' => 0,
                'saisie1' => 1,
                'categorieId' => 16,
                'supprimer' => 0,
                'isaisie1' => 1,
                'statusLot' => 4,
                'fini' => 0,
                'opsupprimer' => 0,
                'etape_traitement_id'=> 12,
                'etape_traitement_org' => 12
            ));
        }


//        $prep = $pdo->prepare($query);
//        $prep->execute(array(
//            'status' => 4,
//            'saisie2' => 0,
//            'decouper' => 0,
//            'supprimer' => 0,
//        ));
        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {

            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {

                    if ($dossier) {
                        $site = $dossier->getSite();
                        $client = $site->getClient();

                        $res[$i]->dossier = $dossier->getNom();

                        $res[$i]->cloture = $dossier->getCloture();
                        $res[$i]->categorie = $categorie->getLibelleNew();

                        $res[$i]->site = $site->getNom();
                        $res[$i]->site_id = $site->getId();
                        $res[$i]->client = $client->getNom();
                        $res[$i]->site_id = $client->getId();


                    }


                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                        $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }


                    if ($dossier) {

                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);

                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }
            else
            {
                if ($dossier) {
                    $site = $dossier->getSite();
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();

                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->categorie = $categorie->getLibelleNew();

                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->site_id = $client->getId();


                }


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }


                if ($dossier) {

                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);

                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }

        /* LISTE CLIENTS DANS AFFECTATION TENUE */
        $query = "SELECT distinct l.dossier_id AS id FROM lot l JOIN affectation_panier_tenue a ON l.id=a.lot_id WHERE a.operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId) AND a.code=:code AND a.fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'code'=>'OS_2',
            'fini'=> 0,
        ));

        $resDossierId = $prep->fetchAll();

        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        if(!$encours) {

            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId0))
                  AND SEP.categorie_id IN (SELECT categorie_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId1))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 0,
                'isaisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
                'userId0'=>$userId,
                'userId1'=>$userId,
            ));
        }
        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId0))
                  AND SEP.categorie_id IN (SELECT categorie_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId1))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'decouper' => 0,
                'saisie1' => 1,
                'categorieId' => 16,
                'supprimer' => 0,
                'isaisie1' => 1,
                'statusLot' => 4,
                'fini' => 0,
                'opsupprimer' => 0,
                'etape_traitement_id'=> 12,
                'etape_traitement_org' => 12,
                'userId0'=>$userId,
                'userId1'=>$userId,
            ));
        }


        $res = $prep->fetchAll();


        for ($i = 0; $i < count($res); $i++) {

            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {

                    if ($dossier) {
                        $site = $dossier->getSite();
                        $client = $site->getClient();

                        $res[$i]->dossier = $dossier->getNom();

                        $res[$i]->cloture = $dossier->getCloture();
                        $res[$i]->categorie = $categorie->getLibelleNew();

                        $res[$i]->site = $site->getNom();
                        $res[$i]->site_id = $site->getId();
                        $res[$i]->client = $client->getNom();
                        $res[$i]->site_id = $client->getId();


                    }


                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                        $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }


                    if ($dossier) {

                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);

                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }
            else
            {
                if ($dossier) {
                    $site = $dossier->getSite();
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();

                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->categorie = $categorie->getLibelleNew();

                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->site_id = $client->getId();


                }


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }


                if ($dossier) {

                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);

                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }
        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    public function lotGroupe($codeEtape)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        $query = "select lot_id, (IF (categorie_id is null, 0,categorie_id)) as categid, usergroup_id from lot_user_group where code = :codeEtape";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'codeEtape' => 'OS_1',
        ));

        $lotcategorie = $prep->fetchAll();

        //$listLotCategorie = array();
        $listUserGroup = array();
        for ($i = 0; $i < count($lotcategorie); $i++) {
            if (array_key_exists($lotcategorie[$i]->usergroup_id, $listUserGroup)) {
                $tab = $listUserGroup[$lotcategorie[$i]->usergroup_id];
                if (array_key_exists($lotcategorie[$i]->lot_id, $tab)) {
                    $tabSous = $tab[$lotcategorie[$i]->lot_id];
                    array_push($tabSous, $lotcategorie[$i]->categid);
                    $tab[$lotcategorie[$i]->lot_id] = $tabSous;
                } else {
                    $tabSous = [];
                    array_push($tabSous, $lotcategorie[$i]->categid);
                    $tab[$lotcategorie[$i]->lot_id] = $tabSous;
                }
                $listUserGroup[$lotcategorie[$i]->usergroup_id] = $tab;
            }
            else
            {
                $tab = [];
                $tabSous = [];
                array_push($tabSous, $lotcategorie[$i]->categid);
                $tab[$lotcategorie[$i]->lot_id]= $tabSous;
                $listUserGroup[$lotcategorie[$i]->usergroup_id] = $tab;
            }

        }
        //echo var_dump($listUserGroup);
        //SELECT COUNT(A.image_id) AS nbimage
        $query = "SELECT (select count(i.nom) from image i join lot l on i.lot_id=l.id join separation s on s.image_id=i.id 
                    WHERE l.id=L.id AND s.categorie_id=SEP.categorie_id AND i.supprimer=0 ) AS nbimage,
                    L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client',lug.usergroup_id, lug.date_panier 
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN lot_user_group lug ON L.id=lug.lot_id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id IN (select lot_id FROM lot_user_group WHERE code=:codeEtape)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  GROUP BY dossier_id,lot_id,categorie_id,lug.usergroup_id ORDER BY lug.usergroup_id,lug.lot_id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'decouper' => 0,
            'supprimer' => 0,
            'statusLot' => 4,
            'categorieId'=>16,
            'codeEtape' => $codeEtape,
        ));
        $res = $prep->fetchAll();
        $lots = [];
        for ($i = 0; $i < count($res); $i++) {
            $trouvee = false;
            if (array_key_exists($res[$i]->usergroup_id, $listUserGroup)) {
                $listLotCategorie = [];
                $listLotCategorie = $listUserGroup[$res[$i]->usergroup_id];
                if (array_key_exists($res[$i]->lot_id, $listLotCategorie)) {
                    $tab = [];
                    $tab = $listLotCategorie[$res[$i]->lot_id];
                    if (count($tab) == 1)
                    {
                        if ($tab[0] == 0)
                        {
                            $trouvee = true;
                        }
                        else
                        {
                            for ($j = 0; $j < count($tab); $j++) {
                                if ($tab[$j] == $res[$i]->categorie_id) {
                                    $trouvee = true;
                                    break;
                                }
                            }
                        }
                    }
                    else {
                        for ($j = 0; $j < count($tab); $j++) {
                            if ($tab[$j] == $res[$i]->categorie_id) {
                                $trouvee = true;
                                break;
                            }
                        }
                    }
                }
            }
            if ($trouvee == true) {
                /** @var Dossier $dossier */
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);


                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();

                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }
        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Lot à traiter dans Saisie 1
     * @param $userId
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotSaisie1($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        //Récupération client attaché à opérateur

        /*$query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userId))";*/
        $query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_id in 
                    (select operateur_id from rattachement rat WHERE rat.operateur_rat_id in
                    (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId))))";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId
        ));

        $resDossierId = $prep->fetchAll();
        $listIdDossier = array();
        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM lot_user_group)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 0,
                'isaisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,

            ));
        }

        else {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM lot_user_group)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 1,
                'isaisie1' => 1,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
                'fini' => 0,
                'etape_traitement_id' => 11,
                'etape_traitement_org' => 11,
                'opsupprimer' => 0,

            ));

        }

        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {
                    /** @var Site $site */
                    $site = $dossier->getSite();

                    /** @var Client $client */
                    $client = $site->getClient();

                    /** @var  $categorie */
                    $categorie = $this->getEntityManager()
                        ->getRepository('AppBundle:Categorie')
                        ->find($res[$i]->categorie_id);

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();

                    $res[$i]->categorie = $categorie->getLibelleNew();


                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                        $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }

                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }
            else
            {
                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();

                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }


        /* LISTE CLIENTS DANS AFFECTATION TENUE */
        $query = "SELECT distinct l.dossier_id AS id FROM lot l JOIN affectation_panier_tenue a ON l.id=a.lot_id WHERE a.operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId) AND a.code=:code AND a.fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'code'=>'OS_1',
            'fini'=> 0,
        ));

        $resDossierId = $prep->fetchAll();
        $listIdDossier = array();
        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM lot_user_group)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId0))
                  AND SEP.categorie_id IN (SELECT categorie_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId1))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 0,
                'isaisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
                'userId0'=>$userId,
                'userId1'=>$userId,
            ));
        }

        else {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM lot_user_group)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId0))
                  AND SEP.categorie_id IN (SELECT categorie_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId1))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 1,
                'isaisie1' => 1,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
                'fini' => 0,
                'etape_traitement_id' => 11,
                'etape_traitement_org' => 11,
                'opsupprimer' => 0,
                'userId0'=>$userId,
                'userId1'=>$userId,
            ));

        }

        $res = $prep->fetchAll();


        for ($i = 0; $i < count($res); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {
                    /** @var Site $site */
                    $site = $dossier->getSite();

                    /** @var Client $client */
                    $client = $site->getClient();

                    /** @var  $categorie */
                    $categorie = $this->getEntityManager()
                        ->getRepository('AppBundle:Categorie')
                        ->find($res[$i]->categorie_id);

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();

                    $res[$i]->categorie = $categorie->getLibelleNew();


                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                        $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }

                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }
            else
            {
                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();

                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }

        //=====================================Charger les lots dans lot_user_group==========================================//

        /*Charger liste lot et catégorie dans lot_user_group*/
        $query = "select lot_id, (IF (categorie_id is null, 0,categorie_id)) as categid from lot_user_group where usergroup_id in
                  (select operateur_rat_id from rattachement where operateur_id in 
                  (select operateur_id from rattachement where operateur_rat_id = :userId and code = :codeEtape) and operateur_rat_id != :userId1)";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'userId1'=>$userId,
            'codeEtape' => 'OS_1',
        ));

        $lotcategorie = $prep->fetchAll();

        $listLotCategorie = array();
        for ($i = 0; $i < count($lotcategorie); $i++) {

            if (isset($listLotCategorie[$lotcategorie[$i]->lot_id]))
            {
                $tab = $listLotCategorie[$lotcategorie[$i]->lot_id];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
            else
            {
                $tab = [];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
        }

        /*________________________________________________*/

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND 
                  L.id IN (select lot_id from lot_user_group where usergroup_id in
                  (select operateur_rat_id from rattachement where operateur_id in 
                  (select operateur_id from rattachement where operateur_rat_id=:userId AND code=:codeEtape) and operateur_rat_id!=:userId1))
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 0,
            'isaisie1' => 0,
            'decouper' => 0,
            'supprimer' => 0,
            'statusLot' => 4,
            'categorieId'=>16,
            'userId' => $userId,
            'userId1' => $userId,
            'codeEtape' => 'OS_1',
        ));
        $res = $prep->fetchAll();
        for ($i = 0; $i < count($res); $i++) {
            $trouvee = false;

            //Recherche catégorie par lot
            foreach ($listLotCategorie as $key => $valueLotCateg)
            {
                if ($key == $res[$i]->lot_id)
                {
                    $tab = $listLotCategorie[$key];
                    for ($j = 0; $j < count($tab); $j++)
                    {
                        if ($tab[$j] == $res[$i]->categorie_id)
                        {
                            $trouvee = true;
                            break;
                        }
                    }
                    break;
                }
            }
            /*if (array_key_exists($res[$i]->lot_id, $listLotCategorie))
            {
                $tab = $listLotCategorie[$res[$i]->lot_id];
                if (count($tab) == 1 and $tab[0] = 0)
                {
                    $trouvee = true;
                }
                else
                {
                    for ($j = 0; $j < count($tab); $j++)
                    {
                        if ($tab[$j] == $res[$i]->categorie_id) {
                            $trouvee = true;

                        }
                    }
                }
            }*/


            if ($trouvee == false) {

                /** @var Dossier $dossier */
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);

                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();

                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }



        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;

    }


    /**
     * Lot à traiter dans Saisie 2
     *
     * @param $userId
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotTenueSaisie2($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
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
            $prep->execute(array(
                'userId' => $userId,
                'userRat' => $userId,
                'fini' => 0,
                'status' => 4,
                'saisie1' => 0,
                'isaisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
            ));
        }

        else {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie2=:isaisie1 AND L.status=:statusLot
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id 
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userId' => $userId,
                'userRat' => $userId,
                'status' => 4,
                'saisie1' => 1,
                'isaisie1' => 1,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
                'fini' => 0,
                'etape_traitement_id' => 12,
                'etape_traitement_org' => 12,
                'opsupprimer' => 0
            ));

        }

        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            /** @var Site $site */
            $site = $dossier->getSite();

            /** @var Client $client */
            $client = $site->getClient();

            /** @var  $categorie */
            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            $res[$i]->dossier = $dossier->getNom();
            $res[$i]->cloture = $dossier->getCloture();
            $res[$i]->site = $site->getNom();
            $res[$i]->site_id = $site->getId();
            $res[$i]->client = $client->getNom();
            $res[$i]->client_id = $client->getId();

            $res[$i]->categorie = $categorie->getLibelleNew();


            if (!isset($lots[$res[$i]->lot_id])) {
                $lots[$res[$i]->lot_id] = $res[$i];
                $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
            } else {
                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
            }

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$res[$i]->lot_id]->priorite = NULL;
                $lots[$res[$i]->lot_id]->tache = '';
                $lots[$res[$i]->lot_id]->color = '#696dcb';
                $lots[$res[$i]->lot_id]->order = 9000;
            }

        }

        //=====================================Charger les lots dans lot_user_group==========================================//

        /*Charger liste lot et catégorie dans lot_user_group*/
        $query = "select lot_id, categorie_id as categid from affectation_panier_tenue where code=:codeEtape AND operateur_id =:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'fini' => 0,
            'codeEtape'=> 'OS_2',
        ));

        $lotcategorie = $prep->fetchAll();

        $listLotCategorie = array();
        for ($i = 0; $i < count($lotcategorie); $i++) {

            if (isset($listLotCategorie[$lotcategorie[$i]->lot_id]))
            {
                $tab = $listLotCategorie[$lotcategorie[$i]->lot_id];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
            else
            {
                $tab = [];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
        }

        /*________________________________________________*/

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie2 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND
                  SEP.categorie_id IN (select categorie_id from affectation_panier_tenue where operateur_id=:userId AND fini=:fini AND code=:codeEtape)
                  AND 
                  L.id IN (select lot_id from affectation_panier_tenue where operateur_id=:userId1 AND fini=:fini1 AND code=:codeEtape1)
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 0,
            'decouper' => 0,
            'categorieId'=>16,
            'userId' => $userId,
            'userId1' => $userId,
            'fini'=>0,
            'fini1'=>0,
            'codeEtape' => 'OS_2',
            'codeEtape1' => 'OS_2',
        ));
        $res = $prep->fetchAll();
        for ($i = 0; $i < count($res); $i++) {
            $trouvee = false;

            //Recherche catégorie par lot
            foreach ($listLotCategorie as $key => $valueLotCateg)
            {
                if ($key == $res[$i]->lot_id)
                {
                    $tab = $listLotCategorie[$key];
                    for ($j = 0; $j < count($tab); $j++)
                    {
                        if ($tab[$j] == $res[$i]->categorie_id)
                        {
                            $trouvee = true;
                            break;
                        }
                    }
                    break;
                }
            }

            if ($trouvee == false) {

                /** @var Dossier $dossier */
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);

                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();

                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }

        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Lot à traiter dans Saisie 1
     * @param $userId
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotTenueSaisie1($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id!=:userId AND fini=:fini)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userId' => $userId,
                'userRat' => $userId,
                'fini' => 0,
                'status' => 4,
                'saisie1' => 0,
                'isaisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
            ));
        }

        else {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id!=:userId AND fini=:fini)
                  AND SEP.categorie_id IS NOT NULL AND I.supprimer = :supprimer AND I.saisie1=:isaisie1 AND L.status=:statusLot
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id 
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userId' => $userId,
                'userRat' => $userId,
                'status' => 4,
                'saisie1' => 1,
                'isaisie1' => 1,
                'decouper' => 0,
                'supprimer' => 0,
                'statusLot' => 4,
                'categorieId' => 16,
                'fini' => 0,
                'etape_traitement_id' => 11,
                'etape_traitement_org' => 11,
                'opsupprimer' => 0
            ));

        }

        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            /** @var Site $site */
            $site = $dossier->getSite();

            /** @var Client $client */
            $client = $site->getClient();

            /** @var  $categorie */
            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            $res[$i]->dossier = $dossier->getNom();
            $res[$i]->cloture = $dossier->getCloture();
            $res[$i]->site = $site->getNom();
            $res[$i]->site_id = $site->getId();
            $res[$i]->client = $client->getNom();
            $res[$i]->client_id = $client->getId();
            $res[$i]->categorie_id = $res[$i]->categorie_id;
            $res[$i]->categorie = $categorie->getLibelleNew();


            if (!isset($lots[$res[$i]->lot_id])) {
                $lots[$res[$i]->lot_id] = $res[$i];
                $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
            } else {
                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
            }

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$res[$i]->lot_id]->priorite = NULL;
                $lots[$res[$i]->lot_id]->tache = '';
                $lots[$res[$i]->lot_id]->color = '#696dcb';
                $lots[$res[$i]->lot_id]->order = 9000;
            }

        }

        //=====================================Charger les lots dans lot_user_group==========================================//

        /*Charger liste lot et catégorie dans lot_user_group*/

        $query = "select lot_id, categorie_id as categid from affectation_panier_tenue where code=:codeEtape AND operateur_id =:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'fini' => 0,
            'codeEtape' => 'OS_1',
        ));

        $lotcategorie = $prep->fetchAll();

        $listLotCategorie = array();
        for ($i = 0; $i < count($lotcategorie); $i++) {

            if (isset($listLotCategorie[$lotcategorie[$i]->lot_id]))
            {
                $tab = $listLotCategorie[$lotcategorie[$i]->lot_id];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
            else
            {
                $tab = [];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
        }

        /*________________________________________________*/

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND
                  SEP.categorie_id IN (select categorie_id from affectation_panier_tenue where operateur_id=:userId AND fini=:fini AND code=:codeEtape)
                  AND
                  L.id IN (select lot_id from affectation_panier_tenue where operateur_id=:userId1 AND fini=:fini1 AND code=:codeEtape1)
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 0,
            'decouper' => 0,
            'categorieId'=>16,
            'userId' => $userId,
            'userId1' => $userId,
            'fini' => 0,
            'fini1' => 0,
            'codeEtape'=> 'OS_1',
            'codeEtape1'=> 'OS_1',
        ));
        $res = $prep->fetchAll();
        for ($i = 0; $i < count($res); $i++) {
            $trouvee = false;

            //Recherche catégorie par lot
            foreach ($listLotCategorie as $key => $valueLotCateg)
            {
                if ($key == $res[$i]->lot_id)
                {
                    $tab = $listLotCategorie[$key];
                    for ($j = 0; $j < count($tab); $j++)
                    {
                        if ($tab[$j] == $res[$i]->categorie_id)
                        {
                            $trouvee = true;
                            break;
                        }
                    }
                    break;
                }
            }

            if ($trouvee == false) {

                /** @var Dossier $dossier */
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);

                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();
                $res[$i]->categorie_id = $res[$i]->categorie_id;
                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }

        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;

    }

    public function lotSaisie2Banque($exercice, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

//        $query = "SELECT COUNT(I.id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
//                  SEP.souscategorie_id as souscategorie_id, 'categorie','souscategorie',
//                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client',
//                  SC.banque_compte_id, 'banque', 'num_compte'
//                  FROM image I
//                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
//                  INNER JOIN lot L ON(I.lot_id=L.id)
//                  INNER JOIN saisie_controle SC on I.id = SC.image_id AND SC.banque_compte_id IS NOT NULL
//                  WHERE I.ctrl_saisie >= :ctrl_saisie AND I.decouper = :decouper
//                  AND SEP.categorie_id = :categorie AND SEP.souscategorie_id = :souscategorie_id
//                  AND I.id NOT IN (SELECT DISTINCT image_id FROM releve) AND I.supprimer = :supprimer
//                  AND I.id NOT IN (SELECT DISTINCT  image_id FROM panier WHERE etape_traitement_id = :etape_traitement_id)
//                  AND I.exercice = :exercice
//                  GROUP BY L.id,SC.banque_compte_id,I.exercice LIMIT 1000";


        if(!$encours) {
            $query = "SELECT COUNT(I.id) AS nbimage, SEP.categorie_id AS categorie_id,
                  SEP.souscategorie_id as souscategorie_id, 'categorie','souscategorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client',
                  SC.banque_compte_id, 'banque', 'num_compte'
                  FROM image I 
                  INNER JOIN separation SEP ON(SEP.image_id=I.id) 
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN saisie_controle SC on I.id = SC.image_id AND SC.banque_compte_id IS NOT NULL 
                  WHERE I.ctrl_saisie >= :ctrl_saisie AND I.decouper = :decouper
                  AND SEP.categorie_id = :categorie AND SEP.souscategorie_id = :souscategorie_id 
                  AND I.id NOT IN (SELECT DISTINCT image_id FROM releve) AND I.supprimer = :supprimer                  
                  AND I.id NOT IN (SELECT DISTINCT  image_id FROM panier WHERE etape_traitement_id = :etape_traitement_id)
                  AND I.exercice >= :exercice
                  GROUP BY SC.banque_compte_id,I.exercice LIMIT 10000";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'ctrl_saisie' => 3,
                'souscategorie_id' => 10,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie' => 16,
                'etape_traitement_id' => 26,
                'exercice' => $exercice
            ));
        }
        else{
            $query = "SELECT COUNT(I.id) AS nbimage, SEP.categorie_id AS categorie_id,
                  SEP.souscategorie_id as souscategorie_id, 'categorie','souscategorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client',
                  SC.banque_compte_id, 'banque', 'num_compte', O.login as operateur, O.id as operateur_id 
                  FROM image I 
                  INNER JOIN separation SEP ON(SEP.image_id=I.id) 
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN saisie_controle SC on I.id = SC.image_id AND SC.banque_compte_id IS NOT NULL 
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur O on P.operateur_id = O.id
                  WHERE I.ctrl_saisie >= :ctrl_saisie AND I.decouper = :decouper
                  AND SEP.categorie_id = :categorie AND SEP.souscategorie_id = :souscategorie_id 
                  AND I.exercice >= :exercice AND I.supprimer = :supprimer AND I.valider <> :valider
                  AND P.etape_traitement_id = :etape_traitement_id AND P.fini = :fini
                  GROUP BY SC.banque_compte_id,I.exercice LIMIT 10000";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'ctrl_saisie' => 3,
                'souscategorie_id' => 10,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie' => 16,
                'etape_traitement_id' => 26,
                'exercice' => $exercice,
                'fini' => 0,
                'valider' => 100
            ));

        }
        $res = $prep->fetchAll();

        $lots = [];
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


//            if (!isset($lots[$res[$i]->lot_id])) {
//                $lots[$res[$i]->lot_id] = $res[$i];
//                $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
//            } else {
//                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
//                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
//            }


            if (!isset($lots[$res[$i]->banque_compte_id])) {
                $lots[$res[$i]->banque_compte_id] = $res[$i];
                $lots[$res[$i]->banque_compte_id]->nbimage = $res[$i]->nbimage;
            } else {
                $lots[$res[$i]->banque_compte_id]->nbimage += $res[$i]->nbimage;
                $lots[$res[$i]->banque_compte_id]->categorie .= "/" . $res[$i]->categorie;
            }


            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
//                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
//                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
//                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
//                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;


                $lots[$res[$i]->banque_compte_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->banque_compte_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->banque_compte_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->banque_compte_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;

            } else {
//                $lots[$res[$i]->lot_id]->priorite = NULL;
//                $lots[$res[$i]->lot_id]->tache = '';
//                $lots[$res[$i]->lot_id]->color = '#696dcb';
//                $lots[$res[$i]->lot_id]->order = 9000;

                $lots[$res[$i]->banque_compte_id]->priorite = NULL;
                $lots[$res[$i]->banque_compte_id]->tache = '';
                $lots[$res[$i]->banque_compte_id]->color = '#696dcb';
                $lots[$res[$i]->banque_compte_id]->order = 9000;
            }
        }
        $lots = array_values($lots);
        usort($lots, function ($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    public function lotSaisie1Banque($releve = true, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $obIds =[5,6,7,8, 937, 939, 941];


        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,SEP.categorie_id AS categorie_id,
                  SEP.souscategorie_id as souscategorie_id, I.exercice, 'categorie','souscategorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
				  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id = :categorie ";

            if ($releve) {
                $query .= " AND SEP.souscategorie_id = 10";
            } else {
//            $query .= " AND SEP.souscategorie_id <> 10";
                $query .= " AND (SEP.souscategorie_id IN (" . implode(',', $obIds) . ") 
                OR (SEP.souscategorie_id = 153 AND SEP.soussouscategorie_id = 1905))";
            }

//        $query .= " AND I.supprimer = :supprimer
//                  GROUP BY dossier_id,lot_id,categorie_id";

            $query .= " AND I.supprimer = :supprimer
                  GROUP BY dossier_id, exercice";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 0,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie' => 16
            ));
            $res = $prep->fetchAll();
        }
        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,SEP.categorie_id AS categorie_id,
                  SEP.souscategorie_id as souscategorie_id, I.exercice, 'categorie','souscategorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client', 
                  O.id AS operateur_id,O.login AS 'operateur'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN panier P ON (P.image_id = I.id) AND P.etape_traitement_id = :etape_traitement_id AND P.fini = :fini
				  INNER JOIN operateur O on P.operateur_id = O.id
				  WHERE A.decouper = :decouper
                  AND SEP.categorie_id = :categorie ";

            if ($releve) {
                $query .= " AND SEP.souscategorie_id = 10";
            } else {
                $query .= " AND (SEP.souscategorie_id IN (" . implode(',', $obIds) . ")  
                OR (SEP.souscategorie_id = 153 AND SEP.soussouscategorie_id = 1905))";
            }

            $query .= " AND I.supprimer = :supprimer
                  GROUP BY dossier_id, exercice";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'decouper' => 0,
                'supprimer' => 0,
                'categorie' => 16,
                'etape_traitement_id' => 11,
                'fini' => 0
            ));
            $res = $prep->fetchAll();
        }

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {

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

            $res[$i]->souscategorie  = $this->getEntityManager()
                ->getRepository('AppBundle:Souscategorie')
                ->find($res[$i]->souscategorie_id)
                ->getLibelleNew();

            $res[$i]->site_id = $site->getId();
            $res[$i]->site = $site->getNom();

            $res[$i]->client_id = $site->getClient()->getId();
            $res[$i]->client = $site->getClient()->getNom();


//            if (!isset($lots[$res[$i]->lot_id])) {
//                $lots[$res[$i]->lot_id] = $res[$i];
//                $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
//            } else {
//                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
//                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
//            }



            if(!isset($lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice])){
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice] = $res[$i];
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->nbimage = $res[$i]->nbimage;
            }
            else{
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->nbimage += $res[$i]->nbimage;
            }


            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
//                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
//                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
//                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
//                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;

                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->order = isset($priorite['order']) ? $priorite['order'] : 9000;

            } else {
//                $lots[$res[$i]->lot_id]->priorite = NULL;
//                $lots[$res[$i]->lot_id]->tache = '';
//                $lots[$res[$i]->lot_id]->color = '#696dcb';
//                $lots[$res[$i]->lot_id]->order = 9000;

                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->priorite = NULL;
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->tache = '';
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->color = '#696dcb';
                $lots[$res[$i]->dossier_id.'-'.$res[$i]->exercice]->order = 9000;
            }
        }
        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;

    }


    /**
     * Lot à traiter dans Contrôle Saisie
     *
     * @param $userId
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotCtrlSaisie($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        /*$query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userId))";*/
        $query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_id in 
                    (select operateur_id from rattachement rat WHERE rat.operateur_rat_id in
                    (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId))))";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId
        ));
        $resDossierId = $prep->fetchAll();
        $listIdDossier = array();
        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }


        if(!$encours) {

            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id', 'site', 'client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)                
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND SEP.categorie_id != :categorieId
                  AND A.decouper = :decouper AND I.supprimer = :supprimer
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 2,
                'saisie2' => 2,
                'decouper' => 0,
                'supprimer' => 0,
                'categorieId' => 16,
            ));
        }
        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id', 'site', 'client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN panier P ON (P.image_id = I.id)
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id                
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND SEP.categorie_id != :categorieId
                  AND A.decouper = :decouper AND I.supprimer = :supprimer
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 5,
                'saisie1' => 3,
                'saisie2' => 3,
                'decouper' => 0,
                'supprimer' => 0,
                'categorieId' => 16,
                'etape_traitement_id' => 13,
                'fini' => 0,
                'etape_traitement_org' => 13,
                'opsupprimer' => 0
            ));
        }

        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {

            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {
                    /** @var Site $site */
                    $site = $dossier->getSite();
                    /** @var Client $client */
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();
                    $res[$i]->categorie = $categorie->getId();

                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }


                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }

        }

        /* LISTE CLIENTS DANS AFFECTATION TENUE */
        $query = "SELECT distinct l.dossier_id AS id FROM lot l JOIN affectation_panier_tenue a ON l.id=a.lot_id WHERE a.operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId) AND a.code=:code AND a.fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'code'=>'CTRL_OS',
            'fini'=> 0,
        ));

        $resDossierId = $prep->fetchAll();

        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }
        if(!$encours) {

            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id', 'site', 'client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)                
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND SEP.categorie_id != :categorieId
                  AND A.decouper = :decouper AND I.supprimer = :supprimer
                  AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId0))
                  AND SEP.categorie_id IN (SELECT categorie_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId1))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 4,
                'saisie1' => 2,
                'saisie2' => 2,
                'decouper' => 0,
                'supprimer' => 0,
                'categorieId' => 16,
                'userId0'=>$userId,
                'userId1'=>$userId,
            ));
        }
        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id', 'site', 'client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN panier P ON (P.image_id = I.id)
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id                
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND SEP.categorie_id != :categorieId
                  AND A.decouper = :decouper AND I.supprimer = :supprimer
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND L.id IN (SELECT lot_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId0))
                  AND SEP.categorie_id IN (SELECT categorie_id FROM affectation_panier_tenue WHERE operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId1))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 5,
                'saisie1' => 3,
                'saisie2' => 3,
                'decouper' => 0,
                'supprimer' => 0,
                'categorieId' => 16,
                'etape_traitement_id' => 13,
                'fini' => 0,
                'etape_traitement_org' => 13,
                'opsupprimer' => 0,
                'userId0'=>$userId,
                'userId1'=>$userId,
            ));
        }

        $res = $prep->fetchAll();


        for ($i = 0; $i < count($res); $i++) {

            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {
                    /** @var Site $site */
                    $site = $dossier->getSite();
                    /** @var Client $client */
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();
                    $res[$i]->categorie = $categorie->getId();

                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }


                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }

        }
        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    /**
     * Lot à traiter dans Contrôle Saisie
     *
     * @param $userId
     * @param bool $encours
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function lotTenueCtrlSaisie($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'CTRL_OS';

        

        if(!$encours) {

            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id', 'site', 'client_id', 'client'
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
        }
        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id', 'site', 'client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN panier P ON (P.image_id = I.id)
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id   
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id             
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.saisie2 = :saisie2 
                  AND SEP.categorie_id != :categorieId
                  AND A.decouper = :decouper AND I.supprimer = :supprimer
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id
                  AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini AND code =:codeEtape)
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND c.id in (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userId'=>$userId,
                'status' => 5,
                'saisie1' => 3,
                'saisie2' => 3,
                'decouper' => 0,
                'supprimer' => 0,
                'categorieId' => 16,
                'etape_traitement_id' => 13,
                'fini' => 0,
                'etape_traitement_org' => 13,
                'codeEtape'=> $code_etape,
                'opsupprimer' => 0,
            ));
        }

        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {

            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            
                    /** @var Site $site */
                    $site = $dossier->getSite();
                    /** @var Client $client */
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getId();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();
                    $res[$i]->categorie = $categorie->getId();

                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }


                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
            
        }

        //=====================================Charger les lots dans lot_user_group==========================================//

        /*Charger liste lot et catégorie dans lot_user_group*/
        $query = "select lot_id, categorie_id as categid from affectation_panier_tenue where code=:codeEtape AND operateur_id =:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'fini' => 0,
            'codeEtape' => $code_etape,
        ));

        $lotcategorie = $prep->fetchAll();

        $listLotCategorie = array();
        for ($i = 0; $i < count($lotcategorie); $i++) {

            if (isset($listLotCategorie[$lotcategorie[$i]->lot_id]))
            {
                $tab = $listLotCategorie[$lotcategorie[$i]->lot_id];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
            else
            {
                $tab = [];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
        }

        /*________________________________________________*/

        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.saisie1 = :saisie1 AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND
                  SEP.categorie_id IN (select categorie_id from affectation_panier_tenue where operateur_id=:userId AND fini=:fini AND code=:codeEtape)
                  AND  
                  L.id IN (select lot_id from affectation_panier_tenue where operateur_id=:userId1 AND fini=:fini1 AND code=:codeEtape1)
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 4,
            'saisie1' => 0,
            'decouper' => 0,
            'categorieId'=>16,
            'userId' => $userId,
            'userId1' => $userId,
            'fini' => 0,
            'fini1' => 0,
            'codeEtape'=> $code_etape,
            'codeEtape1'=> $code_etape,
        ));
        $res = $prep->fetchAll();
        for ($i = 0; $i < count($res); $i++) {
            $trouvee = false;

            //Recherche catégorie par lot
            foreach ($listLotCategorie as $key => $valueLotCateg)
            {
                if ($key == $res[$i]->lot_id)
                {
                    $tab = $listLotCategorie[$key];
                    for ($j = 0; $j < count($tab); $j++)
                    {
                        if ($tab[$j] == $res[$i]->categorie_id)
                        {
                            $trouvee = true;
                            break;
                        }
                    }
                    break;
                }
            }

            if ($trouvee == false) {

                /** @var Dossier $dossier */
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($res[$i]->dossier_id);

                /** @var Site $site */
                $site = $dossier->getSite();

                /** @var Client $client */
                $client = $site->getClient();

                /** @var  $categorie */
                $categorie = $this->getEntityManager()
                    ->getRepository('AppBundle:Categorie')
                    ->find($res[$i]->categorie_id);

                $res[$i]->dossier = $dossier->getNom();
                $res[$i]->cloture = $dossier->getCloture();
                $res[$i]->site = $site->getNom();
                $res[$i]->site_id = $site->getId();
                $res[$i]->client = $client->getNom();
                $res[$i]->client_id = $client->getId();

                $res[$i]->categorie = $categorie->getLibelleNew();


                if (!isset($lots[$res[$i]->lot_id])) {
                    $lots[$res[$i]->lot_id] = $res[$i];
                    $lots[$res[$i]->lot_id]->nbimage = $res[$i]->nbimage;
                } else {
                    $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                    $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                }

                if ($dossier) {
                    $priorite = $this->getEntityManager()
                        ->getRepository('AppBundle:PrioriteLot')
                        ->getPrioriteDossier($dossier);
                    $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                    $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                    $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                    $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                } else {
                    $lots[$res[$i]->lot_id]->priorite = NULL;
                    $lots[$res[$i]->lot_id]->tache = '';
                    $lots[$res[$i]->lot_id]->color = '#696dcb';
                    $lots[$res[$i]->lot_id]->order = 9000;
                }
            }
        }

        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Lot télécharger
     */

    public function lotTelecharger(\DateTime $dateDown){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT i.download,(SELECT nom FROM client WHERE id=s.client_id) as nomClient,
                    d.nom as nomDossier, i.exercice, l.date_scan, l.lot, count(*) as nbImage, i.lot_id 
                    FROM image i 
                    INNER JOIN lot l ON i.lot_id=l.id 
                    INNER JOIN dossier d ON l.dossier_id=d.id
                    INNER JOIN site s ON s.id=d.site_id  WHERE i.numerotation_local=:numerotationLocal AND i.supprimer= :supprimer AND i.download BETWEEN :dateDown1 AND :dateDown2
                    GROUP BY i.lot_id ORDER BY i.download DESC";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'numerotationLocal' => 0,
            'dateDown1' => $dateDown->setTime(0, 0, 0)->format('Y-m-d H:m:s'),
            'dateDown2' => $dateDown->setTime(23, 59, 59)->format('Y-m-d H:m:s'),
            'supprimer' => 0,
        ));

        $lotDown = $prep->fetchAll();
        return $lotDown;
    }


    /**
     * Lot à traiter dans Imputation
     *
     * @param bool $encours
     * @return array
     * @throws \Exception
     */
    public function lotImputation($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /*$query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userId))";*/
        $query = "SELECT id FROM dossier WHERE site_id IN
                  (SELECT id FROM site WHERE client_id IN
                    (SELECT client FROM responsable_client rc INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_id in 
                    (select operateur_id from rattachement rat WHERE rat.operateur_rat_id in
                    (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId))))";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId
        ));
        $resDossierId = $prep->fetchAll();
        $listIdDossier = array();
        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id','site','client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                  
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)                
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND SEP.categorie_id != :categorie_id
                  GROUP BY dossier_id,lot_id,categorie_id";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 6,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie_id' => 16
            ));
        }

        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id','site','client_id', 'client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                  
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)  
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id              
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND I.imputation = :imputation
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id AND SEP.categorie_id != :categorie_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
    
                  GROUP BY dossier_id,lot_id,categorie_id";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 7,
                'decouper' => 0,
                'supprimer' => 0,
                'imputation' => 1,
                'fini' => 0,
                'etape_traitement_id' => 14,
                'opsupprimer' => 0,
                'etape_traitement_org' => 14,
                'categorie_id' => 16
            ));

        }
        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {
                    /** @var Categorie $categorie */
                    $categorie = $this->getEntityManager()
                        ->getRepository('AppBundle:Categorie')
                        ->find($res[$i]->categorie_id);

                    /** @var Dossier $dossier */
                    $dossier = $this->getEntityManager()
                        ->getRepository('AppBundle:Dossier')
                        ->find($res[$i]->dossier_id);

                    /** @var Site $site */
                    $site = $dossier->getSite();

                    /** @var Client $client */
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getNom();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();
                    $res[$i]->categorie = $categorie->getLibelleNew();

                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }

                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }
        }

        /* LISTE CLIENTS DANS AFFECTATION TENUE */
        $query = "SELECT distinct l.dossier_id AS id FROM lot l JOIN affectation_panier_tenue a ON l.id=a.lot_id WHERE a.operateur_id in 
                        (SELECT operateur_id FROM  rattachement r WHERE r.operateur_rat_id=:userId) AND a.code=:code AND a.fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'code'=>'CTRL_OS',
            'fini'=> 0,
        ));

        $resDossierId = $prep->fetchAll();

        for ($i = 0; $i < count($resDossierId); $i++) {
            array_push($listIdDossier, $resDossierId[$i]->id);
        }

        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id','site','client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                  
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)                
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND SEP.categorie_id != :categorie_id
                  GROUP BY dossier_id,lot_id,categorie_id";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 6,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie_id' => 16
            ));
        }

        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id','site','client_id', 'client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                  
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)  
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id              
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND I.imputation = :imputation
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id AND SEP.categorie_id != :categorie_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
    
                  GROUP BY dossier_id,lot_id,categorie_id";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 7,
                'decouper' => 0,
                'supprimer' => 0,
                'imputation' => 1,
                'fini' => 0,
                'etape_traitement_id' => 14,
                'opsupprimer' => 0,
                'etape_traitement_org' => 14,
                'categorie_id' => 16
            ));

        }
        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {
            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            if (count($listIdDossier)>0) {
                if (in_array($res[$i]->dossier_id, $listIdDossier)) {
                    /** @var Categorie $categorie */
                    $categorie = $this->getEntityManager()
                        ->getRepository('AppBundle:Categorie')
                        ->find($res[$i]->categorie_id);

                    /** @var Dossier $dossier */
                    $dossier = $this->getEntityManager()
                        ->getRepository('AppBundle:Dossier')
                        ->find($res[$i]->dossier_id);

                    /** @var Site $site */
                    $site = $dossier->getSite();

                    /** @var Client $client */
                    $client = $site->getClient();

                    $res[$i]->dossier = $dossier->getNom();
                    $res[$i]->cloture = $dossier->getCloture();
                    $res[$i]->site = $site->getNom();
                    $res[$i]->site_id = $site->getNom();
                    $res[$i]->client = $client->getNom();
                    $res[$i]->client_id = $client->getId();
                    $res[$i]->categorie = $categorie->getLibelleNew();

                    if (!isset($lots[$res[$i]->lot_id])) {
                        $lots[$res[$i]->lot_id] = $res[$i];
                    } else {
                        $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                        $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
                    }

                    if ($dossier) {
                        $priorite = $this->getEntityManager()
                            ->getRepository('AppBundle:PrioriteLot')
                            ->getPrioriteDossier($dossier);
                        $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                        $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                        $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                        $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
                    } else {
                        $lots[$res[$i]->lot_id]->priorite = NULL;
                        $lots[$res[$i]->lot_id]->tache = '';
                        $lots[$res[$i]->lot_id]->color = '#696dcb';
                        $lots[$res[$i]->lot_id]->order = 9000;
                    }
                }
            }
        }

        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }


    /**
     * Lot à traiter dans Imputation
     *
     * @param bool $encours
     * @return array
     * @throws \Exception
     */
    public function lotTenueImputation($userId, $encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $code_etape = 'IMP';

        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id','site','client_id', 'client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                  
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)   
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id                 
                  WHERE A.status = :status  AND SEP.categorie_id != :categorie_id 
                  AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini AND code =:codeEtape)
                  AND A.decouper = :decouper AND I.supprimer = :supprimer AND
                  c.id in (SELECT client FROM responsable_client rc 
                  INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userRat' => $userId,
                'userId' => $userId,
                'status' => 6,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie_id' => 16,
                'codeEtape' => $code_etape,
                'fini'=>0,
            ));
        }

        else{
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie', L.dossier_id, 'dossier', 'cloture', 'site_id','site','client_id', 'client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)                  
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)  
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id    
                  INNER JOIN dossier d ON L.dossier_id=d.id
                  INNER JOIN site s ON d.site_id=s.id
                  INNER JOIN client c ON s.client_id=c.id           
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND I.imputation = :imputation
                  AND L.id NOT IN (select lot_id FROM affectation_panier_tenue WHERE operateur_id=:userId AND fini=:fini AND code =:codeEtape)
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id AND SEP.categorie_id != :categorie_id
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in 
                  (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  AND c.id in (SELECT client FROM responsable_client rc 
                  INNER JOIN rattachement r ON rc.responsable=r.operateur_id WHERE r.operateur_rat_id=:userRat)
                  GROUP BY dossier_id,lot_id,categorie_id";


            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'userId' => $userId,
                'userRat' => $userId,
                'codeEtape' => $code_etape,
                'fini' => 0,
                'status' => 7,
                'decouper' => 0,
                'supprimer' => 0,
                'imputation' => 1,
                'fini' => 0,
                'etape_traitement_id' => 14,
                'opsupprimer' => 0,
                'etape_traitement_org' => 14,
                'categorie_id' => 16
            ));

        }
        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {

            /** @var Categorie $categorie */
            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            /** @var Site $site */
            $site = $dossier->getSite();

            /** @var Client $client */
            $client = $site->getClient();

            $res[$i]->dossier = $dossier->getNom();
            $res[$i]->cloture = $dossier->getCloture();
            $res[$i]->site = $site->getNom();
            $res[$i]->site_id = $site->getNom();
            $res[$i]->client = $client->getNom();
            $res[$i]->client_id = $client->getId();
            $res[$i]->categorie = $categorie->getLibelleNew();

            if (!isset($lots[$res[$i]->lot_id])) {
                $lots[$res[$i]->lot_id] = $res[$i];
            } else {
                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
            }

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$res[$i]->lot_id]->priorite = NULL;
                $lots[$res[$i]->lot_id]->tache = '';
                $lots[$res[$i]->lot_id]->color = '#696dcb';
                $lots[$res[$i]->lot_id]->order = 9000;
            }
        }


        //=====================================Charger les lots dans lot_user_group==========================================//

        /*Charger liste lot et catégorie dans lot_user_group*/
        $query = "select lot_id, categorie_id as categid from affectation_panier_tenue where code=:codeEtape AND operateur_id =:userId AND fini=:fini";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId'=>$userId,
            'fini' => 0,
            'codeEtape' => $code_etape,
        ));

        $lotcategorie = $prep->fetchAll();

        $listLotCategorie = array();
        for ($i = 0; $i < count($lotcategorie); $i++) {

            if (isset($listLotCategorie[$lotcategorie[$i]->lot_id]))
            {
                $tab = $listLotCategorie[$lotcategorie[$i]->lot_id];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
            else
            {
                $tab = [];
                array_push($tab, $lotcategorie[$i]->categid);
                $listLotCategorie[$lotcategorie[$i]->lot_id] = $tab;
            }
        }


        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',
                  L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A
                  INNER JOIN separation SEP ON(SEP.image_id=A.image_id) 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  WHERE A.status = :status AND A.decouper = :decouper
                  AND SEP.categorie_id != :categorieId AND
                  SEP.categorie_id IN (select categorie_id from affectation_panier_tenue where operateur_id=:userId AND fini=:fini AND code=:codeEtape)
                  AND   
                  L.id IN (select lot_id from affectation_panier_tenue where operateur_id=:userId1 AND fini=:fini1 AND code=:codeEtape1)
                  GROUP BY dossier_id,lot_id,categorie_id";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 7,
            'decouper' => 0,
            'categorieId'=>16,
            'userId' => $userId,
            'userId1' => $userId,
            'fini' => 0,
            'fini1' => 0,
            'codeEtape'=> $code_etape,
            'codeEtape1'=> $code_etape,
        ));
        $res = $prep->fetchAll();
        for ($i = 0; $i < count($res); $i++) {

            /** @var Categorie $categorie */
            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            /** @var Site $site */
            $site = $dossier->getSite();

            /** @var Client $client */
            $client = $site->getClient();

            $res[$i]->dossier = $dossier->getNom();
            $res[$i]->cloture = $dossier->getCloture();
            $res[$i]->site = $site->getNom();
            $res[$i]->site_id = $site->getNom();
            $res[$i]->client = $client->getNom();
            $res[$i]->client_id = $client->getId();
            $res[$i]->categorie = $categorie->getLibelleNew();

            if (!isset($lots[$res[$i]->lot_id])) {
                $lots[$res[$i]->lot_id] = $res[$i];
            } else {
                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
            }

            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$res[$i]->lot_id]->priorite = NULL;
                $lots[$res[$i]->lot_id]->tache = '';
                $lots[$res[$i]->lot_id]->color = '#696dcb';
                $lots[$res[$i]->lot_id]->order = 9000;
            }
        }

        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    /**
     * Lot à traiter dans Contrôle Imputation
     *
     * @param bool $encours
     * @return array
     * @throws \Exception
     */
    public function lotCtrlImputation($encours = false)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

//        $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
//                  CAT.libelle AS categorie,
//                  D.id AS dossier_id,D.nom AS dossier,D.cloture AS cloture,S.id AS site_id,D.nom AS site,C.id AS client_id,C.nom AS client
//                  FROM image_a_traiter A
//                  INNER JOIN image I ON(A.image_id=I.id)
//                  INNER JOIN lot L ON(I.lot_id=L.id)
//                  INNER JOIN dossier D ON(L.dossier_id = D.id)
//                  INNER JOIN site S ON (D.site_id = S.id)
//                  INNER JOIN client C ON (S.client_id = C.id)
//                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
//                  INNER JOIN categorie CAT ON(SEP.categorie_id=CAT.id)
//                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer
//                  GROUP BY client_id,site_id,dossier_id,lot_id,categorie_id";


        if(!$encours) {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',L.dossier_id,'dossier','cloture','site_id','site','client_id','client'
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)                  
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND SEP.categorie_id != :categorie_id
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 8,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie_id' => 16
            ));
        }
        else {
            $query = "SELECT COUNT(A.image_id) AS nbimage,L.id AS lot_id,L.date_scan AS date_scan,SEP.categorie_id AS categorie_id,
                  'categorie',L.dossier_id,'dossier','cloture','site_id','site','client_id','client', P.operateur_id, OP.login AS operateur
                  FROM image_a_traiter A 
                  INNER JOIN image I ON(A.image_id=I.id)
                  INNER JOIN lot L ON(I.lot_id=L.id)
                  INNER JOIN separation SEP ON(SEP.image_id=I.id)
                  INNER JOIN panier P ON (P.image_id = I.id) 
                  INNER JOIN operateur OP ON (OP.id = P.operateur_id) 
                  INNER JOIN organisation ORG ON ORG.id = OP.organisation_id
                  WHERE A.status = :status AND A.decouper = :decouper AND I.supprimer = :supprimer AND SEP.categorie_id != :categorie_id
                  AND P.fini = :fini AND P.etape_traitement_id = :etape_traitement_id AND I.ctrl_imputation = :imputation
                  AND  (OP.supprimer = :opsupprimer AND ORG.id in (SELECT organisation_id FROM etape_traitement_organisation WHERE etape_traitement_id = :etape_traitement_org))
                  GROUP BY dossier_id,lot_id,categorie_id";

            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'status' => 9,
                'decouper' => 0,
                'supprimer' => 0,
                'categorie_id' => 16,
                'fini' => 0,
                'etape_traitement_id' => 15,
                'opsupprimer' => 0,
                'etape_traitement_org' => 15,
                'imputation' => 1
            ));
        }
        $res = $prep->fetchAll();

        $lots = [];
        for ($i = 0; $i < count($res); $i++) {

            /** @var Categorie $categorie */
            $categorie = $this->getEntityManager()
                ->getRepository('AppBundle:Categorie')
                ->find($res[$i]->categorie_id);

            /** @var Dossier $dossier */
            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);
            /** @var Site $site */
            $site = $dossier->getSite();
            /** @var Client $client */
            $client = $site->getClient();

            $res[$i]->dossier = $dossier->getNom();
            $res[$i]->cloture = $dossier->getCloture();
            $res[$i]->site = $site->getNom();
            $res[$i]->site_id = $site->getId();
            $res[$i]->client = $client->getNom();
            $res[$i]->client_id = $client->getId();
            $res[$i]->categorie = $categorie->getLibelleNew() ;

            if (!isset($lots[$res[$i]->lot_id])) {
                $lots[$res[$i]->lot_id] = $res[$i];
            } else {
                $lots[$res[$i]->lot_id]->nbimage += $res[$i]->nbimage;
                $lots[$res[$i]->lot_id]->categorie .= "/" . $res[$i]->categorie;
            }


            if ($dossier) {
                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);
                $lots[$res[$i]->lot_id]->priorite = isset($priorite['delai']) ? $priorite['delai'] : NULL;
                $lots[$res[$i]->lot_id]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
                $lots[$res[$i]->lot_id]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
                $lots[$res[$i]->lot_id]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
            } else {
                $lots[$res[$i]->lot_id]->priorite = NULL;
                $lots[$res[$i]->lot_id]->tache = '';
                $lots[$res[$i]->lot_id]->color = '#696dcb';
                $lots[$res[$i]->lot_id]->order = 9000;
            }
        }
        $lots = array_values($lots);
        usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });
        return $lots;
    }

    public function retelechargerLot($lotId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "UPDATE image SET download=null WHERE lot_id=:lotId AND numerotation_local=:numLoc";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'lotId' => $lotId,
            'numLoc' => 0,
        ));
        return true;
    }


    public function getLotTirage(&$nb_lot_niv1, &$nb_image_niv1)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT distinct L.id, L.date_scan,D.id as dossier_id,D.nom as dossier,D.cloture,S.id as site_id,S.nom as site,
                C.id as client_id,C.nom as client,count(*) as nbimage, I.exercice, L.lot,
                (IF((select date from taches_priorite_dossier WHERE dossier_id=D.id)  is null,
                date_add(date(now()), INTERVAL 2 YEAR),
                (select date from taches_priorite_dossier WHERE dossier_id=D.id))) 
                as priorite,
                (IF((select date from taches_priorite_dossier WHERE dossier_id=D.id)  is null,
                                to_days(date_add(date(now()), INTERVAL 2 YEAR)) - to_days(date(now())),
                            (select to_days(date) - to_days(date(now())) from taches_priorite_dossier WHERE dossier_id=D.id))
                ) as jourdif 
                FROM  image I 
                INNER JOIN lot L ON(I.lot_id=L.id)
                INNER JOIN lot_group LG ON (L.lot_group_id=LG.id)
                INNER JOIN dossier D ON(L.dossier_id=D.id)
                INNER JOIN site S ON(D.site_id=S.id)
                INNER JOIN client C ON(S.client_id=C.id)
                WHERE L.status =:status AND I.supprimer = :supprimer AND I.download is null 
                AND I.nom is not null and I.nom!= :nom AND  I.renommer= :renommer and D.date_stop_saisie is null
                AND LG.status>0 
                AND L.id NOT IN (SELECT lot_id FROM lot_a_telecharger)
                GROUP BY I.lot_id ORDER BY priorite, I.exercice, L.date_scan";

//client,dossier,exercice,date_scan,

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'status' => 0,
            'supprimer' => 0,
            'nom' => '',
            'renommer' => 1
        ));

        $lots = $prep->fetchAll();

        $nb_lot_niv1 = $prep->rowCount();
        $prioriteDossier = [];
        $dossierPris = [];
        $nb_image_niv1 = 0;
        foreach ($lots as $lot) {
            /*if (!in_array($lot->dossier_id, $dossierPris)) {
                $dossier = $this->getEntityManager()
                    ->getRepository('AppBundle:Dossier')
                    ->find($lot->dossier_id);
                $dossierPris[] = $dossier->getId();

                $priorite = $this->getEntityManager()
                    ->getRepository('AppBundle:PrioriteLot')
                    ->getPrioriteDossier($dossier);

                $prioriteDossier[$dossier->getId()] = $priorite;

            }*/
            $nb_image_niv1 += $lot->nbimage;
        }

        /*for ($i = 0; $i < count($lots); $i++) {

            $priorite = $prioriteDossier[$lots[$i]->dossier_id];

            $lots[$i]->priorite = isset($priorite['delai']) && $priorite['delai'] ? $priorite['delai']->format('Y-m-d') : NULL;
            $lots[$i]->tache = isset($priorite['tache']) ? $priorite['tache'] : '';
            $lots[$i]->color = isset($priorite['color']) ? $priorite['color'] : '#696dcb';
            $lots[$i]->order = isset($priorite['order']) ? $priorite['order'] : 9000;
        }*/
        /*usort($lots, function($a, $b) {
            return $a->order - $b->order;
        });*/
        return $lots;
    }


    public function getLotImageSuppr($dossier, $exercice, $datescan){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT distinct L.lot FROM lot L INNER JOIN image I ON I.lot_id = L.id 
                    WHERE L.dossier_id = :dossier_id AND L.date_scan = :datescan AND I.exercice = :exercice";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'dossier_id' => $dossier,
            'datescan' => $datescan,
            'exercice' => $exercice
        ));

        $lots = $prep->fetchAll();

        return $lots;
    }
}