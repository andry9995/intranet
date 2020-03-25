<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 28/01/2019
 * Time: 10:00
 */

namespace BanqueBundle\Controller;


use AppBundle\Entity\BanqueCompteSouscategorie;
use AppBundle\Entity\BanqueSousCategorieAutre;
use AppBundle\Entity\CarteBleuBanqueCompte;
use AppBundle\Entity\CleSouscategorie;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\ImageFlague;
use AppBundle\Entity\ObManquant;
use AppBundle\Entity\Releve;
use Doctrine\ORM\Persisters\PersisterException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParametreController extends Controller
{
    public function numCbAction(){
        return $this->render('BanqueBundle:Banque/Parametre:numcb.html.twig');
    }

    public function cleListAction(Request $request){
        $rows = [];

        $get = $request->request;

        $banqueid = $get->get('banqueid');
        $banque = null;
        if($banqueid !== ''){
            $banque = $this->getDoctrine()
                ->getRepository('AppBundle:Banque')
                ->find($banqueid);
        }

        $souscategorieid = $get->get('souscategorieid');
        $souscategorie = null;
        if($souscategorieid !== ''){
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieid);
        }




        if($banque && $souscategorie) {

            $tous = $get->get('tous');

            if(intval($tous) === 0) {
                $cles = $this->getDoctrine()
                    ->getRepository('AppBundle:CleSouscategorie')
                    ->getListCleByBanqueSoucategorie($banque, $souscategorie);
            }
            else {
                $cles = $this->getDoctrine()
                    ->getRepository('AppBundle:CleSouscategorie')
                    ->getAllCleBySoucategorie($banque, $souscategorie);
            }

            $cleIds = [];
            /** @var CleSouscategorie $cle */
            foreach ($cles as $cle) {

                $cleIds[] = $cle->getId();


                $actionOb = '<i class="fa fa-save js_c_action_ob pointer icon-action" title="Enregistrer"></i><i class="fa fa-trash js_c_remove_ob pointer icon-action" title="Supprimer"></i>';
                if ($cle->getBanque() !== $banque) {
                    $actionOb = '<i class="fa fa-plus js_c_add_key_ob pointer icon-action" title="Ajouter"></i>';
                }

                $rows[] = ['id' => $cle->getId(), 'cell' =>
                    [
                        'c_mot_cle_ob' => $cle->getCle(),
                        'c_banque' => $cle->getBanque()->getNom(),
                        'c_check' => 'true',
                        'c_action_ob' => $actionOb
                    ]
                ];
            }


        }


        return new JsonResponse(['rows' => $rows, 'cles' => $cleIds]);
    }

    public function cleEditAction(Request $request){
        $post = $request->request;

        $id = $post->get('cleid');
        $banqueid = $post->get('banqueid');
        $banque = null;
        if($banqueid !== '') {
            $banque = $this->getDoctrine()
                ->getRepository('AppBundle:Banque')
                ->find($banqueid);
        }
        $souscategorie = null;
        $souscategorieid = $post->get('souscategorieid');

        if($souscategorieid !== ''){
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieid);
        }
        $cle = $post->get('cle');

        $em = $this->getDoctrine()
            ->getManager();

        if($cle === '' || $banque === null || $souscategorie == null)
            return new JsonResponse(['type'=>'error', 'message' => 'champ invalide']);

        if($id === 'new_row'){
            $cleSg = new CleSouscategorie();
            $cleSg->setCle($cle);
            $cleSg->setBanque($banque);
            $cleSg->setSouscategorie($souscategorie);

            $em->persist($cleSg);
            $em->flush();

            return new JsonResponse(['type'=>'success', 'action' => 'insert']);
        }
        else{
            $cleSg = $this->getDoctrine()
                ->getRepository('AppBundle:CleSouscategorie')
                ->find($id);

            $cleSg->setCle($cle);
            $cleSg->setSouscategorie($souscategorie);
            $cleSg->setBanque($banque);

            try {
                $em->flush();

                return new JsonResponse(['type' => 'success', 'action' => 'update']);
            }
            catch (PersisterException $e){
                return new JsonResponse(['type' => 'error', 'message' => $e->getMessage()]);
            }
        }

    }

    public function cleRemoveAction(Request $request){
        $id = $request->request->get('id');

        $cleScat = null;
        if($id !== 'new_row'){
            $cleScat = $this->getDoctrine()
                ->getRepository('AppBundle:CleSouscategorie')
                ->find($id);
        }

        if($cleScat !== null){
            $em = $this->getDoctrine()->getManager();
            $em->remove($cleScat);
            $em->flush();
        }
        return new JsonResponse(['type' => 'error', 'message' => 'clé introuvable']);
    }


    public function searchCbAction(Request $request)
    {
        $banquecompteid = $request->request->get('banquecompteid');
        $banquecompte = null;
        $banque = null;
        if ($banquecompteid !== '') {
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);
            if ($banquecompte) {
                $banque = $banquecompte->getBanque();
            }
        }
        $exercice = $request->request->get('exercice');

        $souscategorieid = $request->request->get('souscategorieid');
        $souscategorie = null;
        if ($souscategorieid !== '') {
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieid);
        }


        $cleIds = $request->request->get('cles');

        $rows = [];
        if ($banquecompte !== null && $souscategorie !== null) {
            /** @var CleSouscategorie[] $cles */
//            $cles = $this->getDoctrine()
//                ->getRepository('AppBundle:CleSouscategorie')
//                ->getListCleByBanqueSoucategorie($banque, $souscategorie);

            $cles = $this->getDoctrine()
                ->getRepository('AppBundle:CleSouscategorie')
                ->getListCleById($cleIds);

            $clearr = [];
            foreach ($cles as $cle) {
                $clearr [] = $cle->getCle();

                $releves = $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->searchReleve($banquecompte, $exercice, $clearr);
                /** @var Releve[] $releves */
                foreach ($releves as $releve) {
                    $rows[] = ['id' => $releve->getId(),
                        'cell' =>
                            [
                                'c_rb_image' => $releve->getImage()->getNom(),
                                'c_image_id' => $releve->getImage()->getId(),
                                'c_rb_date' => $releve->getDateReleve()->format('Y-m-d'),
                                'c_rb_libelle' => $releve->getLibelle(),
                                'c_rb_debit' => $releve->getDebit(),
                                'c_rb_credit' => $releve->getCredit()
                            ]
                    ];
                }
            }
        }

        return new JsonResponse(['rows' => $rows]);
    }

    public function addCbBcAction(Request $request){

        $numcompte = $request->request->get('numcb');
        $banquecompteid = $request->request->get('banquecompteid');

        $banquecompte = null;
        if($banquecompteid !== ''){
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);
        }

        if($banquecompte !== null && trim($numcompte) !== ''){

            $cartes = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->findBy(['banqueCompte' => $banquecompte, 'numCb' => $numcompte]);

            if(count($cartes) > 0){
                return new JsonResponse(['type' => 'error','message' => 'Num Carte bleu existe déjà']);
            }

            $carte = new CarteBleuBanqueCompte();
            $carte->setBanqueCompte($banquecompte)
                ->setNumCb($numcompte);

            $em = $this->getDoctrine()
                ->getManager();

            $em->persist($carte);
            $em->flush();

            return new JsonResponse(['type' => 'success','message' => 'Num Carte bleu enregistré']);
        }

        return new JsonResponse(['type' => 'error','message' => 'champ non rempli']);
    }

    public function cbDossierAction(Request $request){
        $get = $request->query;

        $banquecompteid = $get->get('banquecompteid');

        if($banquecompteid !== ''){
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);

            if($banquecompte){
                /** @var Dossier $dossier */
                $dossier = $banquecompte->getDossier();

                $ret = -1;
                if($dossier->getAvecCb() !== null){
                    $ret = $dossier->getAvecCb();
                }

                return new JsonResponse($ret);
            }


        }
        throw new NotFoundHttpException('Banque compte introuvable');

    }


    public function editAvecCbDossierAction(Request $request){
        $request = $request->request;

        $banquecompteid = $request->get('banquecompteid');
        $aveccb = $request->get('aveccb');


        if($banquecompteid !== ''){
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);

            if($banquecompte !== null){
                /** @var Dossier $dossier */
                $dossier = $banquecompte->getDossier();

                if(intval($aveccb) === -1){
                    $aveccb = null;
                }
                else{
                    $aveccb = intval($aveccb);
                }

                $em = $this->getDoctrine()->getManager();
                $dossier->setAvecCb($aveccb);

                $em->flush();

                $mustreload = false;
                if($aveccb === 0){
                    $cbBcs = $this->getDoctrine()
                        ->getRepository('AppBundle:CarteBleuBanqueCompte')
                        ->getCarteBleuBanquesCompteByDossier($dossier);
                    /** @var CarteBleuBanqueCompte $bc */
                    foreach ($cbBcs as $bc){
                        $em->remove($bc);
                        $mustreload = true;
                    }

                    $em->flush();
                }

                return new JsonResponse(['type' => 'success', 'message'=> 'Modification effectuée', 'recharger' => $mustreload]);
            }
            return new JsonResponse(['type' => 'error', 'message'=> 'banque compte introuvable']);


        }
        throw new NotFoundHttpException('Banque compte introuvable');


    }

    public function deleteCbBcAction(Request $request){
        $id = $request->request->get('id');

        if($id !== ''){
            $cbBc = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->find($id);

            $em = $this->getDoctrine()->getManager();

            if($cbBc !== null){
                $em->remove($cbBc);
                $em->flush();

                return new JsonResponse(['type' => 'success', 'message' => 'compte supprimé avec succès']);
            }
            return new JsonResponse(['type' => 'error', 'message' => 'compte non trouvé']);
        }

        throw new NotFoundHttpException('Carte Bleu non trouvée');
    }

    public function editCbBcAction(Request $request){
        $post = $request->request;

        $cbbcid = $post->get('cbbcid');
        $typeRecherche = $post->get('typerecherche');
        if($cbbcid !== ''){
            $em = $this->getDoctrine()->getManager();

            $cbbc = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->find($cbbcid);

            if($typeRecherche === '') {
                $typeRecherche = null;
            }
            else $typeRecherche = intval($typeRecherche);

            $cbbc->setTypeRecherche($typeRecherche);
            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Enregistrement effectué']);

        }
        return new JsonResponse(['type' => 'error', 'message' => 'Carte Bleu introuvable']);
    }

    public function listCbBcAction(Request $request){
        if($request->isXmlHttpRequest()) {
            $get = $request->query;

            $banquecompteid = $get->get('banquecompteid');

            if ($banquecompteid !== '') {
                $banquecompte = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->find($banquecompteid);

                /** @var CarteBleuBanqueCompte[] $cbBc */
                $cbBc = $this->getDoctrine()
                    ->getRepository('AppBundle:CarteBleuBanqueCompte')
                    ->findBy(['banqueCompte' => $banquecompte]);

                return $this->render('BanqueBundle:Banque/Parametre:carteBleuBc.html.twig', ['cbs' => $cbBc]);
            }

            throw  new NotFoundHttpException('Banque compte introuvable');
        }
        throw new AccessDeniedHttpException('Accès refusé');

    }

    public function BanquecompteSouscategorieEditAction(Request $request){
        if($request->isXmlHttpRequest()){
            $post = $request->request;

            $banquecompteid = $post->get('banquecompteid');
            $souscategorieid = $post->get('souscategorieid');

            $avecFrais = $post->get('avecfrais');

            if(intval($avecFrais) === -1){
                $avecFrais = null;
            }
            if($banquecompteid !== '' && $souscategorieid !== ''){
                $banquecompte = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->find($banquecompteid);

                $souscategorie = $this->getDoctrine()
                    ->getRepository('AppBundle:Souscategorie')
                    ->find($souscategorieid);

                $banquecomptesouscategories =  $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompteSouscategorie')
                    ->findBy(['banqueCompte' => $banquecompte, 'souscategorie' => $souscategorie]);
                $em = $this->getDoctrine()->getManager();
                if(count($banquecomptesouscategories) > 0){
                    $banquecomptesouscategorie = $banquecomptesouscategories[0];
                    $banquecomptesouscategorie->setAvec($avecFrais);
                    $em->flush();

                    return new JsonResponse(['type' => 'success', 'message' => 'mise à jour effectuée']);
                }
                else{
                    $banquecomptesouscategorie = new BanqueCompteSouscategorie();
                    $banquecomptesouscategorie->setBanqueCompte($banquecompte);
                    $banquecomptesouscategorie->setSouscategorie($souscategorie);
                    $banquecomptesouscategorie->setAvec($avecFrais);
                    $em->persist($banquecomptesouscategorie);
                    $em->flush();

                    return new JsonResponse(['type' => 'success', 'message' => 'ajout effectué']);
                }
            }
            return new JsonResponse(['type' => 'error', 'message' => 'Banque compte introuvable']);
        }
        throw new AccessDeniedHttpException('Accès refusé');
    }

    public function avecFraisAction(Request $request){
        if($request->isXmlHttpRequest()){
            $get = $request->query;

            $banquecompteid = $get->get('banquecompteid');
            $souscategorieid = $get->get('souscategorieid');

            if($banquecompteid !== '' && $souscategorieid !== ''){
                $banquecompte = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->find($banquecompteid);

                $souscategorie =  $this->getDoctrine()
                    ->getRepository('AppBundle:Souscategorie')
                    ->find($souscategorieid);

                /** @var BanqueCompteSouscategorie[] $banquecompteSouscategories */
                $banquecompteSouscategories = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompteSouscategorie')
                    ->findBy(['banqueCompte' =>$banquecompte, 'souscategorie' => $souscategorie]);

                $value = '-1';
                $label = '';
                $title = '';

                if(count($banquecompteSouscategories) > 0){
                        $banquecompteSouscategorie = $banquecompteSouscategories[0];
                        $value = $banquecompteSouscategorie->getAvec();
                }

                switch (intval($souscategorieid)){
                    case 8:
                        $label = 'Les frais bancaires sont ils nécessaires?';
                        $title = 'Information Frais bancaires';
                        break;
                    case 5:
                        $label = 'Les relevés LCR sont ils nécessaires?';
                        $title = 'Information Relevés LCR';
                        break;
                    case 6:
                        $label = 'La liste des virements / chèque emis est elle nécessaire?';
                        $title = 'Information Virements / Chèque emis';
                        break;
                    case 7:
                        $label = 'Les remises en banque sont-elles nécessaires?';
                        $title = 'Information Remise en banque';
                        break;
                    default:
                        break;
                }

                return new JsonResponse(['value' => $value, 'label' => $label, 'title' => $title]);

            }
            throw new NotFoundHttpException('Banque compte et/ou souscategorie introuvable');
        }
        throw new AccessDeniedHttpException('Accès refusé');
    }

    public function searchObAction(Request $request){
        $banquecompteid = $request->request->get('banquecompteid');
        $banquecompte = null;

        if ($banquecompteid !== '') {
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);
        }

        $exercice = $request->request->get('exercice');
        $souscategorieid = $request->request->get('souscategorieid');
        $souscategorie = null;

        $libelle = '';

        if ($souscategorieid !== '') {
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieid);

            $libelle = $souscategorie->getLibelleNew();
        }

        $clesIds = $request->request->get('cles');

        $rows = [];
        if ($banquecompte !== null && $souscategorie !== null) {
            /** @var CleSouscategorie[] $cles */
            $cles = $this->getDoctrine()
                ->getRepository('AppBundle:CleSouscategorie')
                ->getListCleById($clesIds);

            $clearr = [];

            $cbbcs = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->findBy(['banqueCompte' => $banquecompte]);

            foreach ($cles as $cle) {
                $clearr [] = $cle->getCle();
            }

            $releves = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->searchReleve($banquecompte, $exercice, $clearr);

            /** @var Releve[] $releves */
            foreach ($releves as $releve) {
                $rappr = '';
                $check = false;

                $mustSearchOb = false;
                //Jerena raha misy any @ OB
                $montant = abs($releve->getCredit() - $releve->getDebit());

                if ($souscategorie->getId() === 1) {
                    /** @var CarteBleuBanqueCompte $cbbc */
                    foreach ($cbbcs as $cbbc) {
                        if (strpos($releve->getLibelle(), $cbbc->getNumCb()) !== false) {
                            if ($cbbc->getTypeRecherche() === 1) {
                                $mustSearchOb = true;
                                $rappr = $libelle.' manquante';

                                $obManquants = $this->getDoctrine()
                                    ->getRepository('AppBundle:ObManquant')
                                    ->findBy(['releve' => $releve]);

                                if(count($obManquants) > 0)
                                    $check = true;
                            }
                        }
                    }
                } else {
                    if($banquecompte !== null && $souscategorie !== null){
                        /** @var BanqueCompteSouscategorie[] $banquecomptesouscategories */
                        $banquecomptesouscategories = $this->getDoctrine()
                            ->getRepository('AppBundle:BanqueCompteSouscategorie')
                            ->findBy(['banqueCompte' => $banquecompte, 'souscategorie' => $souscategorie]);
                        if(count($banquecomptesouscategories)> 0){
                            $banquecomptesouscategorie = $banquecomptesouscategories[0];
                            if($banquecomptesouscategorie->getAvec() !== 0){
                                $mustSearchOb = true;
                                $rappr = $libelle.' manquante';

                                $obManquants = $this->getDoctrine()
                                    ->getRepository('AppBundle:ObManquant')
                                    ->findBy(['releve' => $releve]);

                                if(count($obManquants) > 0)
                                    $check = true;

                            }
                        }
                    }
                }
                $obs = [];
                if ($mustSearchOb) {
                    $obs = $this->getDoctrine()
                        ->getRepository('AppBundle:BanqueSousCategorieAutre')
                        ->getObByReleve($banquecompte->getDossier(), $montant, $releve->getDateReleve(), 30);
                }
                $ids = [];
                $statusObManquant = null;
                if (count($obs) > 0) {
                    $piecevalide = false;
                    /** @var BanqueSousCategorieAutre $ob */
                    foreach ($obs as $ob) {
                        if ($ob->getImageFlague() === $releve->getImageFlague() && $ob->getImageFlague() !== null) {
                            $piecevalide = true;
                            $rappr = '<span class="text-success js_c_ob_deselect_action pointer">'.$libelle.' validée</span>';

                        } else {
                            $ids [] = $ob->getId();
                        }
                    }

                    if (!$piecevalide) {
                        $rappr = '<span class="text-warning pointer param-valide">'.$libelle.' à valider</span>';

                    }
                }

                $rows[] = ['id' => $releve->getId(),
                    'cell' =>
                        [
                            'c_ob_image' => $releve->getImage()->getNom(),
                            'c_image_id' => $releve->getImage()->getId(),
                            'c_ob_date' => $releve->getDateReleve()->format('Y-m-d'),
                            'c_ob_libelle' => $releve->getLibelle(),
                            'c_ob_debit' => $releve->getDebit(),
                            'c_ob_credit' => $releve->getCredit(),
                            'c_ob_rapprochement' => $rappr,
                            'c_ob_ids' => $ids,
                            'c_ob_check' => $check
                        ]
                ];
            }
        }

        return new JsonResponse(['rows' => $rows]);
    }

    public function obSelectedAction(Request $request){
        $ids = $request->request->get('ids');
        $ids = explode(',', $ids);

        $rows = [];
        if(count($ids) > 0){
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->getObByIds($ids);
            /** @var BanqueSousCategorieAutre $ob */
            foreach ($obs as $ob) {
                $rows[] = [
                    'id' => $ob->getId(),
                    'cell' => [
                        'c_ob_selected_image' => $ob->getImage()->getNom(),
                        'c_image_id' => $ob->getImage()->getId(),
                        'c_ob_selected_date' => $ob->getDate()->format('Y-m-d'),
                        'c_ob_selected_libelle' => $ob->getLibelle(),
                        'c_ob_selected_montant' => $ob->getMontant(),
                        'c_ob_selected_action' => '<span class="pointer">[VALIDER]</span>'
                    ]
                ];
            }
        }

        return new JsonResponse(['rows' => $rows]);
    }

    public function obFlagueAction(Request $request){
        $post = $request->request;

        $releveid = $post->get('releveid');
        $releve = null;
        if($releveid !== ''){
            $releve = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->find($releveid);
        }
        $obid = $post->get('obid');
        $ob = null;
        if($obid !== ''){
            $ob = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->find($obid);
        }

        if($ob !== null && $releve !== null){

            $em = $this->getDoctrine()->getManager();

            $imageFlague = new ImageFlague();
            $imageFlague->setDateCreation(new \DateTime('now'));

            $releve->setImageFlague($imageFlague);
            $ob->setImageFlague($imageFlague);

            $em->persist($imageFlague);
            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Traitement effectué']);

        }
        return new JsonResponse(['type'=>'error', 'message'=>'image non trouvée']);
    }


    public function obDeflagueAction(Request $request)
    {
        $post = $request->request;

        $releveid = $post->get('releveid');
        $releve = null;
        if ($releveid !== '') {
            $releve = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->find($releveid);

            $imageFlague = $releve->getImageFlague();
            $obs = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueSousCategorieAutre')
                ->findBy(['imageFlague' => $imageFlague]);

            $releve->setImageFlague(null);
            foreach ($obs as $ob){
                $ob->setImageFlague(null);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($imageFlague);
            $em->flush();



            return new JsonResponse(['type' => 'success', 'message' => 'Traitement effectué', 'cles' => []]);
        }

        return new JsonResponse(['type' => 'error', 'message' => 'relevé non trouvée']);




    }

    public function obManquantEditAction(Request $request){
        if($request->isXmlHttpRequest()){
            $post = $request->request;

            $selectedids = $post->get('selectedids');
            $notselectedids = $post->get('notselectedids');

            $em = $this->getDoctrine()
                ->getManager();

            foreach ($selectedids as $id){

                $releve= $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->find($id);

                $obmanquants = $this->getDoctrine()
                    ->getRepository('AppBundle:ObManquant')
                    ->findBy(['releve' => $releve]);

                if(count($obmanquants) === 0){
                    $obmanquant = new ObManquant();

                    $obmanquant->setReleve($releve);
                    $obmanquant->setStatus(0);

                    $em->persist($obmanquant);
                    $em->flush();
                }


            }

            foreach ($notselectedids as $id){

                $releve= $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->find($id);

                $obmanquants = $this->getDoctrine()
                    ->getRepository('AppBundle:ObManquant')
                    ->findBy(['releve' => $releve]);

                if(count($obmanquants) > 0) {
                    $obmanquant = $obmanquants[0];

                    $em->remove($obmanquant);
                }

                $em->flush();
            }

            return new JsonResponse(['type' => 'success', '']);
        }
        throw new AccessDeniedHttpException('Accès refusé');
    }
}