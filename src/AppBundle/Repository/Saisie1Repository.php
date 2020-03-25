<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 23/08/2018
 * Time: 16:17
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Image;
use AppBundle\Entity\Saisie1;
use AppBundle\Entity\TdNdfBilanPcc;
use AppBundle\Entity\TdNdfSousnaturePcc;
use AppBundle\Entity\TdNdfSousnaturePcg;
use AppBundle\Entity\Ville;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class Saisie1Repository extends EntityRepository
{
    public function getSaisieByImage(Image $image){
        $qb = $this->createQueryBuilder('s')
            ->where('s.image = :image')
            ->setParameter('image', $image)
            ->getQuery()
            ->getResult();

        $saisie = null;

        if(count($qb) > 0){
            $saisie = $qb[0];
        }

        return $saisie;
    }

    public function getInfoFacturetteByImage($imageid){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT i.nom AS image_nom, i.id AS image_id, i.image_flague_id, i.imputation,s1.date_facture AS date, 
                s1.rs, s1.nbre_couvert, n.id as nature_d, n.libelle as nature, s1.sousnature_id,sn.libelle as sousnature,
                s1.mention_manuscrite_id, mm.libelle as mention_manuscrite,s1.code_postal, scat.libelle_new as souscategorie,
                mr.libelle as mode_reglement, s1.num_paiement,SUM(tva.montant_ttc) AS montant_ttc, SUM(tva.montant_ht) AS montant_ht, 
                ROUND((SUM(tva.montant_ttc)-SUM(tva.montant_ht)),2) as montant_tva, l.dossier_id, i.exercice, s1.distance, 
                'pic_doc', 'pic_banque' , 'cb', 'ndf', 'caisse', 'resultatpcc', 'resultatpcc_id', 'resultatpcg', 'tva', 'tva_id', 'bilan', 'bilan_id',
                'type_compte', 'categorie'
                FROM saisie1 s1 
                INNER JOIN image i ON i.id = s1.image_id
                INNER JOIN lot l ON l.id = i.lot_id
                INNER JOIN separation sep ON sep.image_id = i.id
                LEFT JOIN mode_reglement mr ON mr.id = s1.mode_reglement_id
                LEFT JOIN souscategorie scat ON scat.id = sep.souscategorie_id 
                LEFT JOIN tva_saisie1 tva ON tva.image_id = s1.image_id 
                LEFT JOIN sousnature sn ON sn.id = s1.sousnature_id
                LEFT JOIN nature n ON n.id = sn.nature_id
                LEFT JOIN mention_manuscrite mm ON mm.id = s1.mention_manuscrite_id
                WHERE s1.image_id = :imageid ;";

        $prep = $pdo->prepare($query);
        $prep->execute(
            ['imageid' => $imageid]
        );
        $res = $prep->fetchAll();


        for ($i=0; $i<count($res); $i++) {

            //Raha tsy renseigné ny nombre de couverts
            $pos = strpos( strtolower($res[$i]->sousnature), 'restaur');
            if($res[$i]->nbre_couvert === null && $pos !== false){
                if($res[$i]->montant_ttc >= 100){
                    $res[$i]->nbre_couvert = 2;
                }
            }

            $res[$i]->date = new \DateTime($res[$i]->date);

            $res[$i]->pic_banque = $this->getEntityManager()
                ->getRepository('AppBundle:Releve')
                ->getRelevesByPiece($res[$i]->dossier_id, $res[$i]->montant_ttc, $res[$i]->date, 35 );

            $res[$i]->cb = $this->getEntityManager()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getRelevesByPiece($res[$i]->dossier_id, $res[$i]->montant_ttc, $res[$i]->date, 35);

            $res[$i]->ndf = $this->getEntityManager()
                ->getRepository('AppBundle:Saisie1NdfDepense')
                ->getNdfDepensesByMontant($res[$i]->dossier_id, $res[$i]->montant_ttc);

            $res[$i]->caisse = $this->getEntityManager()
                ->getRepository('AppBundle:ImputationControleCaisse')
                ->getCaisseByMontant($res[$i]->dossier_id, $res[$i]->montant_ttc);


//            $codePostalDossier = $dossier->getCodePostal();
//            $codePostalFacturette = $res[$i]->code_postal;

//            /** @var Ville $villeDossier */
//            $villeDossier = $this->getEntityManager()
//                ->getRepository('AppBundle:Ville')
//                ->getVilleByCodePostal($codePostalDossier);
//
//            $villeFacturette = null;
//            /** @var Ville $villeFacturette */
//            if($codePostalFacturette !== null) {
//                $villeFacturette = $this->getEntityManager()
//                    ->getRepository('AppBundle:Ville')
//                    ->getVilleByCodePostal($codePostalFacturette);
//            }

//            $opts = array('https' => array('method' => "GET"));
//            $context = stream_context_create($opts);
//
//            $distance = 0;
//
//
//            if($villeDossier !== null && $villeFacturette !== null) {
//
//                $key = 'R0US1zIfoBCGLKoRwPbTdHk70VBEhNvn';
//
//                $file = file_get_contents('https://www.mapquestapi.com/directions/v2/route?key=' . $key .
//                    '&from=' . $villeDossier->getNom() . '&to=' . $villeFacturette->getNom() . '&outFormat=json&ambiguities=ignore&routeType=fastest&doReverseGeocode=false&enhancedNarrative=false&avoidTimedConditions=false', false, $context);
//
//                $mapquest = json_decode($file);
//
//                $route = $mapquest->route;
//
//                $distance = $route->distance;
//            }
//
//
//            $distance = round($distance * 1.609344, 0);
//            $res[$i]->distance = $distance;



            $sousnature = null;

            if($res[$i]->sousnature_id !== null) {
                $sousnature = $this->getEntityManager()
                    ->getRepository('AppBundle:Sousnature')
                    ->find($res[$i]->sousnature_id);
            }

            $dossier = $this->getEntityManager()
                ->getRepository('AppBundle:Dossier')
                ->find($res[$i]->dossier_id);

            /** @var TdNdfSousnaturePcc $tdResultat */
            $tdResultat = $this->getEntityManager()
                ->getRepository('AppBundle:TdNdfSousnaturePcc')
                ->getTdNdfSousnaturePccByCriteres($dossier, $sousnature, $res[$i]->nbre_couvert, $res[$i]->distance);

            /** @var TdNdfSousnaturePcg $tdResultatPcg */
            $tdResultatPcg = $this->getEntityManager()
                ->getRepository('AppBundle:TdNdfSousnaturePcg')
                ->getTdNdfSousnaturePcgByCriteres($sousnature, $res[$i]->nbre_couvert, $res[$i]->distance);

            if($tdResultat !== null){
                if($tdResultat->getPccResultat() !== null) {
                    $res[$i]->resultatpcc = $tdResultat->getPccResultat()->getCompte();
                    $res[$i]->resultatpcc_id = $tdResultat->getPccResultat()->getId();
                }

                if($tdResultat->getPccTva() !== null) {
                    $res[$i]->tva = $tdResultat->getPccTva()->getCompte();
                    $res[$i]->tva_id = $tdResultat->getPccTva()->getId();
                }
            }

            if($tdResultatPcg !== null){
                $res[$i]->resultatpcg = $tdResultatPcg;
            }

            $trouve = false;

            if(count($res[$i]->pic_banque) > 0 || count($res[$i]->ndf) > 0
                || count($res[$i]->caisse) > 0 || count($res[$i]->cb) > 0){
                $trouve = true;
            }

            $typecompte = $this->getEntityManager()
                ->getRepository('AppBundle\Entity\TdNdfBilanPcg')
                ->getTypeCompte($trouve, $dossier);

            $res[$i]->type_compte = $typecompte;

            /** @var TdNdfBilanPcc $tdBilan */
            $tdBilan = $this->getEntityManager()
                ->getRepository('AppBundle:TdNdfBilanPcc')
                ->getTdNdfBilanPccByDossierTypeCompte($dossier, $typecompte);

            if($tdBilan !== null){
//                $res[$i]->bilan =$tdBilan->getPcc()->getCompte();
//                $res[$i]->bilan_id = $tdBilan->getPcc()->getId();

                $res[$i]->bilan =$tdBilan;
//                $res[$i]->bilan_id = $tdBilan->getPcc()->getId();
            }

            $indication = $this->getIndicationByImage($imageid);
            if($indication['personnel'] === true){
                $res[$i]->categorie = 11;
            }
            else{
                $res[$i]->categorie = 10;
            }
        }
        return $res;
    }

    public function getIndicationByImage($imageid)
    {
        /** @var Image $image */
        $image = $this->getEntityManager()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = $image->getLot()->getDossier();

        $saisie1 = null;

        /** @var Saisie1 $saisie1 */
        $saisie1 = $this->getEntityManager()
            ->getRepository('AppBundle:Saisie1')
            ->getSaisieByImage($image);

        $personnel = false;
        $societe = false;
        if (null !== $saisie1) {
            if ($saisie1->getMentionManuscrite() !== null) {
                if ($saisie1->getMentionManuscrite()->getPersonnel() === 1) {
                    $personnel = true;
                }
            }
            if (!$personnel) {
                if ($saisie1->getModeReglement() !== null) {
                    if ($saisie1->getModeReglement()->getCode() === 'CB') {
                        //Jerena raha mitovy @ compte banquen'ilay dossier ilay cb
                        $numpaiement = $saisie1->getNumPaiement();
                        if ($numpaiement !== '') {
                            $banqueComptes = $this->getEntityManager()
                                ->getRepository('AppBundle:BanqueCompte')
                                ->findBy(array('dossier' => $dossier));

                            foreach ($banqueComptes as $banqueCompte) {
                                $codeBanque = $banqueCompte->getNumcompte();

                                if (strpos($codeBanque, $numpaiement)) {
                                    $societe = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $res = [
                'personnel' => $personnel,
                'societe' => $societe
            ];


            $res['saisie'] = $saisie1;

            return $res;

        }

        $res = [
            'personnel' => false,
            'societe' => false,
            'saisie1' => null
        ];

        return $res;

    }
}