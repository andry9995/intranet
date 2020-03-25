<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 15/03/2019
 * Time: 09:30
 */

namespace BanqueBundle\Controller;


use AppBundle\Controller\Fonction;
use AppBundle\Controller\Json;
use AppBundle\Entity\BanqueSousCategorieAutre;
use AppBundle\Entity\BanqueTypePcg;
use AppBundle\Entity\Image;
use AppBundle\Entity\ImageATraiter;
use AppBundle\Entity\Imputation;
use AppBundle\Entity\ImputationControle;
use AppBundle\Entity\Lot;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\Releve;
use AppBundle\Entity\SaisieControle;
use AppBundle\Entity\Separation;
use AppBundle\Entity\Tiers;
use AppBundle\Entity\TvaTaux;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SaisieObController extends Controller
{
    public function typeTiersAction(Request $request)
    {
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $banqueTypes = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueType')
            ->findBy([],['libelle' => 'ASC']);

        return $this->render('@Banque/Banque/saisie/banque_select_banque_type.html.twig',
            ['banqueTypes' => $banqueTypes]
        );
    }

    public function tauxTvaAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var TvaTaux[] $tauxTvas */
        $tauxTvas = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->getTvaTaux(1);


        return $this->render('BanqueBundle:Banque/saisie:banque_select_tva.html.twig',
            ['tvas' => $tauxTvas]
        );
    }


    public function natureAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');


        $natures = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->getSoussouscategorieBySouscategories([
                $this->getDoctrine()
                    ->getRepository('AppBundle:Souscategorie')
                    ->find(133)
            ]);

        return $this->render('BanqueBundle:Banque/saisie:banque_select_nature.html.twig',
            ['natures' => $natures]
        );
    }

    public function pccAction(Request $request, $create){
        if(!$request->isXmlHttpRequest())
            throw  new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossierid = $get->get('dossierid');
        $comptes = $get->get('comptes');

        $pccs = [];
        $tiers = [];

        if($comptes === null)
            $comptes=[];

        if($dossierid !==  ''){
            /** @var Image $image */
           $dossier = $this->getDoctrine()
               ->getRepository('AppBundle:Dossier')
               ->find($dossierid);

            $creates = [];
            if($create != '' && gettype($create) !== 'array'){
                $creates = explode(',', $create);
            }


            $pccTiers = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccTiersByDossierArrayLikes($dossier, $comptes, $creates, $this->getUser());

            /** @var Pcc[] $pccs */
            $pccs = $pccTiers['pccs'];
            /** @var Tiers[] $tiers */
            $tiers = $pccTiers['tiers'];
        }
        return $this->render('BanqueBundle:Banque/saisie:banque_select_pcc.html.twig',[
            'pccs' => $pccs,
            'tiers' => $tiers,
            'type' => 0
        ])
            ;
    }

    public function pccBanqueTypeAction(Request $request, $type, $create)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $creates = [];
        if($create != ''){
            $creates = explode(',', $create);
        }

        $dossierid = $request->query->get('dossierid');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $pccs = [];
        $tiers = [];

        $banqueType = null;
        if(intval($type) === 1){
            $banqueType = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueType')
                ->find($request->query->get('banquetypeid'));
        }
        else{
            $rowid = $request->query->get('rowid');

            if($rowid !== 'new_row'){
                $autre = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueSousCategorieAutre')
                    ->find($rowid);
                if($autre !== null) {
                    $banqueType = $autre->getBanqueType();
                }
            }
        }

        if($banqueType !== null) {
            $comptes = [];


            $banqueTypePcgs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueTypePcg')
                ->getBanqueTypePcgByTypes($banqueType, [2]);


            /** @var BanqueTypePcg $banqueTypePcg */
            foreach ($banqueTypePcgs as $banqueTypePcg) {
                $comptes [] = $banqueTypePcg->getPcg()->getCompte();
            }

            if(count($comptes)> 0) {
                $pccTiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccTiersByDossierArrayLikes($dossier, $comptes, $creates, $this->getUser());

                /** @var Pcc[] $pccs */
                $pccs = $pccTiers['pccs'];
                /** @var Tiers[] $tiers */
                $tiers = $pccTiers['tiers'];
            }

        }

        return $this->render('BanqueBundle:Banque/saisie:banque_select_pcc.html.twig',[
            'pccs' => $pccs,
            'tiers' => $tiers,
            'type' => $type
        ]);


    }

    public function enteteRemiseAction(Request $request)
    {
        $dateremise = $request->request->get('dateremise');
        if($dateremise !== ''){
            $dateremise = \DateTime::createFromFormat('d/m/Y', $dateremise);
        }
        else{
            $dateremise = null;
        }

        $totalecheque = $request->request->get('totalecheque');
        $nombrecheque = $request->request->get('nombrecheque');

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($request->request->get('banquecompte'));

        $imageid = $request->request->get('imagid');
        //test si existe deja a developper avec tous les champs pour recherche doublon nom image different

        /** @var Image $image */
        $image= $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $em = $this->getDoctrine()
            ->getManager();

        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        foreach ($bankEntities as $bankEntity) {
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {
                $sc = $scs[0];

                $sc->setBanqueCompte($banquecompte);
                $sc->setDateReglement($dateremise);
                $sc->setNbreCouvert($nombrecheque);
                $sc->setMontantTtc($totalecheque);


                $em->flush();

            } else {
                switch ($bankEntity){
                    case 'SaisieControle':
                        $sc = new SaisieControle();
                        break;
                    case 'Imputation':
                        $sc = new Imputation();
                        break;
                    case 'ImputationControle':
                        $sc = new ImputationControle();
                        break;
                    default:
                        $sc = new SaisieControle();
                }

                $sc->setImage($image);
                $sc->setBanqueCompte($banquecompte);
                $sc->setBanqueCompte($banquecompte);
                $sc->setDateReglement($dateremise);
                $sc->setNbreCouvert($nombrecheque);
                $sc->setMontantTtc($totalecheque);

                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $image->setStatus(3);
        if($image->getCtrlSaisie() < 3) {
            $image->setCtrlSaisie(2);
            $image->setSaisie1(2);
            $image->setSaisie2(2);
            $image->setImputation(2);
            $image->setCtrlImputation(2);

            $em->flush();
        }
        return new JsonResponse($image->getId());
    }

    public function enteteLcrAction(Request $request)
    {
        $dateregl = \DateTime::createFromFormat('d/m/Y', $request->request->get('dateregl'));
        if (!$dateregl)
            $dateregl = null;

        $dateech = \DateTime::createFromFormat('d/m/Y', $request->request->get('dateech'));
        if (!$dateech)
            $dateech = null;

        $datefacture = \DateTime::createFromFormat('d/m/Y', $request->request->get('datef'));
        if(!$datefacture)
            $datefacture = null;

        $isFrais = $request->request->get('isfrais');

        $totallcr = $request->request->get('total');
        $totalfrais = $request->request->get('totalfrais');


        if(json_decode($isFrais) === true){
            $totallcr = $totalfrais;
        }


        if(floatval($totallcr)  === 0.00) {
            $totallcr = null;
        }

        $nombreligne = $request->request->get('nombreligne');

        if (trim($nombreligne) == '') {
            $nombreligne = null;
        }

        $numf = $request->request->get('numf');
        if(trim($numf) == ''){
            $numf = null;
        }

        $releve = $request->request->get('relevelcr');

        $engagement = $request->request->get('engagement');



        $imageId = $request->request->get('imagid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);

        $em = $this->getDoctrine()->getManager();

        if(intval($engagement) > -1){
            $image->setFlaguer(intval($engagement));
            $em->flush();
        }

        $banquecompteid = $request->request->get('banquecompteid');

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);


        //test et ajout mis à jour table saisie controle
        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        foreach ($bankEntities as $bankEntity) {
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {
                $sc = $scs[0];

                $sc->setBanqueCompte($banquecompte);
                $sc->setNumReleve($releve);
                $sc->setDateReglement($dateregl);
                $sc->setDateEcheance($dateech);
                $sc->setMontantTtc($totallcr);
                $sc->setNbreCouvert($nombreligne);
                $sc->setDateFacture($datefacture);
                $sc->setNumFacture($numf);


                $em->flush();

            } else {
                switch ($bankEntity){
                    case 'SaisieControle':
                        $sc = new SaisieControle();
                        break;
                    case 'Imputation':
                        $sc = new Imputation();
                        break;
                    case 'ImputationControle':
                        $sc = new ImputationControle();
                        break;
                    default:
                        $sc = new SaisieControle();
                }

                $sc->setImage($image);
                $sc->setBanqueCompte($banquecompte);
                $sc->setNumReleve($releve);
                $sc->setDateReglement($dateregl);
                $sc->setDateEcheance($dateech);
                $sc->setMontantTtc($totallcr);
                $sc->setNbreCouvert($nombreligne);
                $sc->setDateFacture($datefacture);
                $sc->setNumFacture($numf);

                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $image->setStatus(3);
        if($image->getCtrlSaisie() < 3) {
            $image->setCtrlSaisie(2);
            $image->setSaisie1(2);
            $image->setSaisie2(2);
            $image->setImputation(2);
            $image->setCtrlImputation(2);

            $em->flush();
        }
        return new JsonResponse($image->getId());
    }

    public function enteteVirAction(Request $request)
    {

        $datev = null;

        $datev = \DateTime::createFromFormat('d/m/Y', $request->request->get('datevi'));

        $total = $request->request->get('total');
        $imageid = $request->request->get('imagid');

        /** @var Image $imageE */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($request->request->get('banquecompte'));

        $em = $this->getDoctrine()
            ->getManager();

        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        foreach ($bankEntities as $bankEntity) {
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {
                $sc = $scs[0];

                $sc->setBanqueCompte($banquecompte);
                $sc->setDateEcheance($datev);
                $sc->setMontantTtc($total);

                $em->flush();

            } else {
                switch ($bankEntity){
                    case 'SaisieControle':
                        $sc = new SaisieControle();
                        break;
                    case 'Imputation':
                        $sc = new Imputation();
                        break;
                    case 'ImputationControle':
                        $sc = new ImputationControle();
                        break;
                    default:
                        $sc = new SaisieControle();
                }

                $sc->setImage($image);
                $sc->setBanqueCompte($banquecompte);

                $sc->setDateEcheance($datev);
                $sc->setMontantTtc($total);

                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $image->setStatus(3);
        if($image->getCtrlSaisie() < 3) {
            $image->setCtrlSaisie(2);
            $image->setSaisie1(2);
            $image->setSaisie2(2);
            $image->setImputation(2);
            $image->setCtrlImputation(2);

            $em->flush();
        }

        return new JsonResponse($image->getId());
    }


    public function carteCreditReleveAction(Request $request)
    {
        $total = $request->request->get('total');
        $imageid = $request->request->get('imagid');
        $dateCarte = $request->request->get('datecarte');
        $numcbid = $request->request->get('numcbid');
        $typecbid = $request->request->get('typecb');
        $numcb = null;
        $typecb = null;

        if($numcbid !== ''){
            $numcb = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->find($numcbid);
        }


        $dateOb = \DateTime::createFromFormat('d/m/Y', $dateCarte);

        if($dateOb === false) {
            $dateOb = null;
        }

        $engagement = $request->request->get('engagement');

        $em = $this->getDoctrine()
            ->getManager();

        /** @var Image $imageE */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if(intval($engagement) > -1){
            $image->setFlaguer(intval($engagement));
            $em->flush();
        }

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($request->request->get('banquecompteid'));


        //Enregistrement typecb (perso ou société)
        if($numcb !== null){
            if($typecbid !== ''){
                if(intval($typecbid) !== -1){
                    $typecb = $typecbid;
                }
            }
            $numcb->setTypeCb($typecb);

            $em->flush();
        }

        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        foreach ($bankEntities as $bankEntity) {
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {
                $sc = $scs[0];


                $sc->setDateReglement($dateOb);
                $sc->setBanqueCompte($banquecompte);
                $sc->setMontantTtc($total);
                $sc->setCarteBleuBanqueCompte($numcb);

                $em->flush();

            } else {
                switch ($bankEntity){
                    case 'SaisieControle':
                        $sc = new SaisieControle();
                        break;
                    case 'Imputation':
                        $sc = new Imputation();
                        break;
                    case 'ImputationControle':
                        $sc = new ImputationControle();
                        break;
                    default:
                        $sc = new SaisieControle();
                }

                $sc->setDateReglement($dateOb);
                $sc->setImage($image);
                $sc->setBanqueCompte($banquecompte);
                $sc->setMontantTtc($total);
                $sc->setCarteBleuBanqueCompte($numcb);

                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $image->setStatus(3);
        if($image->getCtrlSaisie() < 3) {
            $image->setCtrlSaisie(2);
            $image->setSaisie1(2);
            $image->setSaisie2(2);
            $image->setImputation(2);
            $image->setCtrlImputation(2);

            $em->flush();
        }

        return new JsonResponse($image->getId());
    }

    public function carteDebitAction(Request $request)
    {

        $imageid = $request->request->get('imagid');

        /** @var Image $imageE */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($request->request->get('banquecompteid'));

        $numcbid = $request->request->get('numcbid');
        $typecbid = $request->request->get('typecb');
        $numcb = null;
        $typecb = null;

        if($numcbid !== ''){
            $numcb = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->find($numcbid);
        }

        $em = $this->getDoctrine()
            ->getManager();

        //Enregistrement typecb (perso ou société)
        if($numcb !== null){
            if($typecbid !== ''){
                if(intval($typecbid) !== -1){
                    $typecb = $typecbid;
                }
            }
            $numcb->setTypeCb($typecb);

            $em->flush();
        }

        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        foreach ($bankEntities as $bankEntity) {
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {
                $sc = $scs[0];

                $sc->setBanqueCompte($banquecompte);
                $sc->setCarteBleuBanqueCompte($numcb);

                $em->flush();

            } else {
                switch ($bankEntity){
                    case 'SaisieControle':
                        $sc = new SaisieControle();
                        break;
                    case 'Imputation':
                        $sc = new Imputation();
                        break;
                    case 'ImputationControle':
                        $sc = new ImputationControle();
                        break;
                    default:
                        $sc = new SaisieControle();
                }

                $sc->setImage($image);
                $sc->setBanqueCompte($banquecompte);
                $sc->setCarteBleuBanqueCompte($numcb);

                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $image->setStatus(3);
        if($image->getCtrlSaisie() < 3) {
            $image->setCtrlSaisie(2);
            $image->setSaisie1(2);
            $image->setSaisie2(2);
            $image->setImputation(2);
            $image->setCtrlImputation(2);

            $em->flush();
        }

        return new JsonResponse($image->getId());
    }

    public function carteCreditAction(Request $request)
    {

        $imageid = $request->request->get('imagid');

        /** @var Image $imageE */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($request->request->get('banquecompteid'));

        $em = $this->getDoctrine()
            ->getManager();

        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        foreach ($bankEntities as $bankEntity) {
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {
                $sc = $scs[0];

                $sc->setBanqueCompte($banquecompte);

                $em->flush();

            } else {
                switch ($bankEntity){
                    case 'SaisieControle':
                        $sc = new SaisieControle();
                        break;
                    case 'Imputation':
                        $sc = new Imputation();
                        break;
                    case 'ImputationControle':
                        $sc = new ImputationControle();
                        break;
                    default:
                        $sc = new SaisieControle();
                }

                $sc->setImage($image);
                $sc->setBanqueCompte($banquecompte);
                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $image->setStatus(3);
        if($image->getCtrlSaisie() < 3) {
            $image->setCtrlSaisie(2);
            $image->setSaisie1(2);
            $image->setSaisie2(2);
            $image->setImputation(2);
            $image->setCtrlImputation(2);

            $em->flush();
        }

        return new JsonResponse($image->getId());
    }

    public function lcrLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);

            $dossier = $image->getLot()
                ->getDossier();
        }
        $rows = [];

        $total = 0;

        $em = $this->getDoctrine()
            ->getManager();

        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){

            $total += $ob->getMontant();
            $compte = '';
            if($ob->getCompteTiers() !== null){
                $compte = $ob->getCompteTiers()->getCompteStr().' - '.$ob->getCompteTiers()->getIntitule();
            }
            else{
                if($ob->getCompteBilan() !== null){
                    $compte = $ob->getCompteBilan()->getCompte().' - '.$ob->getCompteBilan()->getIntitule();
                }
                elseif($ob->getCompteChg() !== null){
                    $compte = $ob->getCompteChg()->getCompte().' - '.$ob->getCompteChg()->getIntitule();
                }
            }

            if($compte === '' && $dossier !== null){
                /** @var Pcc $attente */
                $attente = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getCompteAttente($dossier, 0);

                if($attente !== null){
                    $ob->setCompteBilan($attente);
                    $em->flush();
                    $compte = $attente->getCompte(). ' - '.$attente->getIntitule();
                }
            }

            $rows []= [
              'id' => $ob->getId(),
              'cell' => [
                  'lcr_n_ordre' => $ob->getOrdre(),
                  'lcr_n_facture' => $ob->getNumFacture(),
                  'lcr_date_facture' => ($ob->getDateFacture() === null) ? '' : $ob->getDateFacture()->format('y-m-d'),
                  'lcr_tireur' => $ob->getNomTiers(),
                  'lcr_compte' => $compte,
                  'lcr_montant' => $ob->getMontant(),
                  'lcr_action' =>  '<i class="fa fa-save icon-action lcr-save" title="Enregistrer"></i><i class="fa fa-trash icon-action lcr-delete" title="Supprimer"></i>'
              ]
            ];
        }

        return new JsonResponse([
            'rows' => $rows,
            'userdata' => ['lcr_montant'=>$total]
        ]);
    }

    public function lcrLigneEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $lid = $request->request->get('id');

        $lcrdate = null;
        if($request->request->get('lcr_date_facture') !== '') {
            $lcrdate = \DateTime::createFromFormat('d/m/Y', $request->request->get('lcr_date_facture'));
        }
        $lcrordre = $request->request->get('lcr_n_ordre');
        $lcrtireur = $request->request->get('lcr_tireur');
        $lcrfacture = $request->request->get('lcr_n_facture');
        $lcrmontant = $request->request->get('lcr_montant');


        $compteBilanId = $request->request->get('lcr_compte');
        $compteBilan = null;
        $tiers = null;
        if($compteBilanId !== ''){
            if(strpos($compteBilanId, '0-') !== false) {
                $compteBilanId = str_replace('0-', '', $compteBilanId);
                $compteBilan = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteBilanId);
            }
            elseif(strpos($compteBilanId, '1-') !== false){
                $compteBilanId = str_replace('1-', '', $compteBilanId);
                $tiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Tiers')
                    ->find($compteBilanId);
            }
        }

        if($compteBilan === null && $tiers === null && $dossier !== null){
            $compteBilan = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getCompteAttente($dossier, 0);
            ;

        }


        $em = $this->getDoctrine()
            ->getManager();

        if ($lid != 'new_row') {

            $autre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            if($autre){

                $autre->setOrdre($lcrordre);
                $autre->setNomTiers($lcrtireur);
                $autre->setMontant($lcrmontant);
                $autre->setNumFacture($lcrfacture);
                $autre->setDateFacture($lcrdate);
                $autre->setCompteBilan($compteBilan);
                $autre->setCompteTiers($tiers);

                $em->flush();

                return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);
            }

        } else {

            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(5);

            $typeTiers = $this->getDoctrine()
                ->getRepository('AppBundle:TypeTiers')
                ->find(5);

            $autre = new BanqueSousCategorieAutre();
            $autre->setImage($image);
            $autre->setOrdre($lcrordre);
            $autre->setNomTiers($lcrtireur);
            $autre->setMontant($lcrmontant);
            $autre->setNumFacture($lcrfacture);
            $autre->setDateFacture($lcrdate);
            $autre->setTypeTiers($typeTiers);
            $autre->setSousCategorie($souscategorie);
            $autre->setCompteBilan($compteBilan);
            $autre->setCompteTiers($tiers);

            $em->persist($autre);

            $em->flush();

            $em->refresh($autre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $autre->getId()]);


        }
        return new JsonResponse(['type' => 'error', 'message' => '']);

    }

    public function lcrLignesImportAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $file = $request->files->get('upload');

        if (!is_null($file)) {
            $filename = uniqid() . "." . $file->getClientOriginalExtension();

            $path = $this->get('kernel')->getRootDir() . '/../web/ocr';
            $file->move($path, $filename); // move the file to a path
             $path_file = $path . '/' . $filename;
            $excelObj = $this->get('phpexcel')->createPHPExcelObject($path_file);
            $sheet = $excelObj->getActiveSheet()->toArray(null, true, true, true);


            $imageList = [];
            $em = $this->getDoctrine()->getManager();

            $typeTiers = $this->getDoctrine()
                ->getRepository('AppBundle:TypeTiers')
                ->find(5);

            foreach ($sheet as $i => $row) {
                if (strlen(trim($row['A'])) > 7) {

                    $nomTmp = trim($row['A']);
                    $images = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->findBy(array('nom' => $nomTmp));

                    if (count($images) > 0) {
                        $imageTmp = $images[0];

                        $ordre = $row['B'];
                        if($ordre === ''){
                            $ordre = null;
                        }
                        $numFact = $row['C'];
                        if($numFact === ''){
                            $numFact = null;
                        }
                        $dateFact = null;

                        if (trim($row['D']) != '') {

                            if(strpos($row['D'], '/') !== false) {
                                $datetmp = explode('/', $row['D']);
                                $day = $datetmp[1];
                                $month = $datetmp[0];
                                $year = $datetmp[2];

                                $dateFact = \DateTime::createFromFormat('d/m/Y', $day . '/' . $month . '/' . $year);

                            }
                            else{
                                $dateFact = \PHPExcel_Shared_Date::ExcelToPHPObject($row['D']);
                            }

                        } else {
//                            continue;
                        }

                        $tireur = $row['E'];
                        if($tireur === ''){
                            $tireur = null;
                        }

                        $montant = $row['F'];
                        if($montant !== ''){
                            $montant = $this->floatvalue($montant);
                        }
                        else{
                            $montant = 0;
                        }

                        if (!in_array($imageTmp, $imageList)) {
                            $imageList[] = $imageTmp;

                            //fafana daholo aloha ny releve lcr an'ilay image
                            $obs = $this->getDoctrine()
                                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                                ->findBy(array('image' => $imageTmp));

                            foreach ($obs as $ob) {
                                $em->remove($ob);

                            }
                        }

                        //Inserer-na ilay releve lcr
                        $autre = new BanqueSousCategorieAutre();

                        $autre->setImage($imageTmp);
                        $autre->setOrdre($ordre);
                        $autre->setNomTiers($tireur);
                        $autre->setMontant($montant);
                        $autre->setNumFacture($numFact);
                        $autre->setDateFacture($dateFact);
                        $autre->setTypeTiers($typeTiers);
                        $autre->setSousCategorie($this->getDoctrine()
                            ->getRepository('AppBundle:Souscategorie')
                            ->find(5)
                        );

                        $em->persist($autre);
                        $em->flush();
                    }
                    continue;
                }
            }
        }
        return new JsonResponse(['type' => 'success', 'message' => 'importation effectuée']);
    }


    public function obLigneDeleteAction(Request $request){
        $id = $request->request->get('id');

        $autre = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueSousCategorieAutre')
            ->find($id);

        if($autre !== null){
            $em = $this->getDoctrine()->getManager();

            $em->remove($autre);
            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Ligne supprimée']);
        }

        return new JsonResponse(['type' => 'error', 'message' => 'Erreur']);
    }


    public function virementLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);
        }

        $rows = [];
        $total = 0;

        $em = $this->getDoctrine()
            ->getManager();

        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){

            $total +=$ob->getMontant();

            $compte = '';

            if($ob->getCompteBilan() !== null){
                $compte = $ob->getCompteBilan()->getCompte(). ' - '. $ob->getCompteBilan()->getIntitule();
            }
            elseif($ob->getCompteTiers() !== null){
                $compte = $ob->getCompteTiers()->getCompteStr(). ' - '. $ob->getCompteTiers()->getIntitule();
            }

            if($compte === '' && $dossier !== null){
                /** @var Pcc $attente */
                $attente = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getCompteAttente($dossier, 0);

                if($attente !== null){
                    $ob->setCompteBilan($attente);
                    $em->flush();
                    $compte = $attente->getCompte(). ' - '.$attente->getIntitule();
                }
            }

            $rows []= [
                'id' => $ob->getId(),
                'cell' => [
                    'virement_date' => ($ob->getDate() === null) ? '' : $ob->getDate()->format('Y-m-d'),
                    'virement_num' => $ob->getNumVirement(),
                    'virement_num_fact' => $ob->getNumFacture(),
                    'virement_tiers' => $ob->getNomTiers(),
                    'virement_beneficiaire' => $ob->getBeneficiaire(),
                    'virement_commentaire' => $ob->getCommentaire1(),
                    'virement_montant' => $ob->getMontant(),
                    'virement_type_tiers' => ($ob->getBanqueType() === null) ? '' : $ob->getBanqueType()->getLibelle(),
                    'virement_compte' => $compte ,
                    'virement_action' =>  '<i class="fa fa-save icon-action virement-save" title="Enregistrer"></i><i class="fa fa-trash icon-action virement-delete" title="Supprimer"></i>'
                ]
            ];
        }

        return new JsonResponse([
            'rows' => $rows,
            'userdata' => ['virement_montant' => $total]
        ]);
    }

    public function virementLigneEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        $lid = $request->request->get('id');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null) {
            $dossier = $image->getLot()
                ->getDossier();
        }

        $dateVirement = \DateTime::createFromFormat('d/m/Y', $request->request->get('virement_date')) ;// ;

        if($dateVirement === false)
            $dateVirement = null;

        $nomTiers = $request->request->get('virement_tiers');
        $num = $request->request->get('virement_num');
        $numFact = $request->request->get('virement_num_fact');
        $montant = $request->request->get('virement_montant');
        $banqueTypeId = $request->request->get('virement_type_tiers');

        $beneficiaire = $request->request->get('virement_beneficiaire');
        $commentaire1 = $request->request->get('virement_commentaire');

        if($beneficiaire === ''){
            $beneficiaire = null;
        }

        if($commentaire1 === ''){
            $commentaire1 = null;
        }

        if($num === ''){
            $num = null;
        }

        if($numFact === ''){
            $numFact = null;
        }

        $banqueType = null;
        if($banqueTypeId !== '') {
            $banqueType = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueType')
                ->find($banqueTypeId);
        }

        $compteBilanId = $request->request->get('virement_compte');
        $compte = null;
        $compteBilan = null;
        $compteResultat = null;
        $isBilan = true;

        $tiers = null;
        if($compteBilanId !== ''){
            if(strpos($compteBilanId, '0-') !== false) {
                $compteBilanId = str_replace('0-', '', $compteBilanId);
                $compte = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteBilanId);

                if($compte !== null){
                    $first = $compte->getCompte()[0];
                    if(intval($first) >= 6){
                        $isBilan = false;
                    }
                }

            }
            elseif(strpos($compteBilanId, '1-') !== false){
                $compteBilanId = str_replace('1-', '', $compteBilanId);
                $tiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Tiers')
                    ->find($compteBilanId);
            }
        }

        if($isBilan) {
            $compteBilan = $compte;
        }
        else{
            $compteResultat = $compte;
        }


        $type_tiers = $this->getDoctrine()
            ->getRepository('AppBundle:TypeTiers')
            ->find(5);

        $em = $this->getDoctrine()->getManager();

        if($tiers === null && $compteBilan === null && $dossier !== null){
            $compteBilan = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getCompteAttente($dossier, 0);
            ;
        }

        if ($lid !== 'new_row') {
            //edition ligne

            $banqueSouscategorieAutre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            $banqueSouscategorieAutre->setDate($dateVirement);
            $banqueSouscategorieAutre->setNomTiers($nomTiers);
            $banqueSouscategorieAutre->setMontant($montant);
            $banqueSouscategorieAutre->setNumVirement($num);
            $banqueSouscategorieAutre->setTypeTiers($type_tiers);
            $banqueSouscategorieAutre->setCompteBilan($compteBilan);
            $banqueSouscategorieAutre->setCompteChg($compteResultat);
            $banqueSouscategorieAutre->setBanqueType($banqueType);
            $banqueSouscategorieAutre->setCompteTiers($tiers);
            $banqueSouscategorieAutre->setNumFacture($numFact);

            $banqueSouscategorieAutre->setBeneficiaire($beneficiaire);
            $banqueSouscategorieAutre->setCommentaire1($commentaire1);

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);

        } else {
            //insertion ligne

            $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

            $banqueSouscategorieAutre->setImage($image);
            $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(6)
            );

            $banqueSouscategorieAutre->setDate($dateVirement);
            $banqueSouscategorieAutre->setNomTiers($nomTiers);
            $banqueSouscategorieAutre->setMontant($montant);
            $banqueSouscategorieAutre->setNumVirement($num);
            $banqueSouscategorieAutre->setTypeTiers($type_tiers);
            $banqueSouscategorieAutre->setCompteBilan($compteBilan);
            $banqueSouscategorieAutre->setCompteChg($compteResultat);
            $banqueSouscategorieAutre->setBanqueType($banqueType);
            $banqueSouscategorieAutre->setCompteTiers($tiers);
            $banqueSouscategorieAutre->setNumFacture($numFact);

            $banqueSouscategorieAutre->setBeneficiaire($beneficiaire);
            $banqueSouscategorieAutre->setCommentaire1($commentaire1);

            $em->persist($banqueSouscategorieAutre);
            $em->flush();

            $em->refresh($banqueSouscategorieAutre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $banqueSouscategorieAutre->getId()]);

        }

    }

    public function virementLignesImportAction(Request $request, $dossierid, $exercice){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $file = $request->files->get('upload');

        if (!is_null($file)) {
            $filename = uniqid() . "." . $file->getClientOriginalExtension();

            $path = $this->get('kernel')->getRootDir() . '/../web/ocr';
            $file->move($path, $filename); // move the file to a path
            $path_file = $path . '/' . $filename;
            $excelObj = $this->get('phpexcel')->createPHPExcelObject($path_file);
            $sheet = $excelObj->getActiveSheet()->toArray(null, true, true, true);


            $imageList = [];
            $em = $this->getDoctrine()->getManager();

            $correspondance = [];

            $typeTiers = $this->getDoctrine()
                ->getRepository('AppBundle:TypeTiers')
                ->find(5);

            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierid);

            $utilisateur = $this->getDoctrine()
                ->getRepository('AppBundle:Utilisateur')
                ->find(3345);

            $categorie = $this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find(16);

            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(6);

            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find(303);

            $sourceImage = $this->getDoctrine()
                ->getRepository('AppBundle:SourceImage')
                ->find(15);

            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find(559);

            $newLot = null;



            $columns = ['IMAGE', 'DATE', 'NUM', 'MONTANT', 'NUM FACT', 'TIERS', 'beneficiaires', 'commentaire 1'];


            $rowId  = 0;
            foreach ($sheet as $i => $row) {

                if($rowId === 0){
                    $columns = Fonction::initColumns($columns, $row);
                    $rowId++;
                    continue;
                }


                    $nomTmp = trim($row[$columns['IMAGE']]);

                    if(array_key_exists($nomTmp, $correspondance)){
                        $nomTmp = $correspondance[$nomTmp];
                    }

                    $images = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->findBy(array('nom' => $nomTmp));


                    $dateVirement = null;
                    if (trim($row[$columns['DATE']]) != '') {
                            $dateVirement =  Fonction::getDateFromExcel($row[$columns['DATE']]);
                    }

//                    if($dateVirement === null)
//                        continue;

                    $montant = $row[$columns['MONTANT']];
                    if($montant !== ''){
                        $montant = $this->floatvalue($montant);
                    }
                    else{
                        $montant = 0;
                    }

                    $num = trim($row[$columns['NUM']]);
                    if($num === ''){
                        $num = null;
                    }

                    $numFact = trim($row[$columns['NUM FACT']]);
                    if($numFact === ''){
                        $numFact = null;
                    }

                    $nomTiers = trim($row[$columns['TIERS']]);
                    if($nomTiers === ''){
                        $nomTiers = null;
                    }

                    $beneficaire = trim($row[$columns['beneficiaires']]);
                    if($beneficaire === ''){
                        $beneficaire = null;
                    }

                    $commentaire = trim($row[$columns['commentaire 1']]);
                    if($commentaire === ''){
                        $commentaire = null;
                    }

                    if (count($images) > 0) {
                        $imageTmp = $images[0];

                        if (!in_array($imageTmp, $imageList)) {
                            $imageList[] = $imageTmp;

                            //fafana daholo aloha ny releve lcr an'ilay image
                            $obs = $this->getDoctrine()
                                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                                ->findBy(array('image' => $imageTmp));

                            foreach ($obs as $ob) {
                                $em->remove($ob);
                            }
                        }

                        $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

                        $banqueSouscategorieAutre->setImage($imageTmp);
                        $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                            ->getRepository('AppBundle:Souscategorie')
                            ->find(6)
                        );

                        $banqueSouscategorieAutre->setDate($dateVirement);
                        $banqueSouscategorieAutre->setNomTiers($nomTiers);
                        $banqueSouscategorieAutre->setMontant($montant);
                        $banqueSouscategorieAutre->setNumVirement($num);
                        $banqueSouscategorieAutre->setTypeTiers($typeTiers);
                        $banqueSouscategorieAutre->setNumFacture($numFact);

                        $banqueSouscategorieAutre->setCommentaire1($commentaire);
                        $banqueSouscategorieAutre->setBeneficiaire($beneficaire);

                        $em->persist($banqueSouscategorieAutre);
                        $em->flush();

                    }

                    else{
                        if($newLot === null) {
                            $newLot = new Lot();
                            $newLot->setDateScan(new \DateTime('now'))
                                ->setDossier($dossier)
                                ->setStatus(4)
                                ->setUtilisateur($utilisateur)
                            ;

                            $em->persist($newLot);
                        }
                        //Créer image
                        $image = new Image();
                        $image->setLot($newLot)
                            ->setExercice($exercice)
                            ->setNumerotationLocal(1)
                            ->setSourceImage($sourceImage)
                            ->setRenommer(1)
                            ->setDownload(new \DateTime('now'))
                            ->setNbpage(1)
                            ->setOriginale($nomTmp)
                            ->setSaisie1(3)
                            ->setSaisie2(3)
                            ->setCtrlSaisie(3)
                            ->setImputation(3)
                            ->setCtrlImputation(3);

                        $em->persist($image);

                        //Créer separation
                        $separation = new Separation();
                        $separation->setImage($image)
                            ->setCategorie($categorie)
                            ->setSouscategorie($souscategorie)
                            ->setSoussouscategorie($soussouscategorie)
                            ->setOperateur($operateur);

                        $em->persist($separation);

                        //image a traiter
                        $imageATraiter = new ImageATraiter();
                        $imageATraiter->setImage($image)
                            ->setSaisie1(2)
                            ->setSaisie2(2)
                            ->setDecouper(0)
                            ->setStatus(10);

                        $em->persist($imageATraiter);

                        $imageTmp = $image;

                        if (!in_array($imageTmp, $imageList)) {
                            $imageList[] = $imageTmp;
                        }

                        $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

                        $banqueSouscategorieAutre->setImage($imageTmp);
                        $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                            ->getRepository('AppBundle:Souscategorie')
                            ->find(6)
                        );

                        $banqueSouscategorieAutre->setDate($dateVirement);
                        $banqueSouscategorieAutre->setNomTiers($nomTiers);
                        $banqueSouscategorieAutre->setMontant($montant);
                        $banqueSouscategorieAutre->setNumVirement($num);
                        $banqueSouscategorieAutre->setTypeTiers($typeTiers);
                        $banqueSouscategorieAutre->setNumFacture($numFact);

                        $banqueSouscategorieAutre->setCommentaire1($commentaire);
                        $banqueSouscategorieAutre->setBeneficiaire($beneficaire);

                        $em->persist($banqueSouscategorieAutre);

                          $em->flush();


                        $em->refresh($image);

                        $correspondance [$nomTmp]=$image->getNom();
                    }

            }
        }
        return new JsonResponse(['type' => 'success', 'message' => 'importation effectuée']);
    }


    public function remiseLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;

        if($image!== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);
        }

        $rows = [];

        $em = $this->getDoctrine()
            ->getManager();

        $total = 0;
        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){
            $total += $ob->getMontant();

            $compte = '';

            if($ob->getCompteBilan() !== null) {
                $compte = $ob->getCompteBilan()->getCompte() . ' - ' . $ob->getCompteBilan()->getIntitule();
            }
            elseif($ob->getCompteTiers() !== null){
                $compte = $ob->getCompteTiers()->getCompteStr() . ' - ' . $ob->getCompteTiers()->getIntitule();
            }


            if($compte === '' && $dossier !== null){
                /** @var Pcc $attente */
                $attente = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getCompteAttente($dossier, 1);

                if($attente !== null){
                    $ob->setCompteBilan($attente);
                    $em->flush();
                    $compte = $attente->getCompte(). ' - '.$attente->getIntitule();
                }
            }


            $rows []= [
                'id' => $ob->getId(),
                'cell' => [
                    'remise_num' => $ob->getNumRemise(),
                    'remise_cheque_num' => $ob->getNumCheque(),
                    'remise_tiers' => $ob->getNomTiers(),
                    'remise_libelle' => $ob->getLibelle(),
                    'remise_compte' => $compte,
                    'remise_montant' => $ob->getMontant(),
                    'remise_action' =>  '<i class="fa fa-save icon-action remise-save" title="Enregistrer"></i><i class="fa fa-trash icon-action remise-delete" title="Supprimer"></i>'
                ]
            ];
        }

        return new JsonResponse([
            'rows' => $rows,
            'userdata' => ['remise_montant' => $total]
        ]);
    }

    public function remiseLigneEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        $lid = $request->request->get('id');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $numremise = $request->request->get('remise_num');
        $numcheque = $request->request->get('remise_cheque_num');
        $nomTiers = $request->request->get('remise_tiers');
        $libelle = $request->request->get('remise_libelle');



        $typeTiers = $this->getDoctrine()
            ->getRepository('AppBundle:TypeTiers')
            ->find(11);


        $compteId = $request->request->get('remise_compte');
        $tiers = null;
        $pcc = null;

        if($compteId !== '') {
            $isTiers = false;

            if (strpos($compteId,'1-') !== false) {
                $compteId = str_replace('1-', '', $compteId);
                $isTiers= true;
            } else {
                $compteId = str_replace('0-', '', $compteId);
            }

            if ($isTiers) {
                $tiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Tiers')
                    ->find($compteId);
            } else {
                $pcc = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteId);
            }
        }

        if($pcc === null && $tiers === null && $dossier !== null){
            $pcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getCompteAttente($dossier, 1);
            ;
        }

        $montant = $request->request->get('remise_montant');

        $em = $this->getDoctrine()->getManager();

        if ($lid !== 'new_row') {
            //edition ligne

            $banqueSouscategorieAutre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            $banqueSouscategorieAutre->setNumRemise($numremise);
            $banqueSouscategorieAutre->setNumCheque($numcheque);
            $banqueSouscategorieAutre->setNomTiers($nomTiers);
            $banqueSouscategorieAutre->setTypeTiers($typeTiers);
            $banqueSouscategorieAutre->setLibelle($libelle);
            $banqueSouscategorieAutre->setCompteTiers($tiers);
            $banqueSouscategorieAutre->setCompteBilan($pcc);
            $banqueSouscategorieAutre->setMontant($montant);

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);

        } else {
            //insertion ligne

            $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

            $banqueSouscategorieAutre->setImage($image);
            $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(7)
            );

            $banqueSouscategorieAutre->setNumRemise($numremise);
            $banqueSouscategorieAutre->setNumCheque($numcheque);
            $banqueSouscategorieAutre->setNomTiers($nomTiers);
            $banqueSouscategorieAutre->setTypeTiers($typeTiers);
            $banqueSouscategorieAutre->setLibelle($libelle);
            $banqueSouscategorieAutre->setCompteTiers($tiers);
            $banqueSouscategorieAutre->setCompteBilan($pcc);
            $banqueSouscategorieAutre->setMontant($montant);

            $em->persist($banqueSouscategorieAutre);
            $em->flush();

            $em->refresh($banqueSouscategorieAutre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $banqueSouscategorieAutre->getId()]);

        }

    }

    public function remiseLignesImportAction(Request $request, $dossierid, $exercice){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $file = $request->files->get('upload');

        if (!is_null($file)) {
            $filename = uniqid() . "." . $file->getClientOriginalExtension();

            $path = $this->get('kernel')->getRootDir() . '/../web/ocr';
            $file->move($path, $filename); // move the file to a path
            $path_file = $path . '/' . $filename;
            $excelObj = $this->get('phpexcel')->createPHPExcelObject($path_file);
            $sheet = $excelObj->getActiveSheet()->toArray(null, true, true, true);


            $imageList = [];
            $em = $this->getDoctrine()->getManager();

            $correspondance = [];

            $typeTiers = $this->getDoctrine()
                ->getRepository('AppBundle:TypeTiers')
                ->find(11);

            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierid);

            $utilisateur = $this->getDoctrine()
                ->getRepository('AppBundle:Utilisateur')
                ->find(3345);

            $categorie = $this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find(16);

            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(7);

            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find(12);

            $sourceImage = $this->getDoctrine()
                ->getRepository('AppBundle:SourceImage')
                ->find(15);

            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find(559);

            $newLot = null;



            $columns = ['IMAGE', 'N REMISE', 'N CHEQUE', 'LIBELLE', 'TIERS', 'MONTANT'];


            $rowId  = 0;
            foreach ($sheet as $i => $row) {

                if($rowId === 0){
                    $columns = Fonction::initColumns($columns, $row);
                    $rowId++;
                    continue;
                }

                $nomTmp = trim($row[$columns['IMAGE']]);

                if(array_key_exists($nomTmp, $correspondance)){
                    $nomTmp = $correspondance[$nomTmp];
                }

                $images = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->findBy(array('nom' => $nomTmp));


                $numremise =  trim($row[$columns['N REMISE']]);;
                if ($numremise == '') {
                    $numremise = null;
                }

                $montant = $row[$columns['MONTANT']];
                if($montant !== ''){
                    $montant = $this->floatvalue($montant);
                }
                else{
                    $montant = 0;
                }

                $numcheque = trim($row[$columns['N CHEQUE']]);
                if($numcheque === ''){
                    $numcheque = null;
                }

                $nomTiers = trim($row[$columns['TIERS']]);
                if($nomTiers === ''){
                    $nomTiers = null;
                }

                $libelle = trim($row[$columns['LIBELLE']]);
                if($libelle === ''){
                    $libelle = null;
                }


                if (count($images) > 0) {
                    $imageTmp = $images[0];

                    if (!in_array($imageTmp, $imageList)) {
                        $imageList[] = $imageTmp;

                        //fafana daholo aloha ny releve lcr an'ilay image
                        $obs = $this->getDoctrine()
                            ->getRepository('AppBundle:BanqueSousCategorieAutre')
                            ->findBy(array('image' => $imageTmp));

                        foreach ($obs as $ob) {
                            $em->remove($ob);
                        }
                    }

                    $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

                    $banqueSouscategorieAutre->setImage($imageTmp);
                    $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                        ->getRepository('AppBundle:Souscategorie')
                        ->find(7)
                    );

                    $banqueSouscategorieAutre->setNumRemise($numremise);
                    $banqueSouscategorieAutre->setNumCheque($numcheque);
                    $banqueSouscategorieAutre->setNomTiers($nomTiers);
                    $banqueSouscategorieAutre->setTypeTiers($typeTiers);
                    $banqueSouscategorieAutre->setLibelle($libelle);
                    $banqueSouscategorieAutre->setMontant($montant);

                    $em->persist($banqueSouscategorieAutre);
                    $em->flush();

                }

                else{
                    if($newLot === null) {
                        $newLot = new Lot();
                        $newLot->setDateScan(new \DateTime('now'))
                            ->setDossier($dossier)
                            ->setStatus(4)
                            ->setUtilisateur($utilisateur)
                        ;

                        $em->persist($newLot);
                    }
                    //Créer image
                    $image = new Image();
                    $image->setLot($newLot)
                        ->setExercice($exercice)
                        ->setNumerotationLocal(1)
                        ->setSourceImage($sourceImage)
                        ->setRenommer(1)
                        ->setDownload(new \DateTime('now'))
                        ->setNbpage(1)
                        ->setOriginale($nomTmp)
                        ->setSaisie1(3)
                        ->setSaisie2(3)
                        ->setCtrlSaisie(3)
                        ->setImputation(3)
                        ->setCtrlImputation(3);

                    $em->persist($image);

                    //Créer separation
                    $separation = new Separation();
                    $separation->setImage($image)
                        ->setCategorie($categorie)
                        ->setSouscategorie($souscategorie)
                        ->setSoussouscategorie($soussouscategorie)
                        ->setOperateur($operateur);

                    $em->persist($separation);

                    //image a traiter
                    $imageATraiter = new ImageATraiter();
                    $imageATraiter->setImage($image)
                        ->setSaisie1(2)
                        ->setSaisie2(2)
                        ->setDecouper(0)
                        ->setStatus(10);

                    $em->persist($imageATraiter);

                    $imageTmp = $image;

                    if (!in_array($imageTmp, $imageList)) {
                        $imageList[] = $imageTmp;
                    }

                    $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

                    $banqueSouscategorieAutre->setImage($imageTmp);
                    $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                        ->getRepository('AppBundle:Souscategorie')
                        ->find(7)
                    );

                    $banqueSouscategorieAutre->setNumRemise($numremise);
                    $banqueSouscategorieAutre->setNumCheque($numcheque);
                    $banqueSouscategorieAutre->setNomTiers($nomTiers);
                    $banqueSouscategorieAutre->setTypeTiers($typeTiers);
                    $banqueSouscategorieAutre->setLibelle($libelle);
                    $banqueSouscategorieAutre->setMontant($montant);

                    $em->persist($banqueSouscategorieAutre);

                    $em->flush();


                    $em->refresh($image);

                    $correspondance [$nomTmp]=$image->getNom();
                }

            }
        }
        return new JsonResponse(['type' => 'success', 'message' => 'importation effectuée']);
    }

    public function fraisLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);
        }

        $rows = [];
        $totalHt = 0;
        $totalTtc = 0;
        $totalTva = 0;
        $totalCom = 0;

        $em = $this->getDoctrine()
            ->getManager();

        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){

            $totalHt += $ob->getMontantHt();
            $totalTtc += $ob->getMontant();
            $totalTva += $ob->getMontantTva();
            $totalCom += $ob->getMontantCom();

            $compteBilan = '';
            $compteResultat = '';
            $compteTva = '';
            if($ob->getCompteBilan() !== null){
                $compteBilan = $ob->getCompteBilan()->getCompte().' - '.$ob->getCompteBilan()->getIntitule();
            }

            if($ob->getCompteChg() !== null) {
                $compteResultat = $ob->getCompteChg()->getCompte() . ' - ' . $ob->getCompteChg()->getIntitule();
            }

            if($ob->getCompteTva() !== null){
                $compteTva = $ob->getCompteTva()->getCompte().' - '.$ob->getCompteTva()->getIntitule();
            }


            if($compteBilan === '' && $compteResultat === '' && $dossier !== null){
                /** @var Pcc $attente */
                $attente = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getCompteAttente($dossier, 0);

                if($attente !== null){
                    $ob->setCompteBilan($attente);
                    $em->flush();
                    $compteBilan = $attente->getCompte(). ' - '.$attente->getIntitule();
                }
            }

            $rows []= [
                'id' => $ob->getId(),
                'cell' => [
                    'frais_date' => ($ob->getDate() === null) ? '' : $ob->getDate()->format('y-m-d'),
                    'frais_libelle' => $ob->getLibelle(),
                    'frais_taux' => ($ob->getTvaTaux() === null) ? '' : $ob->getTvaTaux()->getTaux(),
                    'frais_montant_ht' => $ob->getMontantHt(),
                    'frais_montant_tva' => $ob->getMontantTva(),
                    'frais_montant_ttc' => $ob->getMontant(),
                    'frais_montant_com' => $ob->getMontantCom(),
                    'frais_compte_bilan' => $compteBilan,
                    'frais_compte_tva' => $compteTva,
                    'frais_compte_resultat' => $compteResultat,
                    'frais_action' =>  '<i class="fa fa-save icon-action frais-save" title="Enregistrer"></i><i class="fa fa-trash icon-action frais-delete" title="Supprimer"></i>'
                ]
            ];
        }

        return new JsonResponse([
            'rows' => $rows,
            'userdata' => [
                'frais_montant_ht' => $totalHt,
                'frais_montant_tva' => $totalTva,
                'frais_montant_ttc' => $totalTtc,
                'frais_montant_com' => $totalCom
            ]
        ]);
    }

    public function fraisLigneEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        $lid = $request->request->get('id');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $dateFrais = $request->request->get('frais_date');
        if($dateFrais !== null && $dateFrais !== ''){
            $dateFrais = \DateTime::createFromFormat('d/m/Y', $dateFrais);
        }
        $libelle = $request->request->get('frais_libelle');
        $tvaTaux = null;
        $tvaTauxId = $request->request->get('frais_taux');
        if($tvaTauxId !== null && $tvaTauxId !== '') {
            $tvaTaux = $this->getDoctrine()
                ->getRepository('AppBundle:TvaTaux')
                ->find($tvaTauxId);
        }

        $montantHt = $this->floatvalue($request->request->get('frais_montant_ht'));
        $montantTva = $this->floatvalue($request->request->get('frais_montant_tva'));
        $montantTtc = $this->floatvalue($request->request->get('frais_montant_ttc'));
        $montantCom = $this->floatvalue($request->request->get('frais_montant_com'));

        $compteBilanId = $request->request->get('frais_compte_bilan');
        $compteResultatId = $request->request->get('frais_compte_resultat');
        $compteTvaId = $request->request->get('frais_compte_tva');

        $compte = null;
        $compteBilan = null;
        $compteResultat = null;
        $compteTva = null;


        if($compteBilanId !== null && $compteBilanId !== ''){

            if(strpos($compteBilanId, '0-') !== false) {
                $compteBilanId = str_replace('0-', '', $compteBilanId);

                $compteBilan = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteBilanId);

            }
        }

        if($compteResultatId!== null && $compteResultatId !== ''){

            if(strpos($compteResultatId, '0-') !== false) {
                $compteResultatId = str_replace('0-', '', $compteResultatId);

                $compteResultat = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteResultatId);

            }
        }


        if($compteTvaId !== null && $compteTvaId  !== ''){

            if(strpos($compteTvaId , '0-') !== false) {
                $compteTvaId  = str_replace('0-', '', $compteTvaId );

                $compteTva = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteTvaId );

            }
        }

        if($compteBilan === null && $compteResultat === null && $dossier !== null){
            $compteBilan = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getCompteAttente($dossier, 0);
            ;
        }


        $typeTiers = $this->getDoctrine()
            ->getRepository('AppBundle:TypeTiers')
            ->find(3);

        $em = $this->getDoctrine()->getManager();

        if ($lid !== 'new_row') {
            //edition ligne
            $banqueSouscategorieAutre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            $banqueSouscategorieAutre->setDate($dateFrais);
            $banqueSouscategorieAutre->setLibelle($libelle);
            $banqueSouscategorieAutre->setTvaTaux($tvaTaux);
            $banqueSouscategorieAutre->setMontant($montantTtc);
            $banqueSouscategorieAutre->setMontantHt($montantHt);
            $banqueSouscategorieAutre->setMontantTva($montantTva);
            $banqueSouscategorieAutre->setCompteBilan($compteBilan);
            $banqueSouscategorieAutre->setCompteChg($compteResultat);
            $banqueSouscategorieAutre->setCompteTva($compteTva);
            $banqueSouscategorieAutre->setMontantCom($montantCom);

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);

        } else {
            //insertion ligne

            $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

            $banqueSouscategorieAutre->setImage($image);
            $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(8)
            );

            $banqueSouscategorieAutre->setDate($dateFrais);
            $banqueSouscategorieAutre->setLibelle($libelle);
            $banqueSouscategorieAutre->setTvaTaux($tvaTaux);
            $banqueSouscategorieAutre->setMontant($montantTtc);
            $banqueSouscategorieAutre->setMontantHt($montantHt);
            $banqueSouscategorieAutre->setMontantTva($montantTva);
            $banqueSouscategorieAutre->setTypeTiers($typeTiers);
            $banqueSouscategorieAutre->setCompteBilan($compteBilan);
            $banqueSouscategorieAutre->setCompteChg($compteResultat);
            $banqueSouscategorieAutre->setCompteTva($compteTva);
            $banqueSouscategorieAutre->setMontantCom($montantCom);

            $em->persist($banqueSouscategorieAutre);
            $em->flush();

            $em->refresh($banqueSouscategorieAutre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $banqueSouscategorieAutre->getId()]);

        }

    }

    public function carteCreditReleveLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;

        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);
        }

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        $em = $this->getDoctrine()
            ->getManager();

        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){

             if($ob->getSens() === 1){
                $totalDebit += $ob->getMontant();
            }
            elseif($ob->getSens() === 0){
                $totalCredit +=$ob->getMontant();
            }

            $compteBilan = '';
            $compteCharge = '';
            $compteTva = '';

            if($ob->getCompteBilan() !== null){
                $compteBilan = $ob->getCompteBilan()->getCompte(). ' - '.$ob->getCompteBilan()->getIntitule();
            }
            elseif($ob->getCompteTiers() !== null) {
                $compteBilan = $ob->getCompteTiers()->getCompteStr() . ' - ' . $ob->getCompteTiers()->getIntitule();
            }

            if($ob->getCompteChg() !== null){
                $compteCharge = $ob->getCompteChg()->getCompte(). ' - ' . $ob->getCompteChg()->getIntitule();
            }

            if($ob->getCompteTva() !== null){
                $compteTva = $ob->getCompteTva()->getCompte(). ' - ' .$ob->getCompteTva()->getIntitule();
            }

            if($compteCharge === '' && $compteBilan === '' && $dossier !== null){
                /** @var Pcc $attente */
                $attente = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getCompteAttente($dossier, 0);

                if($attente !== null){
                    $ob->setCompteBilan($attente);
                    $em->flush();
                    $compteBilan = $attente->getCompte(). ' - '.$attente->getIntitule();
                }
            }

            $rows []= [
                'id' => $ob->getId(),
                'cell' => [
                    'ccreleve_date' => ($ob->getDate() === null) ? '' : $ob->getDate()->format('y-m-d'),
                    'ccreleve_libelle' => $ob->getLibelle(),
                    'ccreleve_tiers' => $ob->getNomTiers(),
                    'ccreleve_codepos' => $ob->getCodePostal(),
                    'ccreleve_nature' => ($ob->getSoussouscategorie() === null)? '' : $ob->getSoussouscategorie()->getLibelleNew(),
                    'ccreleve_compte_bilan' => $compteBilan,
                    'ccreleve_compte_resultat' => $compteCharge,
                    'ccreleve_compte_tva' => $compteTva,
                    'ccreleve_debit' => ($ob->getSens() === 1) ? $ob->getMontant() : 0,
                    'ccreleve_credit' => ($ob->getSens() === 1) ? 0 : $ob->getMontant(),
                    'ccreleve_tva' => $ob->getMontantTva(),
                    'ccreleve_com' => $ob->getMontantCom(),
                    'ccreleve_action' =>  '<i class="fa fa-save icon-action ccreleve-save" title="Enregistrer"></i><i class="fa fa-trash icon-action ccreleve-delete" title="Supprimer"></i>'
                ]
            ];
        }

        return new JsonResponse([
            'rows' => $rows,
            'userdata' => ['ccreleve_debit' => $totalDebit, 'ccreleve_credit' => $totalCredit]
        ]);
    }

    public function carteCreditReleveLignesEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        $lid = $request->request->get('id');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = null;
        if($image !== null){
            $dossier = $image->getLot()
                ->getDossier();
        }


        $dateReleve = $request->request->get('ccreleve_date');
        if($dateReleve !== null && $dateReleve !== ''){
            $dateReleve = \DateTime::createFromFormat('d/m/Y', $dateReleve);
        }

        $libelle = $request->request->get('ccreleve_libelle');

        $montantDebit = $this->floatvalue($request->request->get('ccreleve_debit'));
        $montantCredit = $this->floatvalue($request->request->get('ccreleve_credit'));

        $montantCom = $this->floatvalue($request->request->get('ccreleve_com'));
        $montantTva = $this->floatvalue($request->request->get('ccreleve_tva'));

        $montant = $montantCredit;
        $sens = 0;
        if(floatval($montantDebit) != 0 ){
            $sens  = 1;
            $montant = $montantDebit;
        }

        //Fournisseur
        $typeTiers = $this->getDoctrine()
            ->getRepository('AppBundle:TypeTiers')
            ->find(11);

        $nomTiers = $request->request->get('ccreleve_tiers');
        if(str_replace(' ','',$nomTiers) === ''){
            $nomTiers = null;
        }
        $codePos = $request->request->get('ccreleve_codepos');
        if(str_replace(' ','', $codePos) === ''){
            $codePos = null;
        }

        $nature = $request->request->get('ccreleve_nature');
        $soussouscategorie = null;
        if($nature !== '' && $nature !== null){
            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find($nature);
        }

        $compteBilanId = $request->request->get('ccreleve_compte_bilan');
        $compteBilan = null;
        $compteTiers = null;

        if($compteBilanId !== '' && $compteBilanId!== null){
            if(strpos($compteBilanId, '0-') !== false){
                $compteBilanId = str_replace('0-','', $compteBilanId);
                $compteBilan = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteBilanId);
            }
            else{
                $compteBilanId = str_replace('1-', '', $compteBilanId);
                $compteTiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Tiers')
                    ->find($compteBilanId);
            }
        }

        $compteChargeId = $request->request->get('ccreleve_compte_resultat');
        $compteCharge = null;

        if($compteChargeId !== '' && $compteChargeId!== null){
            if(strpos($compteChargeId, '0-') !== false){
                $compteChargeId = str_replace('0-','', $compteChargeId);
                $compteCharge = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteChargeId);
            }

        }


        $compteTvaId = $request->request->get('ccreleve_compte_tva');
        $compteTva = null;

        if($compteTvaId !== '' && $compteTvaId!== null){
            if(strpos($compteTvaId, '0-') !== false){
                $compteTvaId = str_replace('0-','', $compteTvaId);
                $compteTva = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($compteTvaId);
            }
        }


        if($compteBilan=== null && $compteTiers === null && $dossier !== null){
            $compteBilan = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getCompteAttente($dossier, 0);
            ;
        }


        $em = $this->getDoctrine()->getManager();

        if ($lid !== 'new_row') {
            //edition ligne
            $banqueSouscategorieAutre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            $banqueSouscategorieAutre->setDate($dateReleve);
            $banqueSouscategorieAutre->setLibelle($libelle);
            $banqueSouscategorieAutre->setMontant($montant);
            $banqueSouscategorieAutre->setMontantCom($montantCom);
            $banqueSouscategorieAutre->setMontantTva($montantTva);
            $banqueSouscategorieAutre->setSens($sens);
            $banqueSouscategorieAutre->setNomTiers($nomTiers);
            $banqueSouscategorieAutre->setCompteBilan($compteBilan);
            $banqueSouscategorieAutre->setCompteTiers($compteTiers);
            $banqueSouscategorieAutre->setCompteTva($compteTva);
            $banqueSouscategorieAutre->setCompteChg($compteCharge);
            $banqueSouscategorieAutre->setCodePostal($codePos);
            $banqueSouscategorieAutre->setSoussouscategorie($soussouscategorie);

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);

        } else {
            //insertion ligne

            $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

            $banqueSouscategorieAutre->setImage($image);
            $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(1)
            );


            $banqueSouscategorieAutre->setDate($dateReleve);
            $banqueSouscategorieAutre->setLibelle($libelle);
            $banqueSouscategorieAutre->setMontant($montant);
            $banqueSouscategorieAutre->setMontantCom($montantCom);
            $banqueSouscategorieAutre->setMontantTva($montantTva);
            $banqueSouscategorieAutre->setSens($sens);
            $banqueSouscategorieAutre->setTypeTiers($typeTiers);
            $banqueSouscategorieAutre->setNomTiers($nomTiers);
            $banqueSouscategorieAutre->setCompteBilan($compteBilan);
            $banqueSouscategorieAutre->setCompteTiers($compteTiers);
            $banqueSouscategorieAutre->setCompteTva($compteTva);
            $banqueSouscategorieAutre->setCompteChg($compteCharge);
            $banqueSouscategorieAutre->setCodePostal($codePos);
            $banqueSouscategorieAutre->setSoussouscategorie($soussouscategorie);

            $em->persist($banqueSouscategorieAutre);
            $em->flush();

            $em->refresh($banqueSouscategorieAutre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $banqueSouscategorieAutre->getId()]);

        }

    }


    public function carteCreditReleveLignesImportAction(Request $request){
        $file = $request->files->get('upload');

        if (!is_null($file)) {
            $filename = uniqid() . "." . $file->getClientOriginalExtension();

            $path = $this->get('kernel')->getRootDir() . '/../web/ocr';
            $file->move($path, $filename); // move the file to a path
            $path_file = $path . '/' . $filename;
            $excelObj = $this->get('phpexcel')->createPHPExcelObject($path_file);
            $sheet = $excelObj->getActiveSheet()->toArray(null, true, true, true);


            $imageList = [];
            $em = $this->getDoctrine()->getManager();

            foreach ($sheet as $i => $row) {
                if (strlen(trim($row['A'])) > 7) {

                    $nomTmp = trim($row['A']);
                    $images = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->findBy(array('nom' => $nomTmp));

                    if (count($images) > 0) {
                        $imageTmp = $images[0];

                        $dateReleve = null;

                        if (trim($row['B']) != '') {

                            if(strpos($row['B'], '/') !== false) {
                                $datetmp = explode('/', $row['B']);
                                $day = $datetmp[1];
                                $month = $datetmp[0];
                                $year = $datetmp[2];

                                $dateReleve = \DateTime::createFromFormat('d/m/Y', $day . '/' . $month . '/' . $year);

                            }
                            else{
                                $dateReleve = \PHPExcel_Shared_Date::ExcelToPHPObject($row['B']);
                            }


                        } else {
                            continue;
                        }

                        $libelle = $row['C'];


                        $montantDebit = $this->floatvalue($row['F']);
                        $montantCredit = $this->floatvalue($row['G']);

                        $nomTiers = $row['D'];
                        if($nomTiers === ''){
                            $nomTiers = null;
                        }

                        $codePostal = $row['E'];
                        if($codePostal === ''){
                            $codePostal = null;
                        }


                        $montant = $montantCredit;
                        $sens = 0;

                        if(floatval($montantDebit) != 0 ){
                            $sens  = 1;
                            $montant = $montantDebit;
                        }


                        if (!in_array($imageTmp, $imageList)) {
                            $imageList[] = $imageTmp;

                            //fafana daholo aloha ny ob an'ilay image
                            $obs = $this->getDoctrine()
                                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                                ->findBy(array('image' => $imageTmp));

                            foreach ($obs as $ob) {
                                $em->remove($ob);

                            }
                        }

                        $typeTiers = $this->getDoctrine()
                            ->getRepository('AppBundle:TypeTiers')
                            ->find(11);

                        $autre = new BanqueSousCategorieAutre();

                        $autre->setImage($imageTmp);
                        $autre->setSousCategorie($this->getDoctrine()
                            ->getRepository('AppBundle:Souscategorie')
                            ->find(1)
                        );


                        $autre->setDate($dateReleve);
                        $autre->setLibelle($libelle);
                        $autre->setMontant($montant);
                        $autre->setSens($sens);
                        $autre->setTypeTiers($typeTiers);
                        $autre->setNomTiers($nomTiers);
                        $autre->setCodePostal($codePostal);

                        $em->persist($autre);

                        $em->flush();
                    }
                    continue;
                }
            }
        }
        return new JsonResponse(['type' => 'success', 'message' => 'importation effectuée']);
    }

    public function carteDebitLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);
        }

        $rows = [];
        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){
            $rows []= [
                'id' => $ob->getId(),
                'cell' => [
                    'cdebit_date' => ($ob->getDate() === null) ? '' : $ob->getDate()->format('y-m-d'),
                    'cdebit_client' => $ob->getNomTiers(),
                    'cdebit_action' =>  '<i class="fa fa-save icon-action cdebit-save" title="Enregistrer"></i><i class="fa fa-trash icon-action cdebit-delete" title="Supprimer"></i>'
                ]
            ];
        }

        return new JsonResponse(['rows' => $rows]);
    }

    public function carteDebitLignesEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        $lid = $request->request->get('id');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);


        $dateDebit = $request->request->get('cdebit_date');
        if($dateDebit !== null && $dateDebit !== ''){
            $dateDebit = \DateTime::createFromFormat('d/m/Y', $dateDebit);
        }

        $client = $request->request->get('cdebit_client');


        //Client
        $typeTiers = $this->getDoctrine()
            ->getRepository('AppBundle:TypeTiers')
            ->find(5);

        $em = $this->getDoctrine()->getManager();

        if ($lid !== 'new_row') {
            //edition ligne
            $banqueSouscategorieAutre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            $banqueSouscategorieAutre->setDate($dateDebit);
            $banqueSouscategorieAutre->setNomTiers($client);

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);

        } else {
            //insertion ligne
            $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

            $banqueSouscategorieAutre->setImage($image);
            $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(1)
            );

            $banqueSouscategorieAutre->setDate($dateDebit);
            $banqueSouscategorieAutre->setTypeTiers($typeTiers);
            $banqueSouscategorieAutre->setNomTiers($client);

            $em->persist($banqueSouscategorieAutre);
            $em->flush();

            $em->refresh($banqueSouscategorieAutre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $banqueSouscategorieAutre->getId()]);

        }

    }


    public function carteCreditLignesAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $obs = [];
        if($image !== null) {
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByImage($image);
        }

        $rows = [];
        /** @var BanqueSousCategorieAutre $ob */
        foreach ($obs as $ob){
            $rows []= [
                'id' => $ob->getId(),
                'cell' => [
                    'ccredit_date' => ($ob->getDate() === null) ? '' : $ob->getDate()->format('y-m-d'),
                    'ccredit_fournisseur' => $ob->getNomTiers(),
                    'ccredit_nature' => ($ob->getSoussouscategorie() === null) ? '' : $ob->getSoussouscategorie()->getLibelleNew(),
                    'ccredit_numcb' => $ob->getNumCb(),
                    'ccredit_montant' => $ob->getMontant(),
                    'ccredit_action' =>  '<i class="fa fa-save icon-action ccredit-save" title="Enregistrer"></i><i class="fa fa-trash icon-action ccredit-delete" title="Supprimer"></i>'
                ]
            ];
        }

        return new JsonResponse(['rows' => $rows]);
    }

    public function carteCreditLignesEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        $lid = $request->request->get('id');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);


        $dateCredit = $request->request->get('ccredit_date');
        if($dateCredit !== null && $dateCredit !== ''){
            $dateCredit = \DateTime::createFromFormat('d/m/Y', $dateCredit);
        }

        $fournisseur = $request->request->get('ccredit_fournisseur');

        $nature = $request->request->get('ccredit_nature');
        $soussouscategorie = null;

        if($nature !== ''){
            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find($nature);
        }

        $montant = $request->request->get('ccredit_montant');
        $numcb = $request->request->get('ccredit_numcb');


        //Fournisseur
        $typeTiers = $this->getDoctrine()
            ->getRepository('AppBundle:TypeTiers')
            ->find(11);

        $em = $this->getDoctrine()->getManager();

        if ($lid !== 'new_row') {
            //edition ligne
            $banqueSouscategorieAutre = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($lid);

            $banqueSouscategorieAutre->setDate($dateCredit);
            $banqueSouscategorieAutre->setNomTiers($fournisseur);
            $banqueSouscategorieAutre->setSoussouscategorie($soussouscategorie);
            $banqueSouscategorieAutre->setNumCb($numcb);
            $banqueSouscategorieAutre->setMontant($montant);

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);

        } else {
            //insertion ligne
            $banqueSouscategorieAutre = new BanqueSousCategorieAutre();

            $banqueSouscategorieAutre->setImage($image);
            $banqueSouscategorieAutre->setSousCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find(1)
            );

            $banqueSouscategorieAutre->setDate($dateCredit);
            $banqueSouscategorieAutre->setTypeTiers($typeTiers);
            $banqueSouscategorieAutre->setNomTiers($fournisseur);
            $banqueSouscategorieAutre->setSoussouscategorie($soussouscategorie);
            $banqueSouscategorieAutre->setNumCb($numcb);
            $banqueSouscategorieAutre->setMontant($montant);

            $em->persist($banqueSouscategorieAutre);
            $em->flush();

            $em->refresh($banqueSouscategorieAutre);
            return new JsonResponse(['type' => 'success', 'message' => 'Insertion effectuée', 'id' => $banqueSouscategorieAutre->getId()]);

        }

    }


    public function verifMontantAction(Request $request)
    {
        $datePiece = \DateTime::createFromFormat('d/m/Y', $request->query->get('datepiece'));

        if($datePiece === false){
            $datePiece = \DateTime::createFromFormat('m/Y', $request->query->get('datepiece'));
            if($datePiece !== false) {
                $datePiece->setDate($datePiece->format('Y'), $datePiece->format('m'), 1);
            }
        }

        if($datePiece === false) {
            $datePiece = null;
        }
        else{
            $datePiece->setTime(0,0,0);
        }

        $montant = $request->query->get('total');

        $banquecompteid = $request->query->get('banquecompteid');

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);

        $imageid = $request->query->get('imageid');

        $now = new \DateTime('now');
        $exercice = $now->format('Y');

        if($imageid !== ''){
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageid);

            if($image !== null){
                $exercice = $image->getExercice();
            }
        }

        $releves = [];
        $releveMonths = [];
        $ribIntrouvable = false;

        if($banquecompte !==null) {
            $ribIntrouvable = !$this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->checkRib($banquecompte);

            if($ribIntrouvable === false){
                /** @var Releve[] $releves */
                $releves = $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->checkMonantInReleve($banquecompte, $montant, $exercice, $datePiece);

                $releveMonths = $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->checkMonthReleve($banquecompte, $exercice, $datePiece);
            }

            $em = $this->getDoctrine()
                ->getManager();

            if($ribIntrouvable === false) {
                //A ne pas saisir
                if (count($releves) === 0) {
                    if ($banquecompte->getObASaisir() === null) {
                        $banquecompte->setObASaisir(0);
                        $em->flush();
                    }
                } else {
                    $banquecompte->setObASaisir(1);
                    $em->flush();
                }
            }
        }

        $releveIds  = '';

        foreach ($releves as $releve){
            if($releveIds === ''){
                $releveIds = $releve->getId();
            }
            else{
                $releveIds .=','.$releve->getId();
            }
        }

        return $this->render('@Banque/Banque/saisie/banque_saisie_a_saisir_label.html.twig', [
            'ribIntrouvable' => $ribIntrouvable,
            'banquecompte' => $banquecompte,
            'releveMonths' => $releveMonths,
            'ids' => $releveIds
        ]);
    }

    public function banqueCompteASaisirAction(Request $request){
        $banquecompteid = $request->query
            ->get('banquecompteid');

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);

        $ribIntrouvable = !$this->getDoctrine()
            ->getRepository('AppBundle:Releve')
            ->checkRib($banquecompte);


        return $this->render('@Banque/Banque/saisie/banque_saisie_a_saisir_label.html.twig',
            [
                'ribIntrouvable' => $ribIntrouvable,
                'banquecompte' => $banquecompte
            ]);
    }

    public function souscategorieASaisirAction(Request $request){
        $post = $request->query;

        $souscategorieid = $post->get('souscategorieid');
        $dossierid = $post->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $souscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find($souscategorieid);

        $souscategoriePasSaisir = $this->getDoctrine()
            ->getRepository('AppBundle:SouscategoriePasSaisir')
            ->findBy(['dossier' => $dossier, 'souscategorie' => $souscategorie]);


        return $this->render('@Banque/Banque/saisie/banque_saisie_a_saisir_label.html.twig',
            ['souscategoriePasSaisie' => $souscategoriePasSaisir]);
    }


    public function showDetailReleveAction(Request $request)
    {


        $releveids = $request->query->get('releveids');

        $releveids = str_replace(' ', '', $releveids);

        $relevearr = explode(',', $releveids);

        /** @var Releve[] $releves */
        $releves = [];


        foreach ($relevearr as $releveid) {
            $releves []= $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->find($releveid);
        }

        return $this->render('@Banque/Banque/saisie/banque_saisie_a_saisir_releve.html.twig', ['releves' => $releves]);
    }


    public function countSouscategorieAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw  new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossierid = $get->get('dossierid');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $exercice = $get->get('exercice');

        $countSouscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->countSouscategorie($dossier, $exercice);

        $countSouscategoriesValide = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->countSouscategorie($dossier, $exercice, true);

//        $countChequeSouscategories = $this->getDoctrine()
//            ->getRepository('AppBundle:Separation')
//            ->countSoussouscategorieCheque($dossier, $exercice);

//        $countChequeSouscategoriesValide = $this->getDoctrine()
//            ->getRepository('AppBundle:Separation')
//            ->countSoussouscategorieCheque($dossier, $exercice, true);

//        $nbChequeEmis = 0;
//        $nbChequeEmisValide = 0;
//        foreach ($countChequeSouscategories as $countChequeSouscategory){
//            if($countChequeSouscategory->soussouscategorie_id === 1905){
//                $nbChequeEmis = $countChequeSouscategory->nbre;
//            }
//        }

//        foreach ($countChequeSouscategoriesValide as $countChequeSouscategory){
//            if($countChequeSouscategory->soussouscategorie_id === 1905){
//                $nbChequeEmisValide = $countChequeSouscategory->nbre;
//            }
//        }



        foreach ($countSouscategories as $countSouscategory){
//            if($countSouscategory->souscategorie_id === 6){
//                $countSouscategory->nbre = $countSouscategory->nbre + $nbChequeEmis;
//            }

            foreach ($countSouscategoriesValide as $countSoucategoryValide){
                if($countSoucategoryValide->souscategorie_id === $countSouscategory->souscategorie_id){
//                    if($countSoucategoryValide->souscategorie_id === 6){
//                        $countSoucategoryValide->nbre = $countSoucategoryValide->nbre + $nbChequeEmisValide;
//                    }

                    $countSouscategory->valide = $countSoucategoryValide->nbre;
                }
            }
        }

//        $countCarteSouscategories = $this->getDoctrine()
//            ->getRepository('AppBundle:Separation')
//            ->countSoussouscategorieCarte($dossier, $exercice);

//        $countCarteSouscategoriesValide = $this->getDoctrine()
//            ->getRepository('AppBundle:Separation')
//            ->countSoussouscategorieCarte($dossier, $exercice, true);

//        foreach ($countCarteSouscategories as $countCarteSouscategory){
//            foreach ($countCarteSouscategoriesValide as $countCarteSouscategoryValide){
//                if($countCarteSouscategoryValide->soussouscategorie_id === $countCarteSouscategory->soussouscategorie_id){
//                    $countCarteSouscategory->valide = $countCarteSouscategoryValide->nbre;
//                }
//            }
//        }

        return new JsonResponse([
            'souscategorie' => $countSouscategories,
//            'soussouscategorie' => $countCarteSouscategories
        ]);
    }


    function floatvalue($val){
        $val = str_replace(",","",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }


}