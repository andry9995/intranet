<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 23/08/2018
 * Time: 08:31
 */

namespace TenueBundle\Controller;


use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\HistoriqueCategorie;
use AppBundle\Entity\ImageFlague;
use AppBundle\Entity\Imputation;
use AppBundle\Entity\ImputationControleCaisse;
use AppBundle\Entity\NdfUtilisateur;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\Pcg;
use AppBundle\Entity\Saisie1;
use AppBundle\Entity\Separation;
use AppBundle\Entity\TdNdfBilanPcc;
use AppBundle\Entity\TdNdfBilanPcg;
use AppBundle\Entity\TdNdfSousnaturePcc;
use AppBundle\Entity\TdNdfSousnaturePcg;
use AppBundle\Entity\TvaImputation;
use AppBundle\Entity\Vehicule;
use AppBundle\Repository\TdNdfResultatPccRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TraitementFacturetteController extends Controller
{
    public function traitementFacturetteAction(){

        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();

        $exercices = [2017,2018,2019,2020];

        return $this->render('@Tenue/Saisie/traitement-facturette.html.twig', array(
            'clients' => $clients,
            'exercices' => $exercices
        ));
    }

    public function traitementFacturetteContentAction(Request $request){
        if($request->isXmlHttpRequest()){

            $post = $request->request;

            $dossierId = $post->get('dossierid');
            $exercice = $post->get('exercice');

            /** @var Dossier $dossier */
            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierId);

            /** @var NdfUtilisateur[] $ndfUtilisateurs */
            $ndfUtilisateurs = $this->getDoctrine()
                ->getRepository('AppBundle:NdfUtilisateur')
                ->findBy(array('dossier' => $dossier));

            /** @var BanqueCompte[] $banqueComptes */
            $banqueComptes = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->findBy(array('dossier' => $dossier));

            /** @var Vehicule[] $vehicules */
            $vehicules = $this->getDoctrine()
                ->getRepository('AppBundle:Vehicule')
                ->findBy(array('dossier' => $dossier));

            $nbFacturette = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getNombreImageBySousCategories($dossier->getId(), $exercice, [133,134]);

            $nbRecap = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getNombreImageBySousCategories($dossier->getId(), $exercice, [136] );

            $nbEnAchat = $this->getDoctrine()
                ->getRepository('AppBundle:HistoriqueCategorie')
                ->getNombreEnAchat($dossier->getId(), $exercice);

            $nbFacturette += $nbEnAchat;

            $ImageRapprochees = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getListeImageRapprochee($dossier->getId(), $exercice, [133,134], 1);

            $nbImageRapprochee = \count($ImageRapprochees);

            $nbImageARapprocher = $nbFacturette - $nbImageRapprochee;

            return $this->render('@Tenue/Saisie/traitement-facturette-content.html.twig', array(
                'dossier' => $dossier,
                'ndfUtilisateurs' => $ndfUtilisateurs,
                'banqueComptes' => $banqueComptes,
                'vehicules' => $vehicules,
                'nbFacturette' => $nbFacturette,
                'nbRecap' => $nbRecap,
                'nbImageRapprochee' => $nbImageRapprochee,
                'nbImageARapprocher' => $nbImageARapprocher,
                'nbEnAchat' => $nbEnAchat
            ));
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function traitementFacturetteSituationAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $post = $request->query;

            $dossierid = $post->get('dossierid');
            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierid);

            $exercice = $post->get('exercice');

            $nbFacturette = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getNombreImageBySousCategories($dossier->getId(), $exercice, [133, 134]);

            $nbRecap = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getNombreImageBySousCategories($dossier->getId(), $exercice, [136]);

            $nbEnAchat = $this->getDoctrine()
                ->getRepository('AppBundle:HistoriqueCategorie')
                ->getNombreEnAchat($dossier->getId(), $exercice);

            $nbFacturette += $nbEnAchat;

            $ImageRapprochees = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getListeImageRapprochee($dossier->getId(), $exercice, [133, 134], 1);
            $nbImageRapprochee = \count($ImageRapprochees);

            $nbImageARapprocher = $nbFacturette - $nbImageRapprochee;

            return $this->render('@Tenue/Saisie/situation-facturette.html.twig', array(
                'nbFacturette' => $nbFacturette,
                'nbRecap' => $nbRecap,
                'nbImageRapprochee' => $nbImageRapprochee,
                'nbImageARapprocher' => $nbImageARapprocher,
                'nbEnAchat' => $nbEnAchat
            ));
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function facturetteListAction(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $post = $request->request;

            $dossierid = $post->get('dossierid');
            $exercice = $post->get('exercice');
            $periodeDu = $post->get('periodedu');
            $periodeAu = $post->get('periodeau');
            $status = $post->get('status');

            if ($status !== '') {
                $status = intval($status);
            }

            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierid);

            $periodeDu = \DateTime::createFromFormat('d/m/Y', $periodeDu);
            $periodeAu = \DateTime::createFromFormat('d/m/Y', $periodeAu);

            $imageFacturettes = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getListeImageRapprochee($dossier->getId(), $exercice, [133, 134], $status, $periodeDu, $periodeAu);

            $infoSaisie = [];
            $rows = [];
            foreach ($imageFacturettes as $image_id) {

                $image = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->find($image_id);

                $info = $this->getDoctrine()
                    ->getRepository('AppBundle:Saisie1')
                    ->getInfoFacturetteByImage($image->getId());

                $rapprochementIcon = '<i class="fa fa-eye-slash btn btn-block btn-xs btn-danger" title="Pas de rapprochement" ></i>';
                $indicationIcon = '<i class="fa fa-eye-slash btn btn-block btn-xs btn-danger" title="Pas de rapprochement" ></i>';

                if (\count($info) > 0) {
                    $infoSaisie[] = $info;

                    $res = $info[0];

                    $trouve = false;
                    if (count($res->pic_banque) > 0 || count($res->ndf) > 0 ||
                        count($res->caisse) || count($res->cb)) {
                        $trouve = true;
                    }

                    if($res->image_flague_id !== null){
                        $rapprochementIcon = '<i class="fa fa-check btn btn-block btn-xs btn-primary" title="Rapprochement déjà effectuté" ></i>';
                        $indicationIcon = '<i class="fa fa-check btn btn-block btn-xs btn-primary" title="Rapprochement déjà effectuté" ></i>';
                    }

                    else if ($trouve) {
                        $rapprochementIcon = '<i class="fa fa-eye btn btn-block btn-xs btn-primary" title="Clicker pour voir les rapprochements" ></i>';
                        $indicationIcon = '<i class="fa fa-eye btn btn-block btn-xs btn-primary" title="Clicker pour voir l\'indication du paiement" ></i>';
                    }

                    $categorieId = $res->categorie;

                    $categorie = $this->getDoctrine()
                        ->getRepository('AppBundle:Categorie')
                        ->find($categorieId);

                    $pccResCompte = null;
                    $pccResId = null;
                    $pccTvaCompte = null;
                    $pccTvaId = null;
                    $pccBilanCompte = null;
                    $pccBilanId = null;
                    $tiersCompte = null;
                    $tiersId = null;
                    $isTiers = false;

                    $resultatPcc = null;
                    $resultatPcg = null;
                    $bilan = null;

                    $isImputation = false;
                    if($res->imputation > 1){
                        $isImputation = true;
                    }

                    if($isImputation){
                        $tvaImputations = $this->getDoctrine()
                            ->getRepository('AppBundle:TvaImputation')
                            ->findBy(array('image' => $image));

                        if(count($tvaImputations)) {
                            $tvaImputation = $tvaImputations[0];

                            if($tvaImputation->getPcc() !== null) {
                                $pccResCompte = $tvaImputation->getPcc()->getCompte();
                                $pccResId = $tvaImputation->getPcc()->getId();
                            }
                            if($tvaImputation->getPccTva() !== null) {
                                $pccTvaCompte = $tvaImputation->getPccTva()->getCompte();
                                $pccTvaId = $tvaImputation->getPccTva()->getId();
                            }
                            if($tvaImputation->getTiers()) {
                                $isTiers = true;
                                $tiersCompte = $tvaImputation->getTiers()->getCompteStr();
                                $tiersId = $tvaImputation->getTiers()->getId();
                            }
                            else{
                                if($tvaImputation->getPccBilan() !== null) {
                                    $tiersCompte = $tvaImputation->getPccBilan()->getCompte();
                                    $tiersId = $tvaImputation->getPccBilan()->getId();
                                }
                            }
                        }
                    }

                    if ($res->resultatpcc !== 'resultatpcc') {
                        $resultatPcc = $res->resultatpcc;
                        $resultatPccid = $res->resultatpcc_id;

                        if(!$isImputation) {
                            $pccResCompte = $resultatPcc;
                            $pccResId = $resultatPccid;
                        }
                    }

                    if ($res->tva !== 'tva') {
                        $tvaPcc = $res->tva;
                        $tvaPccid = $res->tva_id;

                        if(!$isImputation) {
                            $pccTvaCompte = $tvaPcc;
                            $pccTvaId = $tvaPccid;
                        }
                    }

                    if($res->resultatpcg !== 'resultatpcg'){
                        /** @var TdNdfSousnaturePcg $resultatPcg */
                        $resultatPcg = $res->resultatpcg;
                    }

                    if(!$isImputation) {
                        if ($res->bilan !== 'bilan') {
                            /** @var TdNdfBilanPcc $bilan */
                            $bilan = $res->bilan;

                            if($bilan->getPcc() !== null){
                                $tiersCompte = $bilan->getPcc()->getCompte();
                                $tiersId = $bilan->getPcc()->getId();
                            }

                        }
                    }


//                    $rows[] = [
//                        'id' => $image->getId(),
//                        'cell' => [
//                            $res->date->format('Y-m-d'),
//                            $res->image_nom,
//                            $res->rs,
//                            $res->nature,
//                            $res->sousnature,
//                            $res->sousnature_id,
//                            $res->souscategorie,
//                            $res->distance,
//                            $res->nbre_couvert,
//                            $indicationIcon,
//                            $rapprochementIcon,
//                            ($categorie === null) ? '' : $categorie->getLibelleNew(),
//                            ($categorie === null) ? -1 : $categorie->getId(),
//                            $res->montant_ttc,
//                            $res->montant_tva,
//                            $res->montant_ht,
//                            ($res->resultat === 'resultat') ? '' : $res->resultat,
//                            ($res->resultat_id === 'resultat_id') ? '' : $res->resultat_id,
//                            ($res->tva === 'tva') ? '' : $res->tva,
//                            ($res->tva_id === 'tva_id') ? '' : $res->tva_id,
//                            ($resultat === null) ? '' : $resultat->getId(),
//                            ($res->bilan === 'bilan') ? '' : $res->bilan,
//                            ($res->bilan_id === 'bilan_id') ? '' : $res->bilan_id,
//                            $res->type_compte,
//                            $res->image_flague_id,
//                            ($res->imputation > 1) ? '1' : '0'
//                        ]
//                    ];




                    $rows[] = [
                        'id' => $image->getId(),
                        'cell' => [
                            $res->date->format('Y-m-d'),
                            $res->image_nom,
                            $res->rs,
                            $res->nature,
                            $res->sousnature,
                            $res->sousnature_id,
                            $res->souscategorie,
                            $res->distance,
                            $res->nbre_couvert,
                            $indicationIcon,
                            $rapprochementIcon,
                            ($categorie === null) ? '' : $categorie->getLibelleNew(),
                            ($categorie === null) ? -1 : $categorie->getId(),
                            $res->montant_ttc,
                            $res->montant_tva,
                            $res->montant_ht,
                            ($pccResCompte === null) ? '' : $pccResCompte,
                            ($pccResId === null) ? '' : $pccResId,
                            ($pccTvaCompte === null) ? '' : $pccTvaCompte,
                            ($pccTvaId === null) ? '' : $pccTvaId,
                            ($resultatPcg === null) ? '' : $resultatPcg->getId(),
                            ($tiersCompte === null) ? '' : $tiersCompte,
                            ($tiersId === null) ? '' : $tiersId,
                            ($isTiers) ? 1 : 0,
                            $res->type_compte,
                            $res->image_flague_id,
                            ($res->imputation > 1) ? '1' : '0'
                        ]
                    ];
                }
            }
            return new JsonResponse(['rows' => $rows]);
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function facturetteEditAction(Request $request){
        if($request->isXmlHttpRequest()) {

            $post = $request->request;

            $imageId = $post->get('id');
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageId);

            $pccTvaId = $post->get('pcctvaid');
            $pccTva = null;
            if ($pccTvaId !== '') {
                $pccTva = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($pccTvaId);
            }

            $pccResultatId = $post->get('pccresultatid');
            $pccResultat = null;
            if ($pccResultatId !== '') {
                $pccResultat = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($pccResultatId);
            }

            $istiers = false;

            if( $post->get('istiers') === 1){
                $istiers = true;
            }

            $pccBilanId = $post->get('pccbilanid');
            $pccBilan = null;
            $tiers = null;


            if ($pccBilanId !== '') {
                if(!$istiers) {
                    $pccBilan = $this->getDoctrine()
                        ->getRepository('AppBundle:Pcc')
                        ->find($pccBilanId);
                }
                else{
                    $tiers = $this->getDoctrine()
                        ->getRepository('AppBundle:Tiers')
                        ->find($pccBilanId);
                }
            }


            $dosserId = $post->get('dossierid');
            $dossier = null;
            if ($dosserId !== '') {
                $dossier = $this->getDoctrine()
                    ->getRepository('AppBundle:Dossier')
                    ->find($dosserId);
            }

            $categorieId = $post->get('categorieid');
            $categorie = null;
            if($categorieId !== ''){
                $categorie = $this->getDoctrine()
                    ->getRepository('AppBundle:Categorie')
                    ->find($categorieId);
            }

            $ttc = $post->get('ttc');
            $ht = $post->get('ht');
            $tva = $post->get('tva');


            $tdNdfSousnatureId = $post->get('tdndfsousnatureid');
            $tdNdfSousnaturePcg = $this->getDoctrine()
                ->getRepository('AppBundle:TdNdfSousnaturePcg')
                ->find($tdNdfSousnatureId);

            if(null === $tdNdfSousnaturePcg){
                return new JsonResponse([
                    'type' => 'error',
                    'message' => 'Table de decision introuvable'
                ]);
            }

            $pccBilanTypecompte = $post->get('pccbilantypecompte');



            // Jerena aloha raha misy pcc resultat, bilan
            $canSave = false;
            if($pccBilan !== null && ($pccResultat !== null || $tiers !== null)){
                if($ttc - $ht == 0){
                    if($pccTva === null || $tva == 0){
                        $canSave = true;
                    }
                }
                else{
                    if($pccTva !== null){
                        $canSave = true;
                    }
                }
            }

            if(!$canSave){
                return new JsonResponse([
                    'type' => 'error',
                    'message' => 'L\'ecriture n\'est pas equilibrée'
                ]);
            }

            if($categorie=== null){
                return new JsonResponse([
                    'type' => 'error',
                    'message' => 'Catégorie non trouvée'
                ]);
            }

            $imputations = $this->getDoctrine()
                ->getRepository('AppBundle:Imputation')
                ->findBy(array('image' => $image));


            $em = $this->getDoctrine()->getManager();

            $separations = $this->getDoctrine()
                ->getRepository('AppBundle:Separation')
                ->findBy(array('image' => $image));

            $separation = null;
            $oldSeparation = null;

            if (count($separations) > 0) {
                $separation = $separations[0];
                $oldSeparation = new Separation();
                $oldSeparation->setSoussouscategorie($separation->getSoussouscategorie())
                ->setSouscategorie($separation->getSouscategorie())
                ->setCategorie($separation->getCategorie());
            }

            $soussouscategorie = null;

            if($categorie->getCode() === 'CODE_FRNS') {
                /** @var Pcg $pcgResultat */
                $pcgResultat = $tdNdfSousnaturePcg->getPcgResultat();

                $soussouscategorie = $this->getDoctrine()
                    ->getRepository('AppBundle:Soussouscategorie')
                    ->getSoussouscategorieByPcg($pcgResultat);

                //Miova ny catégorie any @ séparation
                if($separation !== null){

                    if (null !== $soussouscategorie) {
                        $separation->setCategorie($soussouscategorie->getSouscategorie()->getCategorie());
                        $separation->setSousCategorie($soussouscategorie->getSouscategorie());
                        $separation->setSoussouscategorie($soussouscategorie);

                        $em->flush();

                        //Enregistrer-na ny logs catégorie
                        $logCategorie = new HistoriqueCategorie();
                        $logCategorie->setImage($image);
                        $logCategorie->setCategorie($oldSeparation->getCategorie());
                        $logCategorie->setSouscategorie($oldSeparation->getSouscategorie());
                        $logCategorie->setSoussouscategorie($oldSeparation->getSoussouscategorie());
                        $logCategorie->setDateModification(new \DateTime('now'));
                        $logCategorie->setMotif('Facturette');
                        $logCategorie->setOperateur($this->getUser());

                        $em->persist($logCategorie);
                        $em->flush();

                    }
                }
            }

            $saisies = $this->getDoctrine()
                ->getRepository('AppBundle:Saisie1')
                ->findBy(array('image' => $image));

            $saisie = null;
            if (count($saisies) > 0) {
                $saisie = $saisies[0];
            }


            //Enregistrer-na any @imputation & tva_imputations ny lignes
            if(count($imputations) > 0){
                $imputation = $imputations[0];

                if($categorie->getCode() === 'CODE_FRNS') {
                    if ($soussouscategorie !== null) {
                        $imputation->setSouscategorie($soussouscategorie->getSouscategorie())
                            ->setSoussouscategorie($soussouscategorie);
                    } else {
                        $imputation->setSouscategorie($separation->getSouscategorie())
                            ->setSoussouscategorie($separation->getSoussouscategorie());
                    }
                }
                else {
                    $imputation->setSouscategorie($separation->getSouscategorie())
                        ->setSoussouscategorie($separation->getSoussouscategorie());
                }

                $em->flush();

                $tvaImputations = $this->getDoctrine()
                    ->getRepository('AppBundle:TvaImputation')
                    ->findBy(array('image' => $image));

                foreach ($tvaImputations as $tvaImputation){
                    $tvaImputation->setPcc($pccResultat)
                        ->setPccTva($pccTva);

                    if(!$istiers) {
                        $tvaImputation->setPccBilan($pccBilan);
                        $tvaImputation->setTiers(null);
                    }
                    else{
                        $tvaImputation->setTiers($tiers);
                        $tvaImputation->setPccBilan(null);
                    }


                    if($categorie->getCode() === 'CODE_FRNS') {
                        if ($soussouscategorie !== null) {
                            $tvaImputation->setSoussouscategorie($soussouscategorie);
                        }
                    }

                    $em->persist($tvaImputation);
                    $em->flush();
                }

            }
            else{
                $imputation = new Imputation();
                $imputation->setJournalDossier($saisie->getJournalDossier())
                    ->setImage($saisie->getImage())
                    ->setRs($saisie->getRs())
                    ->setAbrevRs($saisie->getAbrevRs())
                    ->setNumClient($saisie->getNumClient())
                    ->setSiret($saisie->getSiret())
                    ->setTypePiece($saisie->getTypePiece())
                    ->setTypeAchatVente($saisie->getTypeAchatVente())
                    ->setDateLivraison($saisie->getDateLivraison())
                    ->setNature1($saisie->getNature1())
                    ->setNature2($saisie->getNature2())
                    ->setPeriodeD1($saisie->getPeriodeD1())
                    ->setPeriodeD2($saisie->getPeriodeD2())
                    ->setPeriodeF1($saisie->getPeriodeF1())
                    ->setPeriodeF2($saisie->getPeriodeF2())
                    ->setDevise($saisie->getDevise())
                    ->setTauxDevise($saisie->getTauxDevise())
                    ->setNumBl($saisie->getNumBl())
                    ->setDateCmd($saisie->getDateCmd())
                    ->setNumCommande($saisie->getNumCommande())
                    ->setDateFacture($saisie->getDateFacture())
                    ->setDebutPeriode($saisie->getDebutPeriode())
                    ->setFinPeriode($saisie->getFinPeriode())
                    ->setNumFacture($saisie->getNumFacture())
                    ->setPage($saisie->getPage())
                    ->setModeReglement($saisie->getModeReglement())
                    ->setNumPaiement($saisie->getNumPaiement())
                    ->setDateEcheance($saisie->getDateEcheance())
                    ->setDateReglement($saisie->getDateReglement())
                    ->setZoneA($saisie->getZoneA())
                    ->setZoneB($saisie->getZoneB())
                    ->setZoneC($saisie->getZoneC())
                    ->setZoneD($saisie->getZoneD())
                    ->setEscompte($saisie->getEscompte())
                    ->setSoldeDebut($saisie->getSoldeDebut())
                    ->setSoldeFin($saisie->getSoldeFin())
                    ->setPays($saisie->getPays())
                    ->setChrono($saisie->getChrono())
                    ->setPnc($saisie->getPnc())
                    ->setSousnature($saisie->getSousnature())
                    ->setCerfa($saisie->getCerfa())
                    ->setTypeCaisse($saisie->getTypeCaisse())
                    ->setOrganisme($saisie->getOrganisme())
                    ->setCodeTiers($saisie->getCodeTiers())
                    ->setMontantPaye($saisie->getMontantPaye())
                    ->setComptePcc($saisie->getComptePcc())
                    ->setTauxIntracomm($saisie->getTauxIntracomm())
                    ->setAvecIntracom($saisie->getAvecIntracom())
                    ->setBanqueCompte($saisie->getBanqueCompte());

                if($categorie->getCode() === 'CODE_FRNS') {
                    if ($soussouscategorie !== null) {
                        $imputation->setSouscategorie($soussouscategorie->getSouscategorie())
                            ->setSoussouscategorie($soussouscategorie);
                    }
                }
                else{
                    $imputation->setSouscategorie($separation->getSouscategorie())
                        ->setSoussouscategorie($separation->getSoussouscategorie());
                }

                $em->persist($imputation);
                $em->flush();

                $tvaSaisies = $this->getDoctrine()
                    ->getRepository('AppBundle:TvaSaisie1')
                    ->findBy(array('image' => $image));

                foreach ($tvaSaisies as $tvaSaisie1){
                    $tvaImputation = new TvaImputation();
                    $tvaImputation->setImage($image)
                        ->setSousnature($tvaSaisie1->getSousnature())
                        ->setMontantHt($tvaSaisie1->getMontantHt())
                        ->setTauxTva($tvaSaisie1->getTauxTva())
                        ->setTypeVente($tvaSaisie1->getTypeVente())
                        ->setPeriodeDeb($tvaSaisie1->getPeriodeDeb())
                        ->setPeriodeFin($tvaSaisie1->getPeriodeFin())
                        ->setDateLivraison($tvaSaisie1->getDateLivraison())
                        ->setNature($tvaSaisie1->getNature())
                        ->setPcc($pccResultat)
                        ->setPccTva($pccTva);

                    if(!$istiers){
                        $tvaImputation->setPccBilan($pccBilan);
                    }
                    else{
                        $tvaImputation->setTiers($tiers);
                    }


                    if($categorie->getCode() === 'CODE_FRNS') {
                        if ($soussouscategorie !== null) {
                            $tvaImputation->setSoussouscategorie($soussouscategorie);
                        } else {
                            $tvaImputation->setSoussouscategorie($tvaSaisie1->getSoussouscategorie());
                        }
                    }
                    else {
                        $tvaImputation->setSoussouscategorie($tvaSaisie1->getSoussouscategorie());
                    }

                    $em->persist($tvaImputation);
                    $em->flush();

                }
            }

            //Atao mise à jour ny status an'ny image
            $image->setSaisie1(3);
            $image->setSaisie2(3);
            $image->setCtrlSaisie(3);
            $image->setImputation(2);

            $em->flush();

            if ($tdNdfSousnaturePcg !== null) {
                $sousnature = $tdNdfSousnaturePcg->getSousnature();
                $participant = $tdNdfSousnaturePcg->getNbParticipant();
                $distance = $tdNdfSousnaturePcg->getDistance();

                /** @var TdNdfSousnaturePcc $tdNdfSousnaturePcc */
                $tdNdfSousnaturePcc = $this->getDoctrine()
                    ->getRepository('AppBundle:TdNdfSousnaturePcc')
                    ->getTdNdfSousnaturePccByCriteres($dossier, $sousnature, $participant, $distance);

                if ($tdNdfSousnaturePcc === null) {
                    if ($pccResultat !== null) {
                        $tdNdfSousnaturePcc = new TdNdfSousnaturePcc();
                        $tdNdfSousnaturePcc->setSousnature($sousnature);
                        $tdNdfSousnaturePcc->setNbParticipant($participant);
                        $tdNdfSousnaturePcc->setDistance($distance);
                        $tdNdfSousnaturePcc->setPccResultat($pccResultat);
                    }
                    if ($tdNdfSousnaturePcg->getPcgTva() === null) {
                        $tdNdfSousnaturePcc->setPccTva(null);
                    } else {
                        $tdNdfSousnaturePcc->setPccTva($pccTva);
                    }

                    $em->persist($tdNdfSousnaturePcc);
                    $em->flush();
                } else {
                    if ($pccResultat !== null) {
                        $tdNdfSousnaturePcc->setPccResultat($pccResultat);
                    }
                    if ($tdNdfSousnaturePcg->getPcgTva() === null) {
                        $tdNdfSousnaturePcc->setPccTva(null);
                    } else {
                        $tdNdfSousnaturePcc->setPccTva($pccTva);
                    }
                    $em->flush();
                }
            }


            if ($pccBilan !== null) {
                /** @var TdNdfBilanPcc $tdNdfBilanPcc */
                $tdNdfBilanPcc = $this->getDoctrine()
                    ->getRepository('AppBundle:TdNdfBilanPcc')
                    ->getTdNdfBilanPccByDossierTypeCompte($dossier, $pccBilanTypecompte);

                if ($tdNdfBilanPcc === null) {
                    $tdNdfBilanPcc = new TdNdfBilanPcc();
                    $tdNdfBilanPcc->setPcc($pccBilan);
                    $tdNdfBilanPcc->setDossier($dossier);
                    $tdNdfBilanPcc->setTypeCompte($pccBilanTypecompte);

                    $em->persist($tdNdfBilanPcc);
                    $em->flush();
                } else {
                    $tdNdfBilanPcc->setPcc($pccBilan);
                    $em->flush();
                }
            }

            return new JsonResponse([
                'type' => 'success',
                'message' => 'Enregistrement effectué'
            ]);
        }
        throw  new AccessDeniedException('Accès refusé');
    }

    public function indicactionAction(Request $request){

        if($request->isXmlHttpRequest()) {

            $post = $request->request;

            $imageid = $post->get('imageid');
//        $image = $this->getDoctrine()
//            ->getRepository('AppBundle:Image')
//            ->find($imageid);

            $indication = [
                'personnel' => false,
                'societe' => false
            ];

            if ($imageid !== '') {
                $indication = $this->getDoctrine()
                    ->getRepository('AppBundle:Saisie1')
                    ->getIndicationByImage($imageid);
            }

            /** @var Saisie1 $saisie */
            $saisie = $indication['saisie'];


            return $this->render('@Tenue/Saisie/indication.html.twig', array(
                'personnel' => $indication['personnel'],
                'societe' => $indication['societe'],
                'saisie' => $saisie));
        }
        throw new AccessDeniedException('Accès refusé');

    }

    public function rapprochementListAction(Request $request){
        if($request->isXmlHttpRequest()) {

            $post = $request->request;
            $imageid = $post->get('imageid');
            $imageflagueid = $post->get('imageflagueid');

            if($imageflagueid !== ''){
                $rows = [];

                $imageflague = $this->getDoctrine()
                    ->getRepository('AppBundle:ImageFlague')
                    ->find($imageflagueid);

                $releves = $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->findBy(array('imageFlague' => $imageflague));




                if (count($releves) > 0) {
                    foreach ($releves as $rb) {
                        $rows[] = [
                            'id' => $rb->getId(),
                            'cell' => [
                                $rb->getBanqueCompte()->getBanque()->getNom(),
                                16,
                                $rb->getDateReleve()->format('Y-m-d'),
                                $rb->getImage()->getNom(),
                                $rb->getImage()->getId(),
                                $rb->getLibelle(),
                                $rb->getDebit(),
                                ''
                            ]
                        ];
                    }
                }

                $caisses = $this->getDoctrine()
                    ->getRepository('AppBundle:ImputationControleCaisse')
                    ->findBy(array('imageFlague' => $imageflague));

                if(count($caisses) > 0){
                    /** @var ImputationControleCaisse $caisse */
                    foreach ($caisses as $caisse) {
                        $rows[] = [
                            'id' => $caisse->getId(),
                            'cell' => [
                                'Caisse',
                                14,
                                $caisse->getDate()->format('Y-m-d'),
                                $caisse->getImage()->getNom(),
                                $caisse->getImage()->getId(),
                                $caisse->getLibelle(),
                                $caisse->getSortieTtc(),
                                ''
                            ]
                        ];
                    }
                }


                return new JsonResponse(['rows' => $rows]);

//                $autres = $this->getDoctrine()
//                    ->getRepository('AppBundle:BanqueSousCategorieAutre')
//                    ->findBy(array('image' => $imageflague));
//


            }

            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageid);

            $rapprochements = $this->getDoctrine()
                ->getRepository('AppBundle:Saisie1')
                ->getInfoFacturetteByImage($image->getId());

            $rows = [];

            $picBanques = [];
            $picDocs = [];
            $ndfs = [];
            $caisses = [];
            $cbs = [];

            $selectIcon = '<i class="fa fa-check icon-action rg-action" title="Selectionner"></i>';

            if (count($rapprochements) > 0) {
                $res = $rapprochements[0];

                if ($res->pic_banque !== 'pic_banque')
                    $picBanques = $res->pic_banque;
                if ($res->pic_doc !== 'pic_doc')
                    $picDocs = $res->pic_doc;
                if ($res->ndf !== 'ndf')
                    $ndfs = $res->ndf;
                if ($res->caisse !== 'caisse')
                    $caisses = $res->caisse;
                if ($res->cb !== 'cb')
                    $cbs = $res->cb;

            }

            if (count($picBanques) > 0) {
                foreach ($picBanques as $banque) {
                    $rows[] = [
                        'id' => $banque->releve_id,
                        'cell' => [
                            $banque->banque_nom,
                            16,
                            $banque->date_releve,
                            $banque->image_nom,
                            $banque->image_id,
                            $banque->libelle,
                            $banque->debit,
                            $selectIcon
                        ]
                    ];
                }
            }

            if (count($cbs) > 0) {
                foreach ($cbs as $cb) {
                    $rows[] = [
                        'id' => $cb->cb_id,
                        'cell' => [
                            $cb->banque_nom,
                            160,
                            $cb->date,
                            $cb->image_nom,
                            $cb->image_id,
                            $cb->libelle,
                            $cb->montant,
                            $selectIcon
                        ]
                    ];
                }
            }

            if (count($ndfs) > 0) {
                foreach ($ndfs as $ndf) {
                    $rows[] = [
                        'id' => $ndf->depense_id,
                        'cell' => [
                            'Note de Frais',
                            11,
                            $ndf->date,
                            $ndf->image_nom,
                            $ndf->image_id,
                            $ndf->libelle,
                            $ndf->ttc,
                            $selectIcon
                        ]
                    ];
                }
            }

            if (count($caisses) > 0) {
                foreach ($caisses as $caisse) {
                    $rows[] = [
                        'id' => $caisse->caisse_id,
                        'cell' => [
                            'Caisse',
                            14,
                            $caisse->date,
                            $caisse->image_nom,
                            $caisse->image_id,
                            $caisse->libelle,
                            $caisse->sortie_ttc,
                            $selectIcon
                        ]
                    ];
                }
            }

            if (count($picDocs) > 0) {
                foreach ($picDocs as $picDoc) {
                    $rows[] = [
                        'id' => $picDoc->imputation_id,
                        'cell' => [
                            'Pic Doc',
                            10,
                            $picDoc->date_facture,
                            $picDoc->image_nom,
                            $picDoc->image_id,
                            $picDoc->tiers,
                            $picDoc->montant,
                            $selectIcon
                        ]

                    ];
                }
            }

            return new JsonResponse(['rows' => $rows]);
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function rapprochementEditAction(Request $request){

        if($request->isXmlHttpRequest()) {

            $post = $request->request;

            $imageAFlaguerId = $post->get('imageaflaguer');
            $documentType = $post->get('documenttype');
            $documentId = $post->get('documentid');

            $imageAFlaguer = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageAFlaguerId);

            //Jerena aloha raha efa flagué
            if ($imageAFlaguer !== null) {
                if ($imageAFlaguer->getImageFlague() === null) {

                    $em = $this->getDoctrine()->getManager();

                    $imageFlague = new ImageFlague();
                    $imageFlague->setDateCreation(new \DateTime('now'));
                    $em->persist($imageFlague);
                    $em->flush();

                    $imageAFlaguer->setImageFlague($imageFlague);

                    switch (intval($documentType)) {
                        //Note de frais
                        case 11:
                            break;
                        //Caisse
                        case 14:
                            $document = $this->getDoctrine()
                                ->getRepository('AppBundle:ImputationControleCaisse')
                                ->find($documentId);

                            if (null !== $document)
                                $document->setImageFlague($imageFlague);
                            break;
                        //Relevé Banque
                        case 16:
                            $document = $this->getDoctrine()
                                ->getRepository('AppBundle:Releve')
                                ->find($documentId);

                            if (null !== $document)
                                $document->setImageFlague($imageFlague);
                            break;
                        //CB
                        case 160:
                            $document = $this->getDoctrine()
                                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                                ->find($documentId);

                            if (null !== $document)
                                $document->setImageFlague($imageFlague);
                            break;
                    }

                    $em->flush();

                    return new JsonResponse(['type' => 'success',
                        'imageflagueid' => $imageFlague->getId(),
                        'message' => 'rapprochement effectué']);
                }

                return new JsonResponse(['type' => 'error',
                    'imageflagueid' => '',
                    'message' => 'image deja flaguée'
                ]);
            }
            return new JsonResponse(['type' => 'error',
                'imageflagueid' => '',
                'message' => 'image introuvable'
            ]);
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function rapprochementPcgAction(Request $request, $typecompte){
        if($request->isXmlHttpRequest()){

            $post = $request->request;
            $rows = [];

            $tdId = $post->get('tdndfsousnatureid');
            $pccBilanTypeCompte = $post->get('pccbilantypecompte');

            switch ($typecompte){

                case 'resultat':
                case 'tva':
                    /** @var TdNdfSousnaturePcg $resultat */
                    $resultat = $this->getDoctrine()
                        ->getRepository('AppBundle:TdNdfSousnaturePcg')
                        ->find($tdId);

                    if($resultat !== null){
                        if($typecompte === 'resultat') {
                            if ($resultat->getPcgResultat() !== null) {
                                $rows[] = [
                                    'id' => $resultat->getPcgResultat()->getId(),
                                    'parent' => '#',
                                    'text' => $resultat->getPcgResultat()->getCompte() . ' ' .
                                        $resultat->getPcgResultat()->getIntitule()
                                ];
                            }
                        }
                        else{
                            if($resultat->getPcgTva() !== null){
                                $rows[] = [
                                    'id' =>$resultat->getPcgTva()->getId(),
                                    'parent' => '#',
                                    'text' => $resultat->getPcgTva()->getCompte() . ' ' .
                                        $resultat->getPcgTva()->getIntitule()
                                ];

                            }
                        }
                    }
                    break;

                case 'bilan':
                    // $pccBilanTypeCompte: 0:ndf, 1:ndfei, 2:achat, 3:attente

                    /** @var TdNdfBilanPcg[] $bilans */
                    $bilans = $this->getDoctrine()
                        ->getRepository('AppBundle:TdNdfBilanPcg')
                        ->findBy(array('typeCompte' => $pccBilanTypeCompte));

                    foreach ($bilans as $bilan){
                        $rows[] = [
                            'id' =>$bilan->getPcg()->getId(),
                            'parent' => '#',
                            'text' => $bilan->getPcg()->getCompte() . ' ' .
                                $bilan->getPcg()->getIntitule()
                        ];
                    }
                    break;
            }
            return new JsonResponse($rows);
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function rapprochementPccAction(Request $request, $dossierid, $pcgid){
        if($request->isXmlHttpRequest()){

            $pcg = $this->getDoctrine()
                ->getRepository('AppBundle:Pcg')
                ->find($pcgid);

            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierid);

            $pccs = [];
            if($dossier !== null && $pcg !== null) {
                $pccs = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, $pcg->getCompte());
            }

            $rows =[];
            /** @var Pcc $pcc */
            foreach ($pccs as $pcc) {

                $temp = [
                    'id' => $pcc->getId(),
                    'parent' => '#',
                    'text' => $pcc->getCompte() . ' -- ' . $pcc->getIntitule()
                ];

                if (!in_array($temp, $rows)) {
                    $rows[] = $temp;
                }

            }

            return new JsonResponse($rows);
        }
        throw new AccessDeniedException('Accès refusé');
    }

    public function distanceAction(){

        // marseille
       $lon1 = 0.094238797;
       $lat1 = 0.755683511;

       //paris

        $lon2 = 0.084383441;
        $lat2 = 0.798659174;

        $theta = $lon1 - $lon2;
        $dist = sin(($lat1)) * sin(($lat2)) +  cos(($lat1)) * cos(($lat2)) * cos(($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return new JsonResponse( $miles *  1.609344);

    }
}