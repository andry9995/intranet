<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 18/01/2019
 * Time: 16:16
 */

namespace BanqueBundle\Controller;


use AppBundle\Entity\Banque;
use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\CarteBleuBanqueCompte;
use AppBundle\Entity\Categorie;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\HistoriqueAssemblage;
use AppBundle\Entity\HistoriqueCategorie;
use AppBundle\Entity\Image;
use AppBundle\Entity\ImageATraiter;
use AppBundle\Entity\ImageDuplique;
use AppBundle\Entity\ImageImage;
use AppBundle\Entity\Imputation;
use AppBundle\Entity\ImputationControle;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Panier;
use AppBundle\Entity\PileItem;
use AppBundle\Entity\Releve;
use AppBundle\Entity\ReleveCompte;
use AppBundle\Entity\ReleveDetail;
use AppBundle\Entity\ReleveImputation;
use AppBundle\Entity\SaisieControle;
use AppBundle\Entity\Separation;
use AppBundle\Entity\Souscategorie;
use AppBundle\Entity\Soussouscategorie;
use AppBundle\Functions\CustomPdoConnection;
use ImageBundle\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PDFMerger;
use Symfony\Component\Validator\Constraints\Count;

class SaisieBanqueController extends Controller
{
    public $pdo;

    public $mois;

    //initisalisation pdo
    public function __construct()
    {
        $con = new CustomPdoConnection();
        $this->pdo = $con->connect();

        $this->mois  =[
            1=>'Janvier',
            2=>'Fevrier',
            3=>'Mars',
            4=>'Avril',
            5=>'Mai',
            6=>'Juin',
            7=>'Juillet',
            8=>'Août',
            9=>'Septembre',
            10=>'Octobre',
            11=>'Novembre',
            12=>'Decembre'

        ];
    }

    public function listeNumCbAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;
        $banquecompteid = $get->get('banquecompteid');
        if($banquecompteid === '')
            throw new NotFoundHttpException('Banque compte introuvable');

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);

        if($banquecompte === null)
            throw new NotFoundHttpException('Banque compte introuvable');

        /** @var CarteBleuBanqueCompte[] $cbs */
        $cbs = $this->getDoctrine()
            ->getRepository('AppBundle:CarteBleuBanqueCompte')
            ->findBy(['banqueCompte' => $banquecompte]);

        return $this->render('@Banque/Banque/saisie/cbList.html.twig', ['cbs'=> $cbs]);
    }

    public function dateScanAction(Request $request)
    {
        $dateScans = [];

        //traitement dossier
        $did = $request->query->get('did');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($did);

        $souscategorieid = $request->query->get('souscategorieid');
        $souscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find($souscategorieid);

        $soussouscategorieAddId = $request->query->get('soussouscategorieadd');
        $soussouscategorieAdd = null;
        if($soussouscategorieAddId !== null){
            $soussouscategorieAdd = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find($soussouscategorieAddId);
        }


        $soussouscategorieid = $request->query->get('soussouscategorieid');
        $soussouscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->find(10);

        $exercice = $request->query->get('exercice');

        if ($exercice == '')
            $exercice = (new \DateTime())->format('Y');

        if($dossier !== null && $souscategorie !== null) {
            $dateScanTemps = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getDateScans($dossier, $souscategorie, $soussouscategorie, $exercice);

            $dateScanAddTemps = [];
            if($soussouscategorieAdd !== null) {
                if ($soussouscategorieAdd !== null) {
                    $dateScanAddTemps = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getDateScans($dossier, $soussouscategorieAdd->getSouscategorie(), $soussouscategorieAdd, $exercice);
                }
            }

            $dateScans = array_merge($dateScanAddTemps, $dateScanTemps);


            usort($dateScans, function($a1, $a2) {
                return $a1->getTimestamp() - $a2->getTimestamp(); // $v2 - $v1 to reverse direction
            });
        }

        return $this->render('@Banque/Banque/dateScanValue.html.twig', array('dateScans' => $dateScans));

    }

    public function saisieAction($etape,$releve)
    {
        /** @var Operateur $user */
        $user = $this->getUser();

        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->findBy(array('status' => 1), array('nom' => 'ASC'));

        /** @var Banque[] $banques */
//        $banques = $this->getDoctrine()
//            ->getRepository('AppBundle:Banque')
//            ->findBy(array(),array('nom' => 'ASC'));

        $banques = $this->getDoctrine()
            ->getRepository('AppBundle:Banque')
            ->getAllBanques();

        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find(16);


        $isouscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->findBy(array('categorie' => $categorie, 'actif' => 1), array('libelleNew' => 'ASC'));


        $isoussouscategorie = null;

        $souscategoriecarte = null;

        //Saisie relevés
        if($releve === 1) {

            $souscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->findBy(array('id' => 10));

            $isoussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySouscategories($souscategories);

        }
        //Opérations banquaires
        else {
            $souscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->getObSouscategories();

            $isoussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySouscategories($souscategories);

            /** @var Soussouscategorie[] $souscategoriecarte */
            $souscategoriecarte = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->findBy(['souscategorie' => $this->getDoctrine()
                        ->getRepository('AppBundle:Souscategorie')
                        ->find(1)]
                );
        }

        if($etape == 2){
            return $this->render('BanqueBundle:Banque/saisie:banque_saisie_controle.html.twig', array(
                'banque2s' => $banques,
                'clients' => $clients,
                'souscategories'=> $souscategories,
                'isouscategorie' => $isouscategorie,
                'isoussouscategorie' => $isoussouscategorie,
                'etape' => $etape
            ));
        }


        /** @var Categorie[] $recCategories */
        $recCategories = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->findBy(
                ['id' => 16, 'actif' => 1],
                ['libelleNew' => 'ASC']
                );

        /** @var Souscategorie[] $recSouscategories */
        $recSouscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->findBy([
                'categorie' => $this->getDoctrine()->getRepository('AppBundle:Categorie')->find(16),
                'actif' => 1
            ], ['libelleNew' => 'ASC']);


        return $this->render('BanqueBundle:Banque/saisie:banque_saisie.html.twig', array(
            'banque2s' => $banques,
            'clients' => $clients,
            'souscategories'=> $souscategories,
            'souscategoriecartes' => $souscategoriecarte,
            'isouscategorie' => $isouscategorie,
            'isoussouscategorie' => $isoussouscategorie,
            'etape' => $etape,
            'releve' => $releve,
            'recCategories' => $recCategories,
            'recSouscategories' => $recSouscategories
        ));
    }

    public function enteteReleveAction(Request $request)
    {
        $image = $request->request->get('image');

        /** @var Image $imageE */
        $imageE = null;

        if($image !== ''){
            $imageE = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($image);
        }

        if(!$imageE)
            throw new NotFoundHttpException('Image introuvable');

        $banquecompteid = $request->request->get('banquecompte');
        $releve = $request->request->get('releve');
        if($releve !== ''){
            $releve = intval($releve);
        }
//        $ddate = $this->formatSur($request->request->get('ddate'));
        $ddate = \DateTime::createFromFormat('d/m/Y',$request->request->get('ddate'));
        $ddebit = $request->request->get('ddebit');
        $dcredit = $request->request->get('dcredit');
        $dpage = $request->request->get('dpage');
//        $fdate = $this->formatSur($request->request->get('fdate'));
        $fdate = \DateTime::createFromFormat('d/m/Y', $request->request->get('fdate'));

        $fdebit = $request->request->get('fdebit');
        $fcredit = $request->request->get('fcredit');
        $fpage = $request->request->get('fpage');

        //tester si banque compte existe deja

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);


        $newExercice = $imageE->getExercice();
        try {
            $newExercice = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getExerciceByPeriode($imageE, $ddate, $fdate);
        } catch (\Exception $e) {
        }

        if($newExercice!== $imageE->getExercice()){
            $imageE->setExercice($newExercice);
        }


        $em = $this->getDoctrine()
            ->getManager();

        $em->flush();

        $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

        $dsolde = $dcredit - $ddebit;
        $fsolde = $fcredit - $fdebit;

        foreach ($bankEntities as $bankEntity){
            /** @var SaisieControle[] $scs */
            $scs = $this->getDoctrine()
                ->getRepository('AppBundle:' . $bankEntity)
                ->findBy(['image' => $image]);

            if (count($scs) > 0) {

                $sc = $scs[0];
                $sc->setImage($imageE);
                $sc->setBanqueCompte($banquecompte);
                $sc->setNumReleve($releve);
                $sc->setPeriodeD1($ddate);
                $sc->setSoldeDebut($dsolde);
                $sc->setPageSoldeDebut($dpage);
                $sc->setPeriodeF1($fdate);
                $sc->setSoldeFin($fsolde);
                $sc->setPageSoldeFin($fpage);

                $em->persist($sc);
                $em->flush();
            }
            else{
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

                $sc->setImage($imageE);
                $sc->setBanqueCompte($banquecompte);
                $sc->setNumReleve($releve);
                $sc->setPeriodeD1($ddate);
                $sc->setSoldeDebut($dsolde);
                $sc->setPageSoldeDebut($dpage);
                $sc->setPeriodeF1($fdate);
                $sc->setSoldeFin($fsolde);
                $sc->setPageSoldeFin($fpage);

                $em->persist($sc);
                $em->flush();
            }
        }

        //changement status de l'image
        $imageE->setStatus(3);
        if($imageE->getCtrlSaisie() < 3) {
            $imageE->setCtrlSaisie(2);
            $imageE->setSaisie2(2);
            $imageE->setImputation(2);
            $imageE->setCtrlImputation(2);

            $em->flush();
        }

        return new JsonResponse($image);
    }

    public function listeImageAction(Request $request, $banquecompteid)
    {
        $did = $request->request->get('dossier');
        $dscan = $request->request->get('dscan');
        $souscat = $request->request->get('souscat');
        $soussouscat = $request->request->get('soussouscat');
//        $soussouscatadd = $request->request->get('soussouscatadd');
        $etape = $request->request->get('etape');
        $exercice = $request->request->get('exercice');

        if ($exercice == '') {
            $now = new \DateTime();
            $exercice = $now->format('Y');
        }

        $datasTemp = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
//            ->getListImageBanque($did, $exercice, $dscan, $souscat, $soussouscat, $etape, $banquecompteid);
            ->getListImageBanque($did, $exercice, $dscan, $souscat, $etape, $banquecompteid);

        $datasAdd = [];

//        if($soussouscatadd !== null){
//            $datasAdd = $this->getDoctrine()
//                ->getRepository('AppBundle:Image')
//                ->getListImageBanque($did, $exercice, $dscan, 153, $soussouscatadd, $etape, $banquecompteid);
//        }

        $datas = array_merge($datasTemp, $datasAdd);

        $datar = [];

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        if (count($datas) > 0) {
            $imageDedutFin = "(". $datas[0]->id . "," . $datas[count($datas) - 1]->id . ")";

            $query = "SELECT S.periode_d1,S.periode_f1,S.solde_debut,S.solde_fin 
                        FROM saisie_controle S 
                        WHERE S.image_id IN " . $imageDedutFin . " 
                        ORDER BY S.periode_d1, S.periode_f1";

            $prep = $pdo->prepare($query);
            $prep->execute();
            $datar = $prep->fetchAll();
        }

        $soldeDebutG = 0;
        $soldeFinG = 0;

        if (count($datar) > 0) {
            $soldeDebutG = $datar[0]->solde_debut;
            $soldeFinG = $datar[count($datar) - 1]->solde_fin;
        }

        $title = '';

        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($did);

        if($dossier){
           $title = $this->setTitle($dossier, $exercice);
        }

        $status = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->getStatusDossier($dossier,$exercice);

        return $this->render('@Banque/Banque/saisie/saisie_liste_image.html.twig',
            [
                'images' => $datas,
                'etape' => $etape,
                'souscategorie' => $souscat,
                'soldeDebutG' => $soldeDebutG,
                'soldeFinG' => $soldeFinG,
                'title' => $title,
                'status' => $status,
                'dossierIdTemp' => $dossier->getId()
            ]
        );
    }

    public function listeImagePanierAction(Request $request)
    {
        $souscat = $request->request->get('souscat');
//        $soussouscat = $request->request->get('soussouscat');
        $dossier = $request->request->get('dossier');
        $exercice = $request->request->get('exercice');
        $etape = $request->request->get('etape');
        $banquecompteid = $request->request->get('banquecompteid');



        $operateur = $this->getUser();
        $datas = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
//            ->getListImagePanierBanque($dossier, $exercice,$souscat, $soussouscat, $etape, $operateur->getId(), $banquecompteid);
            ->getListImagePanierBanque($dossier, $exercice,$souscat,  $etape, $operateur->getId(), $banquecompteid);

        $datar = [];

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        /** @var BanqueCompte $banquecomptetemp */
        $banquecomptetemp = null;
        $exercicetemp = null;

        if (count($datas) > 0) {
            $exercicetemp = $datas[0]->exercice;

            $imageDedutFin = "(". $datas[0]->id . "," . $datas[count($datas) - 1]->id . ")";

            $query = "SELECT S.periode_d1,S.periode_f1,S.solde_debut,S.solde_fin, S.banque_compte_id 
                        FROM saisie_controle S 
                        WHERE S.image_id IN " . $imageDedutFin . " 
                        ORDER BY S.periode_d1, S.periode_f1";

            $prep = $pdo->prepare($query);
            $prep->execute();
            $datar = $prep->fetchAll();
        }

        $soldeDebutG = 0;
        $soldeFinG = 0;

        if (count($datar) > 0) {
            $banquecomptetempId = $datar[0]->banque_compte_id;

            if($banquecomptetempId!== null){
                $banquecomptetemp = $this->getDoctrine()
                    ->getRepository('AppBundle:BanqueCompte')
                    ->find($banquecomptetempId);
            }

            $soldeDebutG = $datar[0]->solde_debut;
            $soldeFinG = $datar[count($datar) - 1]->solde_fin;
        }

        $dossierEntity = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossier);

        $title = '';

        if($dossierEntity){
            /** @var Dossier $dossierEntity */
          $title = $this->setTitle($dossierEntity, $exercicetemp);
        }

        $status = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->getStatusDossier($dossierEntity,$exercicetemp);

        return $this->render('@Banque/Banque/saisie/saisie_liste_image.html.twig',
            [
                'images' => $datas,
                'etape' => $etape,
                'souscategorie' => $souscat,
                'soldeDebutG' => $soldeDebutG,
                'soldeFinG' => $soldeFinG,
                'exercice' => $exercicetemp,
                'banquecompte' => $banquecomptetemp,
                'title'=> $title,
                'status' => $status,
                'dossierIdTemp' => $dossierEntity->getId()
            ]
        );
    }


    public function validerImageAction(Request $request){
        $post = $request->request;

        $imageid = $post->get('imageid');

        $images = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getImageBanque($imageid);

        if(count($images) == 0)
            throw new NotFoundHttpException('Image Introuvable');

        $image  = $images[0];

        return $this->render('@Banque/Banque/saisie/saisie_image_item.html.twig',[
            'image' => $image,
            'etape' => 'OS_1'
        ]);
    }

    public function imageAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $ids = $request->query->get('ids');

        /** @var Image[] $images */
        $images = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListImageByids($ids);

        return $this->render('@Banque/Banque/saisie/saisie_image.html.twig', [
            'etape' => 'BANQUE',
            'images' => $images
        ]);
    }

    public function assembleRestoreAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossierid = $get->get('dossierid');
        $exercice = $get->get('exercice');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $historiqueAssemblages = $this->getDoctrine()
            ->getRepository('AppBundle:HistoriqueAssemblage')
            ->getListAssembleByDossierExerice($dossier, $exercice);

        /** @var HistoriqueAssemblage $historiqueAssemblage */

        $rows = [];
        foreach ($historiqueAssemblages as $historiqueAssemblage){
            $rows[] = [
                'id' => $historiqueAssemblage->getId(),
                'cell' => [
                    'd_image_id' => $historiqueAssemblage->getImageFinale()->getId(),
                    'd_image' => $historiqueAssemblage->getImageFinale()->getNom(),
                    'd_image_id_or' => $historiqueAssemblage->getImageOriginale()->getId(),
                    'd_image_or' => $historiqueAssemblage->getImageOriginale()->getNom(),
                    'd_action' => '<i class="fa fa-unlink icon-action d-action" title="Desassembler"></i>'
                ]
            ];

        }

        return new JsonResponse(['rows' => $rows]);

    }

    public function assembleRestorePieceAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $em = $this->getDoctrine()
            ->getManager();

        $historiquesIds = $request->request->get('ids');

        /** @var HistoriqueAssemblage[] $historiques */
        $historiques = $this->getDoctrine()
            ->getRepository('AppBundle:HistoriqueAssemblage')
            ->getListAssembleByIds($historiquesIds);


        $imageDeleted = null;
        $imageRestored = '';
        foreach ($historiques as $historique){

            $originale = $historique->getImageOriginale();
            $originale->setSupprimer(0);

            if($imageDeleted === null){
                $finale = $historique->getImageFinale();
                $finale->setSupprimer(6);

                $imageDeleted = $finale->getId();
            }

            if($imageRestored === ''){
                $imageRestored .=$originale->getId();
            }
            else{
                $imageRestored .= ','.$originale->getId();
            }

            $historique->setDateDesassemblage(new \DateTime('now'));
            $historique->setDesassemblageOperateur($this->getUser());
        }

        $em->flush();

        return new JsonResponse([
            'type' => 'success',
            'message' => 'Image restaurée avec succès',
            'deleted' => $imageDeleted,
            'restored' => $imageRestored
        ]);
    }

    public function assembleAction(Request $request)
    {

        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        //Traitement images
        $imageids = $request->request->get('images');
        $imageOriginales = [];


        $em = $this->getDoctrine()
            ->getManager();

        if (count($imageids) > 1) {

            $images = [];
            $chemins = [];
            $firstDateScan = '';

            $nomTemp = '';

            $now = new \DateTime();
            $exercice = $now->format('Y');
            $lot = null;
            $oldSeparation = null;

            /** @var Releve[] $releves */
            $releves = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->createQueryBuilder('r')
                ->where('r.image IN (:images)')
                ->setParameter('images', $imageids)
                ->orderBy('r.banqueCompte')
                ->addOrderBy('r.dateReleve')
                ->getQuery()
                ->getResult();

            $imageFlagues = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->createQueryBuilder('i')
                ->where('i.imageFlague IN (:imageids)')
                ->setParameter('imageids', $imageids)
                ->getQuery()
                ->getResult();


            /** @var ImputationControle[] $imputationControles */
            $imputationControles = $this->getDoctrine()
                ->getRepository('AppBundle:ImputationControle')
                ->createQueryBuilder('ic')
                ->innerJoin('ic.image', 'image')
                ->where('ic.image in (:images)')
                ->setParameter('images', $imageids)
                ->orderBy('ic.periodeD1')
                ->addOrderBy('image.nom')
                ->select('ic')
                ->getQuery()
                ->getResult();

            $newImputationControle = null;

            if (count($imputationControles) > 0) {


                $firstImputationControle = $imputationControles[0];
                $lastImputationControle = $imputationControles[count($imputationControles) - 1];

                $newImputationControle = new ImputationControle();
                $newImputationControle->setBanqueCompte($firstImputationControle->getBanqueCompte());
                $newImputationControle->setRs($firstImputationControle->getRs());
                $newImputationControle->setPeriodeD1($firstImputationControle->getPeriodeD1());
                $newImputationControle->setPeriodeF1($lastImputationControle->getPeriodeF1());
                $newImputationControle->setSoldeDebut($firstImputationControle->getSoldeDebut());
                $newImputationControle->setSoldeFin($lastImputationControle->getSoldeFin());
                $newImputationControle->setSouscategorie($firstImputationControle->getSouscategorie());
                $newImputationControle->setSoussouscategorie($firstImputationControle->getSoussouscategorie());
                $newImputationControle->setBanqueCompte($firstImputationControle->getBanqueCompte());

                $em->persist($newImputationControle);
            }



            /** @var Imputation[] $imputations */
            $imputations = $this->getDoctrine()
                ->getRepository('AppBundle:Imputation')
                ->createQueryBuilder('ic')
                ->innerJoin('ic.image', 'image')
                ->where('ic.image in (:images)')
                ->setParameter('images', $imageids)
                ->orderBy('ic.periodeD1')
                ->addOrderBy('image.nom')
                ->select('ic')
                ->getQuery()
                ->getResult();

            $newImputation = null;

            if (count($imputations) > 0) {
                $firstImputation = $imputations[0];
                $lastImputation = $imputations[count($imputations) - 1];

                $newImputation = new Imputation();
                $newImputation->setBanqueCompte($firstImputation->getBanqueCompte());
                $newImputation->setRs($firstImputation->getRs());
                $newImputation->setPeriodeD1($firstImputation->getPeriodeD1());
                $newImputation->setPeriodeF1($lastImputation->getPeriodeF1());
                $newImputation->setSoldeDebut($firstImputation->getSoldeDebut());
                $newImputation->setSoldeFin($lastImputation->getSoldeFin());
                $newImputation->setSoussouscategorie($firstImputation->getSoussouscategorie());
                $newImputation->setSouscategorie($firstImputation->getSouscategorie());
                $newImputation->setBanqueCompte($firstImputation->getBanqueCompte());

                $em->persist($newImputation);
            }


            /** @var SaisieControle[] $saisieControles */
            $saisieControles = $this->getDoctrine()
                ->getRepository('AppBundle:SaisieControle')
                ->createQueryBuilder('ic')
                ->innerJoin('ic.image', 'image')
                ->where('ic.image in (:images)')
                ->setParameter('images', $imageids)
                ->orderBy('ic.periodeD1')
                ->addOrderBy('image.nom')
                ->select('ic')
                ->getQuery()
                ->getResult();

            $newSaisieControle = null;

            if (count($saisieControles) > 0) {
                $firstSaisieControle = $saisieControles[0];
                $lastSaisieControle = $saisieControles[count($saisieControles) - 1];

                $newSaisieControle = new SaisieControle();
                $newSaisieControle->setBanqueCompte($firstSaisieControle->getBanqueCompte());
                $newSaisieControle->setRs($firstSaisieControle->getRs());
                $newSaisieControle->setPeriodeD1($firstSaisieControle->getPeriodeD1());
                $newSaisieControle->setPeriodeF1($lastSaisieControle->getPeriodeF1());
                $newSaisieControle->setSoldeDebut($firstSaisieControle->getSoldeDebut());
                $newSaisieControle->setSoldeFin($lastSaisieControle->getSoldeFin());
                $newSaisieControle->setSoussouscategorie($firstSaisieControle->getSoussouscategorie());
                $newSaisieControle->setBanqueCompte($firstSaisieControle->getBanqueCompte());

                $em->persist($newSaisieControle);

            }

            foreach ($imageids as $imageid) {
                /** @var Image $image */
                $image = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->find($imageid);

                $imageOriginales[] = $image;

                $nomTemp .= $image->getNom();
                $dateScanFormated = $image->getLot()->getDateScan()->format('Ymd');

                if ($firstDateScan === '') {
                    $firstDateScan = $dateScanFormated;
                    $exercice = $image->getExercice();
                    $lot = $image->getLot();
                    $oldSeparations = $this->getDoctrine()
                        ->getRepository('AppBundle:Separation')
                        ->findBy(array('image' => $image));

                    if (count($oldSeparations) > 0) {
                        $oldSeparation = $oldSeparations[0];
                    }
                }

                $fileName = $image->getNom() . '.' . $image->getExtImage();
//                $oldFile = $lesexpertsPath . '/' . $dateScanFormated . '/' . $fileName;

                $imageService = new ImageService($this->getDoctrine()->getManager());
                $oldFile = $imageService->getUrl($image->getId());

                $localdir = $this->get('kernel')->getRootDir() . '/../web/IMAGES/' . $dateScanFormated . '/';

                if (!file_exists($localdir)) {
                    mkdir($localdir, 0777);
                }

                $newFile = $localdir . $fileName;

                if (!file_exists($newFile)) {

                    if ($imageService->copySecureFile($oldFile, $newFile)) {
                        $images[] = $image;
                        $chemins[] = $newFile;
                    }

                } else {
                    $images[] = $image;
                    $chemins[] = $newFile;
                }
            }


            $pdf = new PDFMerger();
            foreach ($chemins as $chemin) {
                $pdf->addPDF($chemin, 'all');
            }



            $newImageFile = $this->get('kernel')->getRootDir() . '/../web/IMAGES/' . $firstDateScan . '/' . $nomTemp . '.pdf';
            if (!file_exists($newImageFile)) {
//                $pdf->merge('file', $newImageFile);
                $res = $this->combine_pdf($newImageFile, $chemins);
            }

            $stream = fopen($newImageFile, "r");
            $content = fread($stream, filesize($newImageFile));

            if (!$stream || !$content)
                echo 0;

            $nbPage = 0;
            $regex = "/\/Count\s+(\d+)/";

            if (preg_match_all($regex, $content, $matches))
                $nbPage = max(max($matches));

            $sourceimage = $this->getDoctrine()
                ->getRepository('AppBundle:SourceImage')
                ->find(7);

            fclose($stream);

            $newImage = new Image();
            $newImage->setNbpage($nbPage)
                ->setNomTemp($nomTemp)
                ->setOriginale($nomTemp)
                ->setExercice($exercice)
                ->setDownload($now)
                ->setNumerotationLocal(1)
                ->setRenommer(1)
                ->setLot($lot)
                ->setSaisie1(3)
                ->setSaisie2(3)
                ->setCtrlSaisie(3)
                ->setSourceImage($sourceimage);

            $em->persist($newImage);


            if ($newImputationControle !== null)
                $newImputationControle->setImage($newImage);

            if($newImputation !== null)
                $newImputation->setImage($newImage);

            if ($newSaisieControle !== null)
                $newSaisieControle->setImage($newImage);

            if (count($imageFlagues) > 0) {
                /** @var Image $imageFlague */
                foreach ($imageFlagues as $imageFlague) {
                    $imageFlague->setImageFlague($newImage);
                }
            }

            $newSeparation = new Separation();
            $newSeparation->setImage($newImage);
            $newSeparation->setCategorie($oldSeparation->getCategorie());
            $newSeparation->setSouscategorie($oldSeparation->getSouscategorie());
            $newSeparation->setSoussouscategorie($oldSeparation->getSousSouscategorie());
            $newSeparation->setOperateur($oldSeparation->getOperateur());

            $em->persist($newSeparation);

            foreach ($releves as $releve) {

                $newReleve = new Releve();

                $newReleve->setImage($newImage)
                    ->setDateReleve($releve->getDateReleve())
                    ->setLibelle($releve->getLibelle())
                    ->setDebit($releve->getDebit())
                    ->setCredit($releve->getCredit())
                    ->setDateSolde($releve->getDateSolde())
                    ->setDateValeur($releve->getDateValeur())
                    ->setTypeCompta($releve->getTypeCompta())
                    ->setNumCheque($releve->getNumCheque())
                    ->setNumOperation($releve->getNumOperation())
                    ->setRemarque($releve->getRemarque())
                    ->setCommentaire($releve->getCommentaire())
                    ->setAnalytique($releve->getAnalytique())
                    ->setTypeTiers($releve->getTypeTiers())
                    ->setTypeOperationBancaire($releve->getTypeOperationBancaire())
                    ->setBanqueCompte($releve->getBanqueCompte())
                    ->setPccAttente($releve->getPccAttente())
                    ->setAvecDetail($releve->getAvecDetail())
                    ->setReleveSource($releve->getReleveSource())
                    ->setEclate($releve->getEclate())
                    ->setCritere($releve->getCritere())
                    ->setIdentifiantPg($releve->getIdentifiantPg())
                    ->setNumReleve($releve->getNumReleve())
                    ->setNumPage($releve->getNumPage())
                    ->setRegimeTva($releve->getRegimeTva())
                    ->setReleve($releve->getReleve())
                    ->setMaj($releve->getMaj())
                    ->setCompteTiersTemp($releve->getCompteTiersTemp())
                    ->setCompteChgTemp($releve->getCompteChgTemp())
                    ->setCompteTvaTemp($releve->getCompteTvaTemp())
                    ->setImageTemp($releve->getImageTemp())
                    ->setImputationValider($releve->getImputationValider())
                    ->setCleDossier($releve->getCleDossier())
                    ->setTvaTaux($releve->getTauxTva())
                    ->setPasImage($releve->getPasImage())
                    ->setPasCle($releve->getPasCle())
                    ->setEngagementTresorerie($releve->getEngagementTresorerie())
                    ->setFlaguer($releve->getFlaguer())
                    ->setACategorise($releve->getACategorise())
                    ->setImageFlague($releve->getImageFlague())
                    ->setEcritureChange($releve->getEcritureChange())
                    ->setTvaTaux($releve->getTvaTaux());

                $em->persist($newReleve);

                //Jerena daholo ny clés rehetra
                $imageImages = $this->getDoctrine()
                    ->getRepository('AppBundle:ImageImage')
                    ->findBy(array('releve' => $releve));

                foreach ($imageImages as $imageImage) {
                    $newImageImage = new ImageImage();
                    $newImageImage->setReleve($newReleve)
                        ->setImage($imageImage->getImage())
                        ->setImageAutre($imageImage->getImageAutre())
                        ->setImageType($imageImage->getImageType());

                    $em->persist($newImageImage);
                }

                $pileItems = $this->getDoctrine()
                    ->getRepository('AppBundle:PileItem')
                    ->findBy(array('releve' => $releve));

                foreach ($pileItems as $pileItem) {
                    $newPileItem = new PileItem();
                    $newPileItem->setPileLettrage($pileItem->getPileLettrage())
                        ->setImage($pileItem->getImage())
                        ->setReleve($newReleve)
                        ->setImageStr($pileItem->getImageStr())
                        ->setEcriture($pileItem->getEcriture())
                        ->setValide($pileItem->getValide());

                    $em->persist($newPileItem);
                }

                $fkReleves = $this->getDoctrine()
                    ->getRepository('AppBundle\Entity\Releve')
                    ->findBy(array('releve' => $releve));

                foreach ($fkReleves as $fkReleve) {
                    $fkNewReleve = new Releve();

                    $fkNewReleve->setImage($fkReleve->getImage())
                        ->setDateReleve($fkReleve->getDateReleve())
                        ->setLibelle($fkReleve->getLibelle())
                        ->setDebit($fkReleve->getDebit())
                        ->setCredit($fkReleve->getCredit())
                        ->setDateSolde($fkReleve->getDateSolde())
                        ->setDateValeur($fkReleve->getDateValeur())
                        ->setTypeCompta($fkReleve->getTypeCompta())
                        ->setNumCheque($fkReleve->getNumCheque())
                        ->setNumOperation($fkReleve->getNumOperation())
                        ->setRemarque($fkReleve->getRemarque())
                        ->setCommentaire($fkReleve->getCommentaire())
                        ->setAnalytique($fkReleve->getAnalytique())
//                    ->setImageMere($releve->getImageMere())
                        ->setTypeTiers($fkReleve->getTypeTiers())
                        ->setTypeOperationBancaire($fkReleve->getTypeOperationBancaire())
                        ->setBanqueCompte($fkReleve->getBanqueCompte())
                        ->setPccAttente($fkReleve->getPccAttente())
                        ->setAvecDetail($fkReleve->getAvecDetail())
                        ->setReleveSource($fkReleve->getReleveSource())
                        ->setEclate($fkReleve->getEclate())
                        ->setCritere($fkReleve->getCritere())
                        ->setIdentifiantPg($fkReleve->getIdentifiantPg())
                        ->setNumReleve($fkReleve->getNumReleve())
                        ->setNumPage($fkReleve->getNumPage())
                        ->setRegimeTva($fkReleve->getRegimeTva())
                        ->setReleve($newReleve)
                        ->setMaj($fkReleve->getMaj())
                        ->setCompteTiersTemp($fkReleve->getCompteTiersTemp())
                        ->setCompteChgTemp($fkReleve->getCompteChgTemp())
                        ->setCompteTvaTemp($fkReleve->getCompteTvaTemp())
                        ->setImageTemp($fkReleve->getImageTemp())
                        ->setImputationValider($fkReleve->getImputationValider())
                        ->setCleDossier($fkReleve->getCleDossier())
                        ->setTvaTaux($fkReleve->getTauxTva())
                        ->setPasImage($fkReleve->getPasImage())
                        ->setPasCle($fkReleve->getPasCle())
                        ->setEngagementTresorerie($fkReleve->getEngagementTresorerie())
                        ->setFlaguer($fkReleve->getFlaguer())
                        ->setACategorise($fkReleve->getACategorise())
                        ->setImageFlague($fkReleve->getImageFlague())
                        ->setEcritureChange($fkReleve->getEcritureChange())
                        ->setTvaTaux($fkReleve->getTvaTaux());

                    $em->persist($fkNewReleve);
                }


                $releveComptes = $this->getDoctrine()
                    ->getRepository('AppBundle:ReleveCompte')
                    ->findBy(array('releve' => $releve));

                foreach ($releveComptes as $releveCompte) {
                    $newReleveCompte = new ReleveCompte();
                    $newReleveCompte->setReleve($newReleve)
                        ->setDebit($releveCompte->getDebit())
                        ->setCredit($releveCompte->getCredit())
                        ->setPcc($releveCompte->getPcc())
                        ->setPccAutre($releveCompte->getPccAutre());

                    $em->persist($newReleveCompte);
                }

                $releveDetails = $this->getDoctrine()
                    ->getRepository('AppBundle:ReleveDetail')
                    ->findBy(array('releve' => $releve));

                foreach ($releveDetails as $releveDetail) {
                    $newReleveDetail = new ReleveDetail();
                    $newReleveDetail->setReleve($newReleve)
                        ->setDebit($releveDetail->getDebit())
                        ->setCredit($releveDetail->getCredit())
                        ->setCompteChg($releveDetail->getCompteChg())
                        ->setCompteChg2($releveDetail->getCompteChg2())
                        ->setCompteTiers($releveDetail->getCompteTiers())
                        ->setCompteTiers2($releveDetail->getCompteTiers2())
                        ->setCompteTva($releveDetail->getCompteTva())
                        ->setCompteTva2($releveDetail->getCompteTva2())
                        ->setCompteBilanPcc($releveDetail->getCompteBilanPcc())
                        ->setLignePrincipale($releveDetail->getLignePrincipale());

                    $em->persist($newReleveDetail);
                }

                $releveImputations = $this->getDoctrine()
                    ->getRepository('AppBundle:ReleveImputation')
                    ->findBy(array('releve' => $releve));

                foreach ($releveImputations as $releveImputation) {
                    $newReleveImputation = new ReleveImputation();
                    $newReleveImputation->setReleve($newReleve)
                        ->setType($releveImputation->getType())
                        ->setTiers($releveImputation->getTiers())
                        ->setPcc($releveImputation->getPcc())
                        ->setDebit($releveImputation->getDebit())
                        ->setCredit($releveImputation->getCredit())
                        ->setImage($releveImputation->getImage());

                    $em->persist($newReleveImputation);
                }
            }


            //Creation images mères
            foreach ($imageids as $imageid) {
                /** @var Image $image */
                $image = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->find($imageid);

//                $newImageImage = new ImageImage();
//                $newImageImage->setImage($newImage)
//                    ->setImageAutre($image)
//                    ->setImageType(1);

                $image->setSupprimer(5);
            }

            $em->flush();

            //UPLOAD

            $em->refresh($newImage);



            if(count($imageOriginales) > 0) {
                foreach ($imageOriginales as $imageOriginale) {
                    $historiqueAssemblage = new HistoriqueAssemblage();
                    $historiqueAssemblage->setOperateur($this->getUser());
                    $historiqueAssemblage->setDateAssemblage(new \DateTime('now'));
                    $historiqueAssemblage->setImageFinale($newImage);
                    $historiqueAssemblage->setImageOriginale($imageOriginale);
                    $em->persist($historiqueAssemblage);
                }

            }

            $em->flush();

            $ftp_server = "ns315229.ip-37-59-25.eu";
            $ftp_user_name = "images";
            $ftp_user_pass = "wAgz37^8";
            $destination_file = $newImage->getLot()->getDateScan()->format('Ymd') . '/' . $newImage->getNom() . '.pdf';

            $source_file = $newImageFile;

            $conn_id = ftp_connect($ftp_server);
            ftp_pasv($conn_id, true);


            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

            if ((!$conn_id) || (!$login_result)) {
                return new JsonResponse(array('type' => 'error', 'message' => 'FTP connection failed'));
            }

            $source_file = explode('.pdf', $source_file)[0];

            $source_file .= '.pdf';

            ftp_pasv($conn_id, true);
            $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

            if (!$upload) {
                return new JsonResponse(array('type' => 'error', 'message' => 'FTP upload failed'));
            }

            ftp_close($conn_id);


            $fs = new Filesystem();

            $fs->rename('IMAGES/'.$firstDateScan.'/'.$nomTemp.'.pdf',
                'IMAGES/'.$firstDateScan.'/'.$newImage->getNom().'.pdf');


            return new JsonResponse(array('type' => 'success',
                'message' => 'Assemblage effectuée',
                'id' => $newImage->getId(),
                'nom' => $newImage->getNom()));

            // A faire verification image_flague_id, cle_dossier, releve_detailis

        }


        return new JsonResponse(array('type' => 'error'));


    }

    public function checkCutOffAction(Request $request)
    {

        $exercice = $request->query->get('exercice');
        $did = $request->query->get('dossierid');

        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($did);

        $dateCloture = null;
        $dateOuverture = null;

        if ($dossier) {
            $dateCloture = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossier, $exercice);

            $dateOuverture = clone  $dateCloture;
            $dateOuverture->add(new \DateInterval('P1D'));


        }

        $rows = [];

        $query = "SELECT S.num_releve,S.periode_d1,S.periode_f1,S.solde_debut,S.solde_fin,I.id,I.nom,I.status,SC.libelle_new 
          FROM saisie_controle as S, image as I, dossier as D, lot as L,separation as SP,souscategorie as SC
		  WHERE  I.lot_id = L.id
		  AND I.exercice = " . $exercice . "
		  AND L.dossier_id = D.id
		  AND S.image_id = I.id
		  AND SP.image_id=I.id
		  AND SP.souscategorie_id = SC.id
		  AND SC.id = 10		
		  AND L.dossier_id = " . $did . " ORDER BY S.periode_d1, S.periode_f1 ";
        $prep = $this->pdo->query($query);
        $datar = $prep->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($datar as $man) {

            $dateFin = new \DateTime($man['periode_f1']);

            $banqueCompte = null;


            if ($dateCloture < $dateFin) {

                //jerena aloha hoe misy releve sa tsy misy

                /** @var Releve[] $rels */
                $rels = $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->getRelevesByImage($this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->find($man['id']),  'DESC');


                if (count($rels) == 0) {
                    continue;
                } else {
                    $dateRel = $rels[0]->getDateReleve();

                    if ($dateRel <= $dateCloture) {
                        continue;
                    }

                    $soldeInitial = 0;
                    $dateInitial = '';
                    $totalReleves = 0;

                    $relsB = $this->getDoctrine()
                        ->getRepository('AppBundle:Releve')
                        ->getRelevesByBanqueCompte($rels[0]->getBanqueCompte(), $exercice);


                    /** @var Releve $relB */
                    foreach ($relsB as $relB) {
                        if ($relB->getDateReleve() <= $dateCloture) {
                            $totalReleves = $totalReleves + $relB->getDebit() - $relB->getCredit();
                        } else {
                            $image = $relB->getImage();
                            $saisieControles = $this->getDoctrine()
                                ->getRepository('AppBundle:SaisieControle')
                                ->findBy(array('image' => $image));

                            if (count($saisieControles) > 0) {
                                $soldeInitial = $saisieControles[0]->getSoldeDebut();
                                $dateInitial = $saisieControles[0]->getPeriodeD1()->format('Y-m-d');
//                                $soldeInitial
                            }
                        }
                    }


                    $rows [] = ['id' => $man['id'], 'cell' => [
                        'dateInitialN' => $dateInitial,
                        'soldeInitN' => $soldeInitial,
                        'soldeFinalN' => $soldeInitial + $totalReleves,
                        'dateFinalN' => $dateCloture->format('Y-m-d')
                    ]
                    ];


                }

            }
        }

        return new JsonResponse($rows);
    }

    public function cutoffListAction(Request $request, $dossierid, $exercice, $banquecompteid)
    {
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $dateCloture = null;
        $dateOuverture = null;

        if ($dossier) {
            $dateCloture = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossier, $exercice);

            $dateOuverture = clone  $dateCloture;
            $dateOuverture->add(new \DateInterval('P1D'));
        }

        $rows = [];

        $query = "SELECT S.num_releve,S.periode_d1,S.periode_f1,S.solde_debut,S.solde_fin,I.id,I.nom,I.status,SC.libelle_new 
                      FROM saisie_controle as S, image as I, dossier as D, lot as L,separation as SP,souscategorie as SC
                      WHERE  I.lot_id = L.id
                      AND I.exercice = " . $exercice . "
                      AND L.dossier_id = D.id
                      AND S.image_id = I.id
                      AND SP.image_id=I.id
                      AND SP.souscategorie_id = SC.id
                      AND SC.id = 10		
                      AND S.banque_compte_id = " .$banquecompteid ."
                      AND L.dossier_id = " . $dossierid . " ORDER BY S.periode_d1, S.periode_f1,I.nom ";
        $prep = $this->pdo->query($query);
        $datar = $prep->fetchAll(\PDO::FETCH_ASSOC);


        $dataTmp = [];

        if (count($datar) > 0) {
            $dataTmp[] = $datar[0];
            if (!in_array($datar[count($datar) - 1], $dataTmp)) {
                $dataTmp [] = $datar[count($datar) - 1];
            }
        }

        $datar = $dataTmp;

        $soldeInitial = 0;
        $soldeInitial2 = 0;
        if (count($datar) > 0) {
            $soldeInitial = $datar[0]['solde_debut'];
            $soldeInitial2 = $datar[count($datar) - 1]['solde_debut'];
        }

        foreach ($datar as $man) {

            $dateFin = new \DateTime($man['periode_f1']);
            $soldeFin = $man['solde_fin'];

            $banqueCompte = null;


            if ($dateCloture < $dateFin) {

                //jerena aloha hoe misy releve sa tsy misy

                $image = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->find($man['id']);

                /** @var Releve[] $rels */
                $rels = $this->getDoctrine()
                    ->getRepository('AppBundle:Releve')
                    ->getRelevesByImage($image, 'DESC');

                if (count($rels) == 0) {
                    $rows [] = ['id' => $man['id'], 'cell' => [
                        'c_date_init_n' => $man['periode_d1'],
                        'c_solde_init_n' => $man['solde_debut'],
                        'c_solde_fin_n' => $man['solde_fin'],
                        'c_date_fin_n' => $man['periode_f1'],
                        'c_date_init_n1' => '',
                        'c_solde_init_n1' => '',
                        'c_date_fin_n1' => '',
                        'c_solde_fin_n1' => '',
                        'c_action' => '<i class="fa fa-check"></i>'
                    ]
                    ];

                    continue;
                } else {
                    $dateRel = $rels[0]->getDateReleve();

                    if ($dateRel < $dateCloture) {

                        $rows [] = ['id' => $man['id'], 'cell' => [
                            'c_date_init_n' => $man['periode_d1'],
                            'c_solde_init_n' => $man['solde_debut'],
                            'c_solde_fin_n' => $man['solde_fin'],
                            'c_date_fin_n' => $man['periode_f1'],
                            'c_date_init_n1' => '',
                            'c_solde_init_n1' => '',
                            'c_date_fin_n1' => '',
                            'c_solde_fin_n1' => '',
                            'c_action' => '<i class="fa fa-check"></i>'
                        ]
                        ];

                        continue;
                    }


                    $dateInitial = '';
                    $totalReleves = 0;
                    $totalRelevesN1 = 0;



                   /** @var Releve[] $relsB */
                    $relsB = $this->getDoctrine()
                        ->getRepository('AppBundle:Releve')
                        ->getRelevesByBanqueCompte($rels[0]->getBanqueCompte(), $exercice);

                    /** @var Releve $relB */
                    foreach ($relsB as $relB) {
                        if ($relB->getDateReleve() <= $dateCloture) {
                            $totalReleves = $totalReleves - $relB->getDebit() + $relB->getCredit();
                        } else {
                            $image = $relB->getImage();
                            $saisieControles = $this->getDoctrine()
                                ->getRepository('AppBundle:SaisieControle')
                                ->findBy(array('image' => $image));

                            if (count($saisieControles) > 0) {
                                $dateInitial = $saisieControles[0]->getPeriodeD1()->format('Y-m-d');
                            }

                            $totalRelevesN1 = $totalRelevesN1 - $relB->getDebit() + $relB->getCredit();
                        }
                    }

                    $soldeFinN = 0;
                    if ($soldeInitial == 0) {
                        $soldeFinN = $soldeFin - $totalRelevesN1;
                    } else {
                        $soldeFinN = $soldeInitial + $totalReleves;

                    }

                    $rows [] = ['id' => $man['id'], 'cell' => [
                        'c_date_init_n' => $dateInitial,
                        'c_solde_init_n' => $soldeInitial2,
                        'c_solde_fin_n' => $soldeFinN,
                        'c_date_fin_n' => $dateCloture->format('Y-m-d'),
                        'c_date_init_n1' => $dateOuverture->format('Y-m-d'),
                        'c_solde_init_n1' => $soldeFinN,
                        'c_date_fin_n1' => $dateFin->format('Y-m-d'),
                        'c_solde_fin_n1' => $soldeFin,
                        'c_action' => 'CUTOFF'
                    ]
                    ];

                }

            } else {
                $rows [] = ['id' => $man['id'], 'cell' => [
                    'c_date_init_n' => $man['periode_d1'],
                    'c_solde_init_n' => $man['solde_debut'],
                    'c_solde_fin_n' => $man['solde_fin'],
                    'c_date_fin_n' => $man['periode_f1'],
                    'c_date_init_n1' => '',
                    'c_solde_init_n1' => '',
                    'c_date_fin_n1' => '',
                    'c_solde_fin_n1' => '',
                    'c_action' => '<i class="fa fa-check"></i>'
                ]
                ];
            }
        }

        $caption = 'CUTOFF: '.' '.$dossier->getSite()->getClient()->getNom(). ' | '.$dossier->getNom().' | '.$dateCloture->format("d-m-Y");

        return new JsonResponse(['rows' => $rows, 'caption' => $caption]);

    }

    public function cutOffAction(Request $request)
    {
        $post = $request->request;
        $imageid = $post->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dateInitN = \DateTime::createFromFormat('d/m/Y', $post->get('dateInitN'));
        $dateFinN = \DateTime::createFromFormat('d/m/Y', $post->get('dateFinN'));

        $soldeInitN = $post->get('soldeInitN');
        $soldeFinN = $post->get('soldeFinN');

        $dateInitN1 = \DateTime::createFromFormat('d/m/Y', $post->get('dateInitN1'));
        $dateFinN1 = \DateTime::createFromFormat('d/m/Y', $post->get('dateFinN1'));

        $soldeInitN1 = $post->get('soldeInitN1');
        $soldeFinN1 = $post->get('soldeFinN1');

        $em = $this->getDoctrine()->getManager();

        //Atao mise à jour ny @saisie
        $saisieControles = $this->getDoctrine()
            ->getRepository('AppBundle:SaisieControle')
            ->findBy(array('image' => $image));

        $saisieControle = null;

        $periodeD1Initial = null;
        $periodeF1Initial = null;
        $soldeDebutInitial = null;
        $soldeFinalInitial = null;

        if (count($saisieControles) > 0) {
            $saisieControle = $saisieControles[0];

            $periodeD1Initial = $saisieControle->getPeriodeD1();
            $periodeF1Initial = $saisieControle->getPeriodeF1();
            $soldeDebutInitial = $saisieControle->getSoldeDebut();
            $soldeFinalInitial = $saisieControle->getSoldeFin();

            $saisieControle->setPeriodeD1($dateInitN);
            $saisieControle->setPeriodeF1($dateFinN);

            $saisieControle->setSoldeDebut($soldeInitN);
            $saisieControle->setSoldeFin($soldeFinN);
        }


        /** @var Releve[] $relevesCutoff */
        $relevesCutoff = $this->getDoctrine()
            ->getRepository('AppBundle:Releve')
            ->getRelevesByImageDate($image, $dateFinN);

        $banquecompte = null;
        if (count($relevesCutoff) > 0) {
            $banquecompte = $relevesCutoff[0]->getBanqueCompte();
        }

        $sourceimage = $this->getDoctrine()
            ->getRepository('AppBundle:SourceImage')
            ->find(7);


        $newImage = new Image();
        $newImage->setNbpage($image->getNbpage())
            ->setNomTemp($image->getNom() . 'cutoff')
            ->setOriginale($image->getNom())
            ->setExercice($image->getExercice() + 1)
            ->setDownload(new \DateTime('now'))
            ->setNumerotationLocal(1)
            ->setRenommer(1)
            ->setLot($image->getLot())
            ->setSaisie1(3)
            ->setSaisie2(3)
            ->setCtrlSaisie(3)
            ->setSourceImage($sourceimage);

        $em->persist($newImage);
        $em->flush();

        $em->refresh($newImage);

        $oldSeparations = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->findBy(array('image' => $image));

        if (count($oldSeparations) > 0) {
            $oldSeparation = $oldSeparations[0];

            $newSeparation = new Separation();
            $newSeparation->setImage($newImage);
            $newSeparation->setSouscategorie($oldSeparation->getSouscategorie());
            $newSeparation->setSoussouscategorie($oldSeparation->getSoussouscategorie());
            $newSeparation->setCategorie($oldSeparation->getCategorie());
            $newSeparation->setOperateur($oldSeparation->getOperateur());

            $em->persist($newSeparation);
            $em->flush();
        }

        $newSaisieControle = new SaisieControle();
        $newSaisieControle->setImage($newImage);
        $newSaisieControle->setBanqueCompte($banquecompte);
        $newSaisieControle->setRs($saisieControle->getRs());
        $newSaisieControle->setPeriodeD1($dateInitN1);
        $newSaisieControle->setPeriodeF1($dateFinN1);
        $newSaisieControle->setSoldeDebut($soldeInitN1);
        $newSaisieControle->setSoldeFin($soldeFinN1);
        $newSaisieControle->setSoussouscategorie($saisieControle->getSoussouscategorie());

        $em->persist($newSaisieControle);
        $em->flush();

        foreach ($relevesCutoff as $releve) {
            $releve->setImage($newImage);
            $em->flush();
        }

        $dateScanFormated = $image->getLot()->getDateScan()->format('Ymd');
//        $lesexpertsPath = 'https://lesexperts.biz/IMAGES';
//        $oldFile = $lesexpertsPath . '/' . $dateScanFormated . '/' . $image->getNom() . '.' . $image->getExtImage();

        $imageService = new ImageService($this->getDoctrine()->getManager());
        $oldFile = $imageService->getUrl($image->getId());


        $localdir = $this->get('kernel')->getRootDir() . '/../web/IMAGES/' . $dateScanFormated . '/';


        if (!file_exists($localdir)) {
            mkdir($localdir, 0777);
        }

        $newFile = $localdir . $newImage->getNom() . '.' . $newImage->getExtImage();

        $error = [];

        if ($imageService->copySecureFile($oldFile, $newFile)) {
            $ftp_server = "ns315229.ip-37-59-25.eu";
            $ftp_user_name = "images";
            $ftp_user_pass = "wAgz37^8";
            $destination_file = $newImage->getLot()->getDateScan()->format('Ymd') . '/' . $newImage->getNom() . '.' . $newImage->getExtImage();
            $source_file = $newFile;

            $conn_id = ftp_connect($ftp_server);
            ftp_pasv($conn_id, true);

            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

            if ((!$conn_id) || (!$login_result)) {
                $error [] = ['message' => 'FTP connection failed'];

            }

            if (count($error) === 0) {
                if (!file_exists($source_file)) {
                    $error [] = ['message' => 'Unable to find source file'];
                }
            }

            if (count($error) === 0) {
                ftp_pasv($conn_id, true);

                $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

                if (!$upload) {
                    $error [] = ['message' => 'FTP upload has failed'];
                }
            }

            ftp_close($conn_id);
        } else {
            if (count($error) === 0) {
                $error [] = ['message' => 'Can\'t copy file'];
            }
        }

        if (count($error) === 0) {
            return new JsonResponse(['type' => 'success', 'message' => 'upload avec succès']);
        } else {

            //Réintialiser-na ilay images
            foreach ($relevesCutoff as $releve) {
                $releve->setImage($image);
                $em->flush();
            }

            $controles = $this->getDoctrine()
                ->getRepository('AppBundle:SaisieControle')
                ->findBy(array('image' => $newImage));

            foreach ($controles as $controle) {
                $controle->setImage($image);

                $em->flush();
            }

            $em->remove($newImage);

            if ($saisieControle) {
                $saisieControle->setPeriodeD1($periodeD1Initial);
                $saisieControle->setPeriodeF1($periodeF1Initial);
                $saisieControle->setSoldeDebut($soldeDebutInitial);
                $saisieControle->setSoldeFin($soldeFinalInitial);

                $em->flush();
            }

            return new JsonResponse(['type' => 'error', 'message' => $error[0]['message']]);
        }

    }

    public function releveImageDetailsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $post = $request->request;

            $image = $post->get('image');
            $soldedebut = $post->get('soldedebut');
            $soldefin = $post->get('soldefin');

            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($image);

            /** @var Releve[] $releves */
            $releves = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->getRelevesByImage($image, 'ASC');

            $rows = [];

            $progression = floatval($soldedebut);
            $soldefin = floatval($soldefin);

            foreach ($releves as $releve) {

                $attente = $releve->getPccAttente();

                $progression += -floatval($releve->getDebit()) + floatval($releve->getCredit());

                $rows[] = [
                    'id' => $releve->getId(), 'cell' => [
                        'r-date' => $releve->getDateReleve()->format('Y-m-d'),
                        'r-compte' => ($attente === null) ? '' : $attente->getCompte(),
                        'r-libelle' => $releve->getLibelle(),
                        'r-debit' => $releve->getDebit(),
                        'r-credit' => $releve->getCredit(),
                        'r-progression' => $progression,
                        'r-piece' => ($releve->getPieces() === null)? '' : $releve->getPieces(),
                        'r-tiers' => ($releve->getTiers() == null)? '': $releve->getTiers(),
                        'r-commentaire' => '',
                        'r-action' => '<i class="fa fa-save icon-action r-action" title="Enregistrer"></i>'
                    ]
                ];

            }

            if (count($releves) === 0) {
                $progression = $soldefin;
            }

            return new JsonResponse(array(
                'rows' => $rows,
                'progressionGenerale' => false,
                'progression' => $progression
            ));


        }
        throw new AccessDeniedHttpException('Accès refusé');
    }

    public function controleAction(Request $request)
    {
        $image = $request->query->get('image');
        $exercice = -1;
        $dossier = -1;
        $banquecompte = -1;
        $souscat = -1;

        if($image != ''){
            /** @var Image $image */
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($image);

            if($image){

                $dossier = $image->getLot()->getDossier()->getId();

                $exercice = $image->getExercice();

                $saisiecontroles = $this->getDoctrine()
                    ->getRepository('AppBundle:SaisieControle')
                    ->findBy(array('image' => $image));

                if(count($saisiecontroles) > 0){
                    $saisiecontrole =$saisiecontroles[0];
                    if($saisiecontrole->getBanqueCompte()){
                        $banquecompte = $saisiecontrole->getBanqueCompte()->getId();
                    }
                }

                $separations = $this->getDoctrine()
                    ->getRepository('AppBundle:Separation')
                    ->findBy(array('image' => $image));

                if(count($separations) > 0){
                    $separation = $separations[0];
                    if($separation->getSouscategorie()){
                        $souscat = $separation->getSouscategorie()->getId();
                    }
                }
            }
        }
        $rows = [];

        if($exercice !== -1 && $dossier !== -1 && $banquecompte !== -1 && $souscat !== -1) {

            $query = "SELECT S.num_releve,S.periode_d1,S.periode_f1,S.solde_debut,S.solde_fin,I.id,
                          I.nom,I.status,L.date_scan, I.source_image_id, SC.libelle_new, 
                          '' as remarque, 0 as trou, '' as ass, '' as assid, '' as assnb,
                          '' as c_solde_debut, '' as c_solde_fin 
                      FROM  saisie_controle as S, image as I, dossier as D, lot as L,separation as SP,souscategorie as SC
                      WHERE  I.lot_id = L.id
                      AND L.dossier_id = D.id
                      AND S.image_id = I.id
                      AND SP.image_id=I.id
                      AND SP.souscategorie_id = SC.id
                      AND L.dossier_id = " . $dossier . " 
                      AND I.exercice = " . $exercice . " 
                      AND I.supprimer = 0
                      AND SP.souscategorie_id = " . $souscat . "
                      AND S.banque_compte_id = " . $banquecompte . " 
                      ORDER BY S.periode_d1, S.periode_f1, I.nom";

            $con = new CustomPdoConnection();
            $pdo = $con->connect();
            $prep = $pdo->prepare($query);

            $prep->execute();
            /** @var Image[] $datas */
            $datas = $prep->fetchAll();

            //Mijery doublon
            for($i = 0; $i< count($datas) - 1; $i++){

                if($datas[$i]->remarque == '') {
                    $ti = $datas[$i];

                    for ($j = $i + 1; $j < count($datas); $j++) {
                        $tj = $datas[$j];

                        if ($tj->periode_d1 === $ti->periode_d1 &&
                            $tj->periode_f1 === $ti->periode_f1 &&
                            $tj->solde_debut === $ti->solde_debut &&
                            $tj->solde_fin === $ti->solde_fin
                        ) {
                            $datas[$j]->remarque = 'Doublon de '. $ti->nom;
                        }
                    }
                }
            }

            //Mijery trou
            for($i = 0; $i < count($datas) - 1; $i++){
                $ti = $datas[$i];
                $tj = $datas[$i+1];

                if($tj->remarque == ''){
                    if($ti->solde_fin != $tj->solde_debut){
                        $trou = $ti->solde_fin- $tj->solde_debut;
                        $datas[$i+1]->trou = $trou;

                        $datas[$i]->c_solde_fin = 'X';
                        $datas[$i+1]->c_solde_debut = 'X';
                    }
                    else{
                        $datas[$i]->c_solde_fin = 'Y';
                        $datas[$i+1]->c_solde_debut = 'Y';
                    }
                }
            }

            //Mijery a assembler
            for ($i = 0; $i<count($datas) -1 ; $i++) {
                $j = $i + 1;
                $tempi = $datas[$i];
                $tempj = $datas[$j];

                if ($tempi->solde_fin == 0) {
                    if ($tempj->solde_debut == 0) {
                        $datas[$j]->ass = $tempi->id;
                    }
                }
            }

            $count = 1;
            $assembler = '';

            if(count($datas) > 0){
                $assembler = $datas[0]->id;
            }

            for($i = 1; $i<count($datas); $i++){

                if($datas[$i]->ass !== ''){
                    $count++;
                    $assembler .= ','.$datas[$i]->id;
                    if($i < count($datas) - 1) {
                        if ($datas[$i + 1]->ass == '') {
                            $datas[$i]->assnb = $count;
                            $datas[$i]->assid = $assembler;
                        }
                    }
                    else{
                        $datas[$i]->assid = $assembler;
                        $datas[$i]->assnb = $count;
                    }
                }
                else{
                    $count = 1;
                    $assembler = $datas[$i]->id;
                }
            }

            foreach ($datas as $item) {

                if($item->assnb !== ''){
                    $item->assnb = $item->assnb.' images <i class="fa fa-level-up"></i>';
                }

                $sb = '';
                if($item->source_image_id === 3){
                    $sb = 'SB';
                }

                $rows []= [
                    'id' => $item->id,
                    'cell' => [
                        'c_image' => $item->nom,
                        'c_source' => $sb,
                        'c_periode_debut' => $item->periode_d1,
                        'c_periode_fin' => $item->periode_f1,
                        'c_solde_debut' => $item->solde_debut,
                        'c_solde_fin' => $item->solde_fin,
                        'c_doublon' => $item->remarque,
                        'c_trou' => $item->trou,
                        'c_id_assembler' => $item->assid,
                        'c_nb_assembler' => $item->assnb,
                        'c_c_solde_debut' => $item->c_solde_debut,
                        'c_c_solde_fin' => $item->c_solde_fin,
                    ]

                ];
            }
        }

        return new JsonResponse(['rows' => $rows]);

    }

    public function setDoublonAction(Request $request){

        $imageid = $request->request->get('imageid');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $lastTables = null;
        $withsc = false;

        $souscategorieD = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find(3);

        $soussouscategorieD = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->find(2259);

        if($image){
            $separations = $this->getDoctrine()
                ->getRepository('AppBundle:Separation')
                ->findBy(['image' => $image]);


            if($image->getCtrlImputation() > 1){
                $lastTables = $this->getDoctrine()
                    ->getRepository('AppBundle:ImputationControle')
                    ->findBy(['image' => $image]);

                $withsc = true;
            }
            else if($image->getImputation() > 1){
                $lastTables = $this->getDoctrine()
                    ->getRepository('AppBundle:Imputation')
                    ->findBy(['image' => $image]);

                $withsc = true;
            }
            else if($image->getCtrlSaisie() > 1){
                $lastTables = $this->getDoctrine()
                    ->getRepository('AppBundle:SaisieControle')
                    ->findBy(['image' => $image]);
            }

            $em = $this->getDoctrine()->getManager();

            if(count($separations) > 0){
                $separation = $separations[0];

                $historique = new HistoriqueCategorie();
                $historique->setImage($image);
                $historique->setCategorie($separation->getCategorie());
                $historique->setSoussouscategorie($separation->getSoussouscategorie());
                $historique->setSouscategorie($separation->getSouscategorie());
                $historique->setOperateur($this->getUser());
                $historique->setDateModification(new \DateTime('now'));
                $historique->setMotif('DOUBLON BANQUE');

                $em->persist($historique);

                $separation->setSouscategorie($souscategorieD);
                $separation->setSoussouscategorie($soussouscategorieD);


            }

            if(count($lastTables) > 0){
                /** @var Imputation $lastTable */
                $lastTable = $lastTables[0];

                if($withsc) {
                    $lastTable->setSouscategorie($souscategorieD);
                }
                $lastTable->setSoussouscategorie($soussouscategorieD);

            }

            //Atao traitement fini satria doublon
            /** @var Panier[] $paniers */
            $paniers = $this->getDoctrine()
                ->getRepository('AppBundle:Panier')
                ->getPanierSaisieBanqueByImage($image);

            foreach ($paniers as $panier){
                $panier->setFini(1);
            }

            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);
        }
        else{
            return new JsonResponse(['type' => 'error', 'message' => 'Image introuvable']);
        }
    }

    public function checkDoublonAction(Request $request)
    {
        $get = $request->query;

        $imageid = $get->get('imageId');

        $periodeDebut = $get->get('periodeDebut');
        $periodeFin = $get->get('periodeFin');
        $soldeDebut = $get->get('soldeDebut');
        $soldeFin = $get->get('soldeFin');

        $res = [];

        if($imageid === '' || $periodeFin === '' || $periodeDebut === '' || $soldeDebut === '' || $soldeFin === '')
            return new JsonResponse($res);

        $periodeDebut = \DateTime::createFromFormat('d/m/Y', $periodeDebut);
        $periodeFin = \DateTime::createFromFormat('d/m/Y', $periodeFin);


        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);


        if ($image) {
            $dossier = $image->getLot()->getDossier();

            $saisieControles = $this->getDoctrine()
                ->getRepository('AppBundle:SaisieControle')
                ->createQueryBuilder('sc')
                ->innerJoin('sc.image', 'image')
                ->innerJoin('image.lot', 'lot')
                ->innerJoin('lot.dossier', 'dossier')
                ->where('sc.periodeD1 = :periodeDebut')
                ->andWhere('sc.periodeF1 = :periodeFin')
                ->andWhere('sc.soldeDebut = :soldeDebut')
                ->andWhere('sc.soldeFin = :soldeFin')
                ->andWhere('image.exercice = :exercice')
                ->andWhere('dossier = :dossier')
                ->andWhere('image <> :image')
                ->setParameter('periodeDebut', $periodeDebut)
                ->setParameter('periodeFin', $periodeFin)
                ->setParameter('soldeDebut', $soldeDebut)
                ->setParameter('soldeFin', $soldeFin)
                ->setParameter('exercice', $image->getExercice())
                ->setParameter('dossier', $dossier)
                ->setParameter('image', $image)
                ->select('sc')
                ->getQuery()
                ->getResult();

            /** @var SaisieControle $saisieControle */
            foreach ($saisieControles as $saisieControle){
                $res[]= ['id' => $saisieControle->getImage()->getId(),
                'nom' => $saisieControle->getImage()->getNom()];
            }

        }

        return new JsonResponse($res);
    }

    public function restoreDoublonAction(Request $request){
        $post = $request->request;

        $imageid = $post->get('imageid');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image){
            $saisiecontroles = $this->getDoctrine()
                ->getRepository('AppBundle:SaisieControle')
                ->findBy(['image' => $image]);

            if(count($saisiecontroles) > 0){
                $refSc = $saisiecontroles[0];
                $refBc = $refSc->getBanqueCompte();
                if($refBc){
                    $historiques = $this->getDoctrine()
                        ->getRepository('AppBundle:HistoriqueCategorie')
                        ->getListeByBanqueCompte($refBc->getId(), $image->getExercice());

                    if(count($historiques) > 0){
                        $em = $this->getDoctrine()->getManager();

                        foreach ($historiques as $historique){
                            $refHistorique = $this->getDoctrine()
                                ->getRepository('AppBundle:HistoriqueCategorie')
                                ->find($historique->id);

                            $image = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->find($historique->image_id);

                            $refScatId = $historique->souscategorie_id;
                            $refScat = null;
                            if($refScatId && $refScatId != '')
                                $refScat = $this->getDoctrine()
                                    ->getRepository('AppBundle:Souscategorie')
                                    ->find($refScatId);

                            $refSscatId = $historique->soussouscategorie_id;
                            $refSscat = null;
                            if($refSscatId && $refSscatId != '')
                                $refSscat = $this->getDoctrine()
                                    ->getRepository('AppBundle:Soussouscategorie')
                                    ->find($refSscatId);

                            if($image){
                                $separations = $this->getDoctrine()
                                    ->getRepository('AppBundle:Separation')
                                    ->findBy(['image' => $image]);
                                $withsc = false;
                                $lastTables = [];

                                if($image->getCtrlImputation() > 1){
                                    $lastTables = $this->getDoctrine()
                                        ->getRepository('AppBundle:ImputationControle')
                                        ->findBy(['image' => $image]);

                                    $withsc = true;
                                }
                                else if($image->getImputation() > 1){
                                    $lastTables = $this->getDoctrine()
                                        ->getRepository('AppBundle:Imputation')
                                        ->findBy(['image' => $image]);

                                    $withsc = true;
                                }
                                else if($image->getCtrlSaisie() > 1){
                                    $lastTables = $this->getDoctrine()
                                        ->getRepository('AppBundle:SaisieControle')
                                        ->findBy(['image' => $image]);
                                }

                                if(count($lastTables) > 0){
                                    /** @var Imputation $lastTable */
                                    $lastTable = $lastTables[0];

                                    if($withsc) {
                                        $lastTable->setSouscategorie($refScat);
                                    }
                                    $lastTable->setSoussouscategorie($refSscat);

                                }

                                if(count($separations) > 0){
                                    $separation = $separations[0];
                                    $separation->setSouscategorie($refScat);
                                    $separation->setSoussouscategorie($refSscat);
                                }
                            }

                            $em->remove($refHistorique);
                        }

                        $em->flush();
                        return new JsonResponse(['type' => 'success', 'message'=> 'Restauration categorie effectuée']);
                    }
                    return new JsonResponse(['type' => 'warning', 'message'=> 'Aucune image à restaurer']);
                }
                return new JsonResponse(['type' => 'error', 'message'=> 'Banque compte non trouvé']);
            }
        }
        return new JsonResponse(['type' => 'error', 'message'=> 'Erreur restauration']);
    }

    public function traitementFiniAction(Request $request){
        $post = $request->request;

        $ip = $_SERVER['REMOTE_ADDR'];

        $imageIds = $post->get('images');

        $souscategorieId = $post->get('souscategorie');

        $souscategorie = null;
        if($souscategorieId !== '') {
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieId);
        }

        $etapeTraitement = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->find(11);

        $em = $this->getDoctrine()
            ->getManager();

        foreach ($imageIds as $imageId){
            /** @var Image $image */
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageId);

            $image->setSaisie1(3);
            $image->setSaisie2(3);
            $image->setCtrlSaisie(3);
            $image->setImputation(3);
            $image->setCtrlImputation(3);

            /** @var Image[] $imageTraiters */
            $imageTraiters = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->findBy(['image' => $image]);

            if(count($imageTraiters) > 0){
                $imageTraiter= $imageTraiters[0];
                $imageTraiter->setStatus(6);
                $imageTraiter->setSaisie1(2);
                $imageTraiter->setSaisie2(2);
            }

            /** @var Panier[] $paniers */
            $paniers = $this->getDoctrine()
                ->getRepository('AppBundle:Panier')
                ->findBy([
                    'image' => $image,
                    'operateur' => $this->getUser(),
                    'etapeTraitement' => $etapeTraitement
                ]);

            if(count($paniers) > 0){
                $panier = $paniers[0];
                $panier->setFini(1);

                if($souscategorie->getId() === 10) {
                    $etapeRb2 = $this->getDoctrine()
                        ->getRepository('AppBundle:EtapeTraitement')
                        ->find(26);

                    $panierRb2s = $this->getDoctrine()
                        ->getRepository('AppBundle:Panier')
                        ->findBy([
                            'image' => $image,
                            'etapeTraitement' => $etapeRb2
                        ]);

                    if(count($panierRb2s) === 0) {
                        $panierRb2 = new Panier();
                        $panierRb2->setCategorie($this->getDoctrine()
                            ->getRepository('AppBundle:Categorie')
                            ->find(16)
                        );
                        $panierRb2->setImage($image);
                        $panierRb2->setDossier($image->getLot()->getDossier());
                        $panierRb2->setDatePanier(new \DateTime());
                        $panierRb2->setOpPartageId($panier->getOpPartageId());
                        $panierRb2->setOperateur($this->getUser());
                        $panierRb2->setEtapeTraitement($etapeRb2);

                        $em->persist($panierRb2);
                    }
                }
            }

            $logs = new Logs();
            $logs
                ->setOperateur($this->getUser())
                ->setDateDebut(new \DateTime())
                ->setDateFin(new \DateTime())
                ->setIp($ip)
                ->setRemarque('TRAITEMENT FINI ' . $etapeTraitement->getLibelle() . '(' . $this->getUser()->getId() . ')')
                ->setDossier($image->getLot()->getDossier())
                ->setImage($image)
                ->setEtapeTraitement($etapeTraitement);

            $em->persist($logs);
        }

        $em->flush();

        return new JsonResponse([
            'type' => 'success',
            'message' =>  'Enregistrement effectuée'
        ]);
    }

    public function sobankAction(Request $request)
    {
        $get = $request->query;

        $imgSobank = $get->get('sobankid');
        $imgRel = $get->get('releveid');


        $scSobanks = $this->getDoctrine()
            ->getRepository('AppBundle:SaisieControle')
            ->findBy(['image' => $imgSobank]);

        $scRels = $this->getDoctrine()
            ->getRepository('AppBundle:SaisieControle')
            ->findBy(['image' => $imgRel]);

        if(count($scSobanks) > 0 && count($scRels) > 0){
            /** @var SaisieControle $scRel */
            $scRel = $scRels[0];
            /** @var SaisieControle $scSobank */
            $scSobank = $scSobanks[0];

            $imageService = new ImageService($this->getDoctrine()->getManager());

            $url = $imageService->getUrl($imgRel);


            return $this->render('@Banque/Banque/saisie/saisie_releve_sobank.html.twig',
            [
                'scRel' => $scRel,
                'scSb' => $scSobank,
                'url' => $url
            ]);

        }
        throw new NotFoundHttpException('Saisie controle introuvable');

    }

    public function sobankEditAction(Request $request){

        $post = $request->request;

        $imgSobankId = $post->get('img_sb_id');

        $imgSobank = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imgSobankId);

        $imgRelId = $post->get('img_rel_id');
        $imgRel = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imgRelId);

        $dateDebut = $post->get('date_debut_sb');
        if($dateDebut !== ''){
            $dateDebut = \DateTime::createFromFormat('d/m/Y', $dateDebut);
        }

        $dateFin = $post->get('date_fin_sb');
        if($dateFin !== ''){
            $dateFin = \DateTime::createFromFormat('d/m/Y', $dateFin);
        }

        $soldeDebut = $post->get('credit_sb_init');
        $soldeFin = $post->get('credit_sb_fin');

        if($imgSobank && $imgRel){

            $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];

            $em = $this->getDoctrine()
                ->getManager();

            foreach ($bankEntities as $bankEntity) {
                /** @var SaisieControle[] $scs */
                $scs = $this->getDoctrine()
                    ->getRepository('AppBundle:' . $bankEntity)
                    ->findBy(['image' => $imgSobank]);

                if (count($scs) > 0) {

                    $sc = $scs[0];
                    $sc->setSoldeDebut($soldeDebut);
                    $sc->setSoldeFin($soldeFin);
                    $sc->setPeriodeD1($dateDebut);
                    $sc->setPeriodeF1($dateFin);

                    $em->flush();
                }
            }




            /** @var Separation[] $separtions */
            $separtions = $this->getDoctrine()
                ->getRepository('AppBundle:Separation')
                ->findBy(['image' => $imgRel]);

            if(count($separtions)){
                $separtion = $separtions[0];

                $historiqueCategorie = new HistoriqueCategorie();
                $historiqueCategorie->setImage($imgRel);
                $historiqueCategorie->setCategorie($separtion->getCategorie());
                $historiqueCategorie->setSouscategorie($separtion->getSouscategorie());
                $historiqueCategorie->setOperateur($this->getUser());
                $historiqueCategorie->setMotif('Releve Sobank');
                $historiqueCategorie->setDateModification(new \DateTime('now'));

                $em->persist($historiqueCategorie);
                $em->flush();

                $doublon = $this->getDoctrine()
                    ->getRepository('AppBundle:Souscategorie')
                    ->find(3);

                $separtion->setSouscategorie($doublon);
                $em->flush();
            }

            return new JsonResponse([
                'type' => 'success',
                'message' => 'Enregistrement effecutée',
                'image' => $imgSobank->getId()
            ]);
        }

        return new JsonResponse([
            'type' =>'error',
            'message' => 'Image introuvable',
            'image' => $imgSobank->getId()
        ]);
    }

    public function releveDossierDetailsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {


            $post = $request->request;

            $banquecompteid = $post->get('banquecompteid');
            $exercice = $post->get('exercice');
            $soldedebut = $post->get('soldedebut');

            $dscan = $post->get('datescan');

            $dateScan = \DateTime::createFromFormat('Y-m-d', $dscan);
            if($dateScan === false) {
                $dateScan = null;
            }
            else{
                $dateScan = $dateScan->setTime(0,0,0);
            }



            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);

            /** @var Releve[] $releves */
            $releves = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->getRelevesByBanqueCompte($banquecompte, $exercice, $dateScan);
            $rows = [];

            $progression = floatval($soldedebut);

            //alaina par images
            $selectedImages = [];
            $ecarts = [];


            foreach ($releves as $releve) {


                $imageTmp = $releve->getImage();
                if(!in_array($imageTmp, $selectedImages)){
                    $selectedImages[] = $imageTmp;

                    $relTmps = $this->getDoctrine()
                        ->getRepository('AppBundle:Releve')
                        ->getRelevesByImage($imageTmp);

                    $scTmps = $this->getDoctrine()
                        ->getRepository('AppBundle:SaisieControle')
                        ->findBy(['image' => $imageTmp]);

                    if(count($scTmps) > 0){
                        $scTmp = $scTmps[0];

                        $progressionTmp = $scTmp->getSoldeDebut();

                        foreach ($relTmps as $relTmp){
                            $progressionTmp +=  -floatval($relTmp->getDebit()) + floatval($relTmp->getCredit());;
                        }

                        if(abs(floatval($progressionTmp) - floatval($scTmp->getSoldeFin())) >= 0.000001){
                            $ecarts[] = ['image' => $imageTmp->getNom(),
                                'imageid' => $imageTmp->getId(),
                                'progression' => $progressionTmp,
                                'soldeDebut' => $scTmp->getSoldeDebut(),
                                'soldeFin' => $scTmp->getSoldeFin(),
                                'ecart' => number_format(abs($progressionTmp - $scTmp->getSoldeFin()), 2, '.', ' ')
                            ];
                        }
                    }
                }

                $attente = $releve->getPccAttente();

                $progression += -floatval($releve->getDebit()) + floatval($releve->getCredit());

                $rows[] = ['id' => $releve->getId(),
                    'cell' => [
                        'r-date' => $releve->getDateReleve()->format('Y-m-d'),
                        'r-compte' => ($attente === null) ? '' : $attente->getCompte(),
                        'r-libelle' => $releve->getLibelle(),
                        'r-debit' => $releve->getDebit(),
                        'r-credit' => $releve->getCredit(),
                        'r-progression' => $progression,
                        'r-piece' => ($releve->getPieces() === null)? '' : $releve->getPieces(),
                        'r-tiers' => ($releve->getTiers() == null)? '': $releve->getTiers(),
                        'r-commentaire' => '',
                        'r-action' => '<i class="fa fa-save icon-action r-action" title="Enregistrer"></i>'
                    ]
                ];
            }
            return new JsonResponse(array(
                'rows' => $rows,
                'progressionGenerale' => true,
                'progression' => $progression,
                'ecarts' => $ecarts
            ));
        }
        throw new AccessDeniedHttpException('Accès refusé');
    }

    public function releveDossierDetailsEditAction(Request $request, $imageid)
    {

        $post = $request->request;

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $releveId = $post->get('id');
        $releve = $this->getDoctrine()
            ->getRepository('AppBundle:Releve')
            ->find($releveId);

        $dateReleve = \DateTime::createFromFormat('d/m/Y', $post->get('r-date'));
        $pccAttenteId = $post->get('r-compte');
        $pccAttente = null;

        if($pccAttenteId) {
            $pccAttente = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($pccAttenteId);
        }

        $debit = $post->get('r-debit');
        if ($debit == '')
            $debit = 0;
        $credit = $post->get('r-credit');
        if ($credit == '')
            $credit = 0;
        $libelle = $post->get('r-libelle');
        if ($libelle == '')
            $libelle = null;


        $commentaire = $post->get('r-commentaire');
        if ($commentaire == '')
            $commentaire = null;

        $piece = $post->get('r-piece');
        if($piece == '')
            $piece = null;

        $tiers = $post->get('r-tiers');
        if($tiers == '')
            $tiers = null;


        $relevesource = $this->getDoctrine()
            ->getRepository('AppBundle:ReleveSource')
            ->find(1);

        $em = $this->getDoctrine()
            ->getManager();

        if ($releve) {
            $releve->setDateReleve($dateReleve);
            $releve->setPccAttente($pccAttente);
            $releve->setDebit($debit);
            $releve->setCredit($credit);
            $releve->setLibelle($libelle);
            $releve->setCommentaire($commentaire);
            $releve->setTiers($tiers);
            $releve->setPieces($piece);

            $em->flush();


            return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée', 'action' => 'update']);

        } else {
            if ($releveId === 'new_row') {
                if ($image) {

                    $saisiecontroles = $this->getDoctrine()
                        ->getRepository('AppBundle:SaisieControle')
                        ->findBy(['image' => $image]);

                    if(count($saisiecontroles) == 0)
                        return new JsonResponse(['type' => 'error', 'action' => 'insert', 'message' => 'saisie introuvable']);

                    $saisiecontrole = $saisiecontroles[0];

                    $banqueCompte = $saisiecontrole->getBanqueCompte();

                    if($banqueCompte === null)
                        return new JsonResponse(['type' => 'error', 'action' => 'insert', 'message' => 'banque compte introuvable']);

                    $releve = new Releve();
                    $releve->setImage($image);
                    $releve->setDateReleve($dateReleve);
                    $releve->setPccAttente($pccAttente);
                    $releve->setDebit($debit);
                    $releve->setCredit($credit);
                    $releve->setLibelle($libelle);
                    $releve->setCommentaire($commentaire);
                    $releve->setReleveSource($relevesource);
                    $releve->setBanqueCompte($banqueCompte);
                    $releve->setTiers($tiers);
                    $releve->setPieces($piece);

                    $em->persist($releve);

                    $em->flush();

                    $em->refresh($releve);

                    return new JsonResponse(['type' => 'success',
                        'message' => 'ligne inserée',
                        'id'=> $releve->getId(),
                        'action' => 'insert'
                    ]);

                } else {
                    throw  new NotFoundHttpException('Image non trouvée');
                }
            }
        }
        throw new NotFoundHttpException('Relevé non trouvé');

    }

    public function restoreReleveDoublonAction(Request $request){

        if(!$request->isXmlHttpRequest()){
            throw new AccessDeniedHttpException('Accès refusé');
        }

        $post =$request->request;

        $banquecompteid = $post->get('banquecompteid');
        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);

        if($banquecompte === null)
            return new JsonResponse(['type' => 'error', 'message' => 'Banque compte introuvable']);

        $exercice = $post->get('exercice');

        /** @var Releve[] $doublons */
        $doublons = $this->getDoctrine()
            ->getRepository('AppBundle:Releve')
            ->getDoublonRelevesByBanqueCompte($banquecompte, $exercice);

        $em = $this->getDoctrine()
            ->getManager();
        foreach ($doublons as $doublon){
            $doublon->setOperateur(null);


            $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];
            foreach ($bankEntities as $bankEntity){
                /** @var SaisieControle[] $scs */
                $scs = $this->getDoctrine()
                    ->getRepository('AppBundle:' . $bankEntity)
                    ->findBy(['image' => $doublon->getImage()]);

                if (count($scs) > 0) {
                    $sc = $scs[0];
                    $soldeFin = $sc->getSoldeFin()  - ($doublon->getDebit() - $doublon->getCredit());
                    $sc->setSoldeFin($soldeFin);
                }
            }
        }

        $em->flush();


        return new JsonResponse(['type' => 'success', 'message' => 'Restauration effectuée']);
    }

    public function controleDoublonReleveAction(Request $request, $banquecompteid, $exercice)
    {
        if ($request->isXmlHttpRequest()) {

            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);

            /** @var Releve[] $releves */
            $releves = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->getRelevesByBanqueCompte($banquecompte, $exercice);
            $rows = [];
            $doublons = [];
            $finded = [];

            for($i = 0; $i < count($releves); $i++) {
                $reli = $releves[$i];

                for ($j = $i + 1; $j < count($releves); $j++) {

                    if(in_array($j, $finded))
                        continue;

                    $relj = $releves[$j];

                    if (
                        $reli->getDateReleve() == $relj->getDateReleve()
                        && $reli->getDebit() == $relj->getDebit()
                        && $reli->getCredit() == $relj->getCredit()
                        && $reli->getLibelle() === $relj->getLibelle()
                    ) {
                        $doublons[] = ['index' => $j, 'reference' => $i];
                        $finded[] = $j;
                    }
                }
            }

            for ($i = 0; $i<count($releves); $i++){

                $reli = $releves[$i];
                $commentaire = '';
                $reference = '';
                for ($j = 0; $j<count($doublons); $j++){
                    if($doublons[$j]['index'] === $i){
                        $commentaire = 'Doublon';
                        $reference = $releves[$doublons[$j]['reference']]->getId();
                    }
                    else if($doublons[$j]['reference'] === $i){
                        $commentaire = 'Doublon';
                        $reference = $releves[$doublons[$j]['index']]->getId();
                    }
                }


                $rows[] = ['id' => $reli->getId(),
                    'cell' => [
                        'dr-image' => $reli->getImage()->getNom(),
                        'dr-date' => $reli->getDateReleve()->format('Y-m-d'),
                        'dr-libelle' => $reli->getLibelle(),
                        'dr-debit' => $reli->getDebit(),
                        'dr-credit' => $reli->getCredit(),
                        'dr_commentaire' => $commentaire,
                        'dr_reference' => $reference,
                        'dr-action' => '<i class="fa fa-trash icon-action dr-action" title="Supprimer"></i>'
                    ]
                ];
            }

            $caption = ' (Aucun doublon trouvé) ';
            if(count($doublons) > 0){
                if(count($doublons) === 1){
                    $caption = ' (1 doublon trouvé) ';
                }
                else{
                    $caption = 'CONTROLE DOUBLON RELEVÉS ('.count($doublons). ' trouvés) ';
                }
            }

            return new JsonResponse([
                'rows' => $rows,
                'caption' => $caption
            ]);
        }
        throw new AccessDeniedHttpException('Accès refusé');
    }

    public function deleteReleveAction(Request $request){
        if($request->isXmlHttpRequest()){
            $releveid = $request->request->get('id');

            $releve = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->find($releveid);

            if($releve){
                $em = $this->getDoctrine()
                    ->getManager();

                $releve->setOperateur($this->getUser());

                $bankEntities = ['SaisieControle', 'Imputation', 'ImputationControle'];
                foreach ($bankEntities as $bankEntity){
                    /** @var SaisieControle[] $scs */
                    $scs = $this->getDoctrine()
                        ->getRepository('AppBundle:' . $bankEntity)
                        ->findBy(['image' => $releve->getImage()]);

                    if (count($scs) > 0) {
                        $sc = $scs[0];
                        $soldeFin = $sc->getSoldeFin()  + ($releve->getDebit() - $releve->getCredit());
                        $sc->setSoldeFin($soldeFin);
                    }
                }
                $em->flush();
                return new JsonResponse(['type' => 'success', 'message' => 'relevé supprimé avec succès']);
            }
            return new JsonResponse(['type' => 'error', 'message' => 'relevé introuvable']);

        }
        throw new AccessDeniedHttpException('Accsè refusé');
    }




    public function deleteMultipleReleveAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $selectedIds = $request->request->get('selectedids');

        $em = $this->getDoctrine()
            ->getManager();

        foreach ($selectedIds as $selectedId){
            $releve = $this->getDoctrine()
                ->getRepository('AppBundle:Releve')
                ->find($selectedId);

            $releve->setOperateur($this->getUser());
        }

        $em->flush();

        return new JsonResponse(['type' => 'success', 'message' => 'relevé(s) supprimé(s) avec succès']);
    }

    public function saveNumCbAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $banquecompteid = $post->get('banquecompteid');
        $numcb = $post->get('numcb');

        if($banquecompteid === '' || $numcb === ''){
            return new JsonResponse(['type' => 'error', 'message' => 'banquecompte ou numcb introuvable']);
        }

        $banquecompte = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueCompte')
            ->find($banquecompteid);

        if($banquecompte === null){
            return new JsonResponse(['type' => 'error', 'message' => 'Banque compte introuvable']);
        }

        $cbs = $this->getDoctrine()
            ->getRepository('AppBundle:CarteBleuBanqueCompte')
            ->getCbByBanqueCompte($banquecompte, $numcb);

        if(count($cbs) > 0){
            return new JsonResponse(['type' => 'warning', 'message' => 'Ce numero de compte existe deja']);
        }

        $em = $this->getDoctrine()
            ->getManager();

        $cb = new CarteBleuBanqueCompte();
        $cb->setBanqueCompte($banquecompte);
        $cb->setNumCb($numcb);

        $em->persist($cb);
        $em->flush();

        $em->refresh($cb);

        return new JsonResponse([
            'type' => 'success',
            'message' => 'insertion effectuée',
            'id'=>$cb->getId()
        ]);
    }

    public function saveBanqueCompteAction(Request $request, $isIban){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $banqueid = $post->get('banqueid');

        $ibanval = null;

        if($isIban === 0) {
            $numcompte = $post->get('numcompte');
        }
        else{
            $ibanval = $post->get('iban');
            $numcompte = substr($ibanval, 4, strlen($ibanval));
        }

        $dossierid = $post->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $banque = $this->getDoctrine()
            ->getRepository('AppBundle:Banque')
            ->find($banqueid);

        if($dossier) {

            if($banque === null){
                $codeBanque = substr($numcompte, 0 ,5);

                $banque = $this->getDoctrine()
                    ->getRepository('AppBundle:Banque')
                    ->getBanqueByCode($codeBanque);
            }

            if(!$banque)
                return new JsonResponse(['type' => 'error', 'message' => 'Code banque introuvable']);

            /** @var BanqueCompte[] $banqueComptes */
            $banqueComptes = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->getBanqueComptes($dossier, $numcompte);

            if (count($banqueComptes) > 0) {
                return new JsonResponse(
                    [
                    'type' => 'warning',
                    'message' => 'Le numero de compte existe déjà',
                    'id' => $banqueComptes[0]->getId()
                    ]
                );
            }

            $em = $this->getDoctrine()
                ->getManager();

            $banqueCompte = New BanqueCompte();
            $banqueCompte->setBanque($banque);
            $banqueCompte->setDossier($dossier);

            if(intval($isIban) === 1)
                $banqueCompte->setIban($ibanval);

            $banqueCompte->setNumcompte($numcompte);
            $banqueCompte->setStatus(1);

            $em->persist($banqueCompte);
            $em->flush();

            $em->refresh($banqueCompte);

            return new JsonResponse([
                'type' => 'success',
                'message' => 'Numero de compte inséré avec succès',
                'id' => $banqueCompte->getId()
            ]);

        }

        return new JsonResponse(['type' => 'error', 'message' => 'Dossier introuvable']);
    }

    public function releveImportAction(Request $request)
    {
        $file = $request->files->get('upload');
        $status = array('status' => "success", "fileUploaded" => false);
        if (!is_null($file)) {
            $filename = uniqid() . "." . $file->getClientOriginalExtension();

            $path = $this->get('kernel')->getRootDir() . '/../web/ocr';
            $file->move($path, $filename); // move the file to a path
            $path_file = $path . '/' . $filename;
            $excelObj = $this->get('phpexcel')->createPHPExcelObject($path_file);
            try {
                $sheet = $excelObj->getActiveSheet()->toArray(null, true, false, true);

                $imageList = [];
                $em = $this->getDoctrine()->getManager();

                foreach ($sheet as $i => $row) {
                    if (strlen(trim($row['J'])) > 7) {

                        $nomTmp = trim($row['J']);
                        $images = $this->getDoctrine()
                            ->getRepository('AppBundle:Image')
                            ->findBy(array('nom' => $nomTmp));

                        if (count($images) > 0) {
                            $imageTmp = $images[0];

                            $dossier = $imageTmp->getLot()->getDossier();

                            $releveSource = $this->getDoctrine()
                                ->getRepository('AppBundle:ReleveSource')
                                ->find(1);

                            $dateReleve = null;

                            $saisieControles = $this->getDoctrine()
                                ->getRepository('AppBundle:SaisieControle')
                                ->findBy(['image' => $imageTmp]);

                            $banqueCompte = null;

                            if(count($saisieControles) > 0){
                                $saisieControle = $saisieControles[0];
                                $banqueCompte = $saisieControle->getBanqueCompte();
                            }

                            if($banqueCompte === null) {
                                $banqueComptes = $this->getDoctrine()
                                    ->getRepository('AppBundle:BanqueCompte')
                                    ->findBy(array('numcompte' => $row['E'], 'dossier' => $dossier));

                                if (count($banqueComptes) > 0) {
                                    $banqueCompte = $banqueComptes[0];
                                }
                            }

                            if($banqueCompte === null){
                                continue;
                            }

                            $libelle = $row['L'];
                            $debit = $this->floatvalue($row['M']);
                            $credit = $this->floatvalue($row['N']);

                            $typeTiers = $this->getDoctrine()
                                ->getRepository('AppBundle:TypeTiers')
                                ->find(19);

                            if (trim($row['K']) != '') {

                                if(strpos($row['K'], '/') !== false) {
                                    $datetmp = explode('/', $row['K']);
                                    $day = $datetmp[1];
                                    $month = $datetmp[0];
                                    $year = $datetmp[2];

                                    $dateReleve = \DateTime::createFromFormat('d/m/Y', $day . '/' . $month . '/' . $year);
                                }
                                else{
                                    $dateReleve = \PHPExcel_Shared_Date::ExcelToPHPObject($row['K']);
                                }

                            } else {
                                continue;
                            }

                            if (!in_array($imageTmp, $imageList)) {
                                $imageList[] = $imageTmp;

                                //fafana daholo aloha ny relevés an'ilay image
                                $releves = $this->getDoctrine()
                                    ->getRepository('AppBundle:Releve')
                                    ->findBy(array('image' => $imageTmp));

                                foreach ($releves as $releve) {
                                    $em->remove($releve);

                                }
                            }

                            //Inserer-na ilay releves
                            $releve = new Releve();

                            $releve->setImage($imageTmp);
                            $releve->setBanqueCompte($banqueCompte);
                            $releve->setLibelle($libelle);
                            $releve->setDebit($debit);
                            $releve->setCredit($credit);
                            $releve->setDateReleve($dateReleve);
                            $releve->setTypeTiers($typeTiers);
                            $releve->setReleveSource($releveSource);

                            $em->persist($releve);

                        }
                        continue;
                    }
                }

                $em->flush();

                //Esorina ao anaty panier 'zay vita importation
                /** @var Image[] $imageList */
                foreach ($imageList as $image){

                    $finished = false;

                    /** @var SaisieControle[] $scs */
                    $scs = $this->getDoctrine()
                        ->getRepository('AppBundle:SaisieControle')
                        ->findBy(['image' => $image]);

                    if(count($scs) > 0){
                        $sc = $scs[0];

                        $banqueCompte = $sc->getBanqueCompte();

                        if($banqueCompte !== null){
                            $soldeDebut = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getSolde($banqueCompte->getId(), $image->getExercice());

                            $soldeFin = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getSolde($banqueCompte->getId(), $image->getExercice(), false);

                            $mouvements = $this->getDoctrine()
                                ->getRepository('AppBundle:Image')
                                ->getMouvement($image->getExercice(), $banqueCompte->getId());

                            $ecart = $soldeFin - ($soldeDebut + $mouvements);

                            if((float)$ecart <0.001){
                                $finished = true;
                            }
                        }
                    }

                    if($finished) {

                        $image->setValider(100);

                        $etapeTraitement = $this->getDoctrine()
                            ->getRepository('AppBundle:EtapeTraitement')
                            ->find(26);

                        $paniers = $this->getDoctrine()
                            ->getRepository('AppBundle:Panier')
                            ->findBy(['image' => $image, 'etapeTraitement' => $etapeTraitement]);


                        foreach ($paniers as $panier) {
                            $panier->setFini(1);
                        }
                    }
                }
                $em->flush();

            } catch (\PHPExcel_Exception $e) {
                return new JsonResponse(['type' => 'Error', 'message' => $e->getMessage()]);
            }



        }
        return new JsonResponse(['type' => 'success', 'message' => 'importation effectuée']);
    }

    public function dupliqueAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Souscategorie[] $souscategories */
        $souscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->getSouscategoriesByCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find(16)
            );

        return $this->render('BanqueBundle:Banque/saisie:saisie_duplique.html.twig',
            array('souscategories' => $souscategories));
    }

    public function dupliqueEditAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageid = $post->get('imageid');
        $souscategorieid = $post->get('souscategorieid');
        $soussouscategorieid = $post->get('soussouscategorieid');

        $souscategorie = null;
        $soussouscategorie = null;

        if($souscategorieid !== ''){
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieid);
        }

        if($soussouscategorieid !== ''){
            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find($soussouscategorieid);
        }

        $operateur = $this->getUser();

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);


        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);

        /** @var ImageATraiter $imageAtraiter */
        $imageAtraiter = $this->getDoctrine()
            ->getRepository('AppBundle:ImageATraiter')
            ->getImageAtraiterByImage($image);

        $em = $this->getDoctrine()
            ->getManager();

        $newImage = new Image();
        $newImage
            ->setOriginale($image->getNom())
            ->setExercice($image->getExercice())
            ->setDownload(new \DateTime('now'))
            ->setValider(0)
            ->setNbpage($image->getNbpage())
            ->setSourceImage($image->getSourceImage())
            ->setStatus($image->getStatus())
            ->setLot($image->getLot())
            ->setNumPage($image->getNumPage())
            ->setANePasTraiter($image->getANePasTraiter())
            ->setARemonter($image->getARemonter())
            ->setDecouper($image->getDecouper())
            ->setNomTemp($image->getNomTemp())
            ->setNumerotationLocal(1)
            ->setRenommer(1)
            ->setSaisie1($image->getSaisie1())
            ->setSaisie2($image->getSaisie2())
            ->setCtrlSaisie($image->getCtrlSaisie())
            ->setImputation($image->getImputation())
            ->setCtrlImputation($image->getCtrlImputation())
            ->setExtImage($image->getExtImage())
        ;

        $em->persist($newImage);
        $em->flush();
        $em->refresh($newImage);

       
        if($separation !== null) {
            $newSeparation = new Separation();
            $newSeparation->setImage($image)
                ->setCategorie($this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find(16))
                ->setSouscategorie($souscategorie)
                ->setSoussouscategorie($soussouscategorie)
                ->setOperateur($this->getUser());

            $em->persist($newSeparation);
        }

        if($imageAtraiter !== null){
            $newImageATraiter = new ImageATraiter();
            $newImageATraiter->setImage($newImage)
                ->setSaisie1($imageAtraiter->getSaisie1())
                ->setSaisie2($imageAtraiter->getSaisie2())
                ->setStatus($imageAtraiter->getStatus())
                ->setDecouper($imageAtraiter->getDecouper());

            $em->persist($newImageATraiter);
        }

        $imageDuplique = new ImageDuplique();
        $imageDuplique->setImage($image);
        $imageDuplique->setOperateur($operateur);
        $imageDuplique->setDateDuplication(new \DateTime('now'));
        $imageDuplique->setSouscategorie($souscategorie);
        $imageDuplique->setSoussouscategorie($soussouscategorie);

        $em->persist($imageDuplique);
        $em->flush();

        $dateScanFormated = $image->getLot()
            ->getDateScan()
            ->format('Ymd');

        $imageService = new ImageService($this->getDoctrine()->getManager());
        $oldFile = $imageService->getUrl($image->getId());

        $fileName = $newImage->getNom() . '.' . $newImage->getExtImage();

        $localdir = $this->get('kernel')->getRootDir() . '/../web/IMAGES/' . $dateScanFormated . '/';

        if (!file_exists($localdir)) {
            mkdir($localdir, 0777);
        }

        $newFile = $localdir . $fileName;

        if (!file_exists($newFile)) {
            $imageService->copySecureFile($oldFile, $newFile);
        }


        $ftp_server = "ns315229.ip-37-59-25.eu";
        $ftp_user_name = "images";
        $ftp_user_pass = "wAgz37^8";
        $destination_file = $newImage->getLot()->getDateScan()->format('Ymd') . '/' . $newImage->getNom() . '.pdf';

        $source_file = $newFile;

        $conn_id = ftp_connect($ftp_server);
        ftp_pasv($conn_id, true);


        $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

        if ((!$conn_id) || (!$login_result)) {
            return new JsonResponse(array('type' => 'error', 'message' => 'FTP connection failed'));
        }

        $source_file = explode('.pdf', $source_file)[0];

        $source_file .= '.pdf';

        ftp_pasv($conn_id, true);
        $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

        if (!$upload) {
            return new JsonResponse(array('type' => 'error', 'message' => 'FTP upload failed'));
        }

        ftp_close($conn_id);


        return new JsonResponse(['type' => 'success', 'message' => 'image dupliquée avec succès']);

    }


    function recategoriesationFiltreAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $souscategorieid = $post->get('souscategorie');
        $soussouscategorieid = $post->get('soussouscategorie');
        $exercice = $post->get('exercice');

        $souscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find($souscategorieid);

        if ($souscategorie === null)
            throw new NotFoundHttpException('Sous categorie introuvable');

        $soussouscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->find($soussouscategorieid);


        $client = '';
        $dossier = '';

        return new JsonResponse(['client' => $client, 'dossier' => $dossier]);

    }




    function floatvalue($val){
        $val = str_replace(",","",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }

    function combine_pdf($outputName,$fileArray)
    {

        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";

        foreach($fileArray as $file)
        {
            $cmd .= $file." ";
        }
        $result = shell_exec($cmd);

        return $result;

    }

    function setTitle(Dossier $dossier, $exercice){
        if($dossier->getCloture() !== null) {
            return $dossier->getSite()->getClient()->getNom() . ' | ' .
                $dossier->getNom() . ' | ' . $exercice . ' | '. $this->mois[$dossier->getCloture()] ;
        }

        return $dossier->getSite()->getClient()->getNom() . ' | ' .
                $dossier->getNom() . ' | ' . $exercice;

    }

    function sortFunction( $a, $b ) {
        return strtotime($a) - strtotime($b);
    }

}