<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 29/05/2019
 * Time: 15:44
 */

namespace TenueBundle\Controller;



use AppBundle\Entity\CaisseNature;
use AppBundle\Entity\CarteBleuBanqueCompte;
use AppBundle\Entity\Categorie;
use AppBundle\Entity\Cerfa;
use AppBundle\Entity\Client;
use AppBundle\Entity\CodeAnalytique;
use AppBundle\Entity\ControleRegleEcheance;
use AppBundle\Entity\Devise;
use AppBundle\Entity\DeviseTaux;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Ecriture;
use AppBundle\Entity\HistoriqueCategorie;
use AppBundle\Entity\Image;
use AppBundle\Entity\ImageATraiter;
use AppBundle\Entity\Imputation;
use AppBundle\Entity\ImputationControle;
use AppBundle\Entity\ImputationControleRegleEcheance;
use AppBundle\Entity\ImputationRegleEcheance;
use AppBundle\Entity\InstructionDossier;
use AppBundle\Entity\InstructionSaisie;
use AppBundle\Entity\Lot;
use AppBundle\Entity\MentionManuscrite;
use AppBundle\Entity\MethodeComptable;
use AppBundle\Entity\ModeReglement;
use AppBundle\Entity\Nature;
use AppBundle\Entity\NdfTypeVehicule;
use AppBundle\Entity\NdfUtilisateur;
use AppBundle\Entity\Organisme;
use AppBundle\Entity\Pays;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\Pcg;
use AppBundle\Entity\ReglePaiementDossier;
use AppBundle\Entity\ResponsableScriptura;
use AppBundle\Entity\Saisie1;
use AppBundle\Entity\Saisie1RegleEcheance;
use AppBundle\Entity\Saisie2;
use AppBundle\Entity\Saisie2RegleEcheance;
use AppBundle\Entity\SaisieControle;
use AppBundle\Entity\Separation;
use AppBundle\Entity\Souscategorie;
use AppBundle\Entity\Sousnature;
use AppBundle\Entity\Soussouscategorie;
use AppBundle\Entity\SoussouscategorieOrganisme;
use AppBundle\Entity\TdCaisseBilanPcc;
use AppBundle\Entity\TdCaisseResultatPcc;
use AppBundle\Entity\TdCaisseResultatPcg;
use AppBundle\Entity\TdNdfBilanPcc;
use AppBundle\Entity\TdNdfSousnaturePcc;
use AppBundle\Entity\TdTvaPcc;
use AppBundle\Entity\TdTvaPcg;
use AppBundle\Entity\Tiers;
use AppBundle\Entity\TvaImputation;
use AppBundle\Entity\TvaImputationControle;
use AppBundle\Entity\TvaSaisie1;
use AppBundle\Entity\TvaSaisie2;
use AppBundle\Entity\TvaSaisieControle;
use AppBundle\Entity\TvaTaux;
use AppBundle\Entity\TypeVehicule;
use AppBundle\Entity\TypeVente;
use AppBundle\Entity\Vehicule;
use AppBundle\Entity\VehiculeMarque;
use AppBundle\Functions\CustomPdoConnection;
use ImageBundle\Service\ImageService;
use PHPExcel_Settings;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use TenueBundle\Service\LogService;
use AppBundle\Controller\Fonction;
use TenueBundle\Service\StatusImageService;


class SaisieUniverselleController extends Controller
{

    private $regleEntities  =  [
        'Saisie1RegleEcheance',
        'Saisie2RegleEcheance',
        'ControleRegleEcheance',
        'ImputationRegleEcheance',
        'ImputationControleRegleEcheance'
    ];

    private   $saisieEntities = [
        'Saisie1',
        'Saisie2',
        'SaisieControle',
        'Imputation',
        'ImputationControle'
    ];

    private $tvaEntities = [
        'TvaSaisie1',
        'TvaSaisie2',
        'TvaSaisieControle',
        'TvaImputation',
        'TvaImputationControle'
    ];

    public function indexAction()
    {
        /** @var Client[] $clients */
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->findBy(['status' => 1], ['nom' => 'ASC']);

        /** @var ResponsableScriptura[] $responsables */
        $responsables = $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->getSubDirections();

        $categories = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->getAllCategories();

        /** @var Categorie[] $importCategories */
        $importCategories = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->getCategoriesByCode(['CODE_CLIENT', 'CODE_FRNS']);

        /** @var VehiculeMarque[] $vehiculeMarques */
        $vehiculeMarques = $this->getDoctrine()
            ->getRepository('AppBundle:VehiculeMarque')
            ->findBy([], ['libelle' => 'ASC']);

        /** @var TypeVehicule[] $typeVehicules */
        $typeVehicules = $this->getDoctrine()
            ->getRepository('AppBundle:TypeVehicule')
            ->findBy([], ['libelle' => 'ASC']);

        /** @var NdfTypeVehicule[] $ndfTypeVehicules */
        $ndfTypeVehicules = $this->getDoctrine()
            ->getRepository('AppBundle:NdfTypeVehicule')
            ->findBy([], ['libelle' => 'ASC']);

        $carburants = $this->getDoctrine()
            ->getRepository('AppBundle:Carburant')
            ->findAll();

        return $this->render('@Tenue/SaisieUniverselle/index.html.twig',
            [
                'clients' => $clients,
                'responsables' => $responsables,
                'categories' => $categories,
                'vehiculeMarques' => $vehiculeMarques,
                'typeVehicules' => $typeVehicules,
                'ndfTypeVehicules' => $ndfTypeVehicules,
                'carburants' => $carburants,
                'importCategories' => $importCategories
            ]
        );
    }

    public function dossierAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $clientid = $request->query->get('clientid');

        $client = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->find($clientid);

        if($client === null)
            throw new NotFoundHttpException('Client introuvable');

        /** @var Dossier[] $dossiers */
        $dossiers = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->getAllDossierObject($client);

        return $this->render('@Tenue/SaisieUniverselle/optionDossier.html.twig', ['dossiers' => $dossiers]);
    }

    public function exerciceAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $dossierid = $request->query->get('dossierid');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);
        if($dossier === null)
            throw new NotFoundHttpException('Dossier introuvable');


        $exercices = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getExercicesByDossier($dossier);

        return $this->render('TenueBundle:SaisieUniverselle:optionExercice.html.twig', ['exercices' => $exercices]);

    }

    public function souscategoriesAction(Request $request, $toutes){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $categorieid = $request->query->get('categorieid');
        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find($categorieid);
        if($categorie === null)
            throw new NotFoundHttpException('Categorie introuvable');

        /** @var Souscategorie[] $souscategories */
        $souscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->getSouscategoriesByCategorie($categorie);

        return $this->render('TenueBundle:SaisieUniverselle:optionSouscategorie.html.twig', [
            'souscategories' => $souscategories,
            'toutes' => $toutes
        ]);

    }

    public function soussouscategoriesAction(Request $request, $toutes){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $souscategorieid = $request->query->get('souscategorieid');
        $tous = false;
        if(intval($souscategorieid) === -1){
            $tous = true;
        }
        $souscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find($souscategorieid);

        if($souscategorie === null && !$tous)
            throw new NotFoundHttpException('Souscategorie introuvable');

        $soussouscategories = [];
        if(!$tous)
        /** @var Soussouscategorie[] $soussouscategories */
        $soussouscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->getSoussouscategorieBySouscategories([$souscategorie]);


        return $this->render('TenueBundle:SaisieUniverselle:optionSoussouscategorie.html.twig', [
            'soussouscategories' => $soussouscategories,
            'toutes' => $toutes
        ]);

    }

    public function selectOrganismeAction(Request $request, $select){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $organismeid = $request->query->get('organismeid');
        $categorieid = $request->query->get('categorieid');

        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find($categorieid);

        $organisme = $this->getDoctrine()
            ->getRepository('AppBundle:Organisme')
            ->find($organismeid);

        /** @var Nature[] $natures */
        $natures = [];

        /** @var Sousnature[] $sousnatures */
        $sousnatures = [];

        /** @var Souscategorie[] $souscategories */
        $souscategories = [];

        /** @var Soussouscategorie[] $soussouscategories */
        $soussouscategories = [];

        if($organisme !== null){
            /** @var SoussouscategorieOrganisme[] $soussosucategorieOrganismes */
            $soussosucategorieOrganismes = $this->getDoctrine()
                ->getRepository('AppBundle:SoussouscategorieOrganisme')
                ->findBy(['organisme' => $organisme]);

            foreach ($soussosucategorieOrganismes as $soussosucategorieOrganisme){
                /** @var Soussouscategorie $souscategorieTmp */
                $souscategorieTmp = $soussosucategorieOrganisme->getSoussouscateg();
                if($souscategorieTmp !== null){
                    if($souscategorieTmp->getActif() === 1) {
                        $sousnatureTmp = $souscategorieTmp->getSousnature();
                        if ($sousnatureTmp !== null) {
                            if (!in_array($sousnatureTmp->getNature(), $natures))
                                $natures[] = $sousnatureTmp->getNature();

                            if (!in_array($sousnatureTmp, $sousnatures))
                                $sousnatures[] = $sousnatureTmp;
                        }

                        if (!in_array($souscategorieTmp, $soussouscategories))
                            $soussouscategories[] = $souscategorieTmp;

                        if (!in_array($souscategorieTmp->getSouscategorie(), $souscategories))
                            $souscategories[] = $souscategorieTmp->getSouscategorie();
                    }
                }
            }
        }
        if(count($natures) === 0){
            $natures = $this->getDoctrine()
                ->getRepository('AppBundle:Nature')
                ->findBy(['categorie' => $categorie], ['libelle' => 'ASC']);
        }

        if(count($souscategories) === 0){
            $souscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->getSouscategoriesByCategorie($categorie);
        }

        switch ($select){
            case 'nature':
                return $this->render('@Tenue/SaisieUniverselle/optionNature.html.twig', ['natures' => $natures]);
                break;
            case 'sousnature':
                return $this->render('@Tenue/SaisieUniverselle/optionSousnature.html.twig', ['sousnatures' => $sousnatures]);
                break;
            case 'souscategorie':
                return $this->render('TenueBundle:SaisieUniverselle:optionSouscategorie.html.twig',
                    ['souscategories' => $souscategories, 'toutes' => 0]
                );
                break;
            case 'soussouscategorie':
                return $this->render('TenueBundle:SaisieUniverselle:optionSoussouscategorie.html.twig',
                    ['soussouscategories' => $soussouscategories, 'toutes' => 0]
                );
                break;
        }

        throw new NotFoundHttpException('Page introuvable');

    }

    public function sousNatureAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $natureid = $request->query->get('natureid');
        $nature = $this->getDoctrine()
            ->getRepository('AppBundle:Nature')
            ->find($natureid);
        /** @var Sousnature[] $sousnatures */
        $sousnatures = [];
        if($nature !== null){
            $sousnatures = $this->getDoctrine()
                ->getRepository('AppBundle:Sousnature')
                ->findBy(['nature' => $nature, 'actif' => 1], ['libelle' => 'ASC']);
        }

        $jqgrid  = $request->query->get('jqgrid');

        if($jqgrid === null)
            $jqgrid = false;

        return $this->render('@Tenue/SaisieUniverselle/optionSousnature.html.twig',
            ['sousnatures' => $sousnatures, 'jqgrid' =>$jqgrid]);

    }



    public function statutAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossier = $get->get('dossierid');

        $exercice = $get->get('exercice');

        $recue = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'recue', -1, -1, -1, false, true);


        $categorisee = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'categorisee', -1, -1, -1, false, true);

        $saisie = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'saisie', -1, -1, -1, false, true);


        $aImputer = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'aimputer', -1, -1, -1, false, true);

        $imputee = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'imputee', -1, -1, -1, false, true);

        $nonLettreeImputee = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'nonlettreeimputee', -1, -1, -1, false, true);

        $nonLettreeRevisee = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'nonlettreerevisee', -1, -1, -1, false, true);


        $revisee = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'revisee', -1, -1, -1, false, true);


        $revue = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossier, $exercice, 'revue', -1, -1,-1, false, true);

        return $this->render('@Tenue/SaisieUniverselle/optionStatut.html.twig', [
            'recue' => $recue,
            'categorisee' => $categorisee,
            'saisie' => $saisie,
            'aImputer' => $aImputer,
            'imputee' => $imputee,
            'nonLettreeImputee' => $nonLettreeImputee,
            'nonLettreeRevisee' => $nonLettreeRevisee,
            'revisee' => $revisee,
            'revue' => $revue
        ]);
    }

    public function categorieAction(Request $request){

        $get = $request->query;

        $dossier = $get->get('dossierid');
        $exercice = $get->get('exercice');
        $status = ($get->get('status') === null) ? -1 : $get->get('status');

//        if(in_array(intval($status), $statusAccepted)){
            $nbParCategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->getNbreImageParCategorie($dossier, $exercice, $status);
//        }

        return $this->render('@Tenue/SaisieUniverselle/optionCategorie.html.twig', ['categories' => $nbParCategorie]);
    }

    public function listImageAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $get = $request->query;

        $dossierid = $get->get('dossierid');
        $status = $get->get('status');
        $categorieid = $get->get('categorieid');
        $souscategorieid = $get->get('souscategorieid');
        $soussouscategorieid = $get->get('soussouscategorieid');
        $exercice = $get->get('exercice');
        $datescan = $get->get('datescan');

        $datescan = \DateTime::createFromFormat('Y-m-d', $datescan);

        $images = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getListeImageOrDateScanSU($dossierid, $exercice, $status, $categorieid, $souscategorieid, $soussouscategorieid, $datescan);


        return $this->render('@Tenue/SaisieUniverselle/listImage.html.twig', ['images'=> $images]);

    }

    public function sirenAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $siren = $request->query->get('siren');

        $query = "SELECT NOMEN_LONG, CODPOS FROM siren WHERE SIREN = :siren";

        $con = new CustomPdoConnection();
        $pdo = $con->sirenConnect();
        $prep = $pdo->prepare($query);
        $prep->execute(['siren' => $siren]);

        $res = $prep->fetchAll();

        $rs = '';
        $codePostal = '';

        if(count($res) > 0){
            $rs = $res[0]->NOMEN_LONG;
            $codePostal = $res[0]->CODPOS;
        }

        return new JsonResponse(['rs' => $rs, 'codpos' => $codePostal]);
    }

    public function imageAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->query->get('imageid');

        $imageService = new ImageService($this->getDoctrine()->getManager());

        return new Response($imageService->getUrl($imageid));
    }

    public function dataImageAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->query->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            throw new NotFoundResourceException('Image introuvable');

        $dossier = $image->getLot()->getDossier();

        $saisieEntity = '';
        $tvaEntity = '';
        $regleEcheanceEntity = '';
        $inImputation = false;

        if($image->getCtrlImputation() >= 2) {
            $saisieEntity = 'ImputationControle';
            $regleEcheanceEntity = 'ImputationControleRegleEcheance';
            $tvaEntity = 'TvaImputationControle';
            $inImputation = true;
        }
        elseif ($image->getImputation() >= 2){
            $saisieEntity = 'Imputation';
            $regleEcheanceEntity = 'ImputationRegleEcheance';
            $tvaEntity = 'TvaImputation';
            $inImputation = true;
        }
        elseif ($image->getCtrlSaisie() >= 2){
            $saisieEntity = 'SaisieControle';
            $regleEcheanceEntity = 'ControleRegleEcheance';
            $tvaEntity = 'TvaSaisieControle';
        }
        elseif ($image->getSaisie2() >= 2){
            $saisieEntity = 'Saisie2';
            $regleEcheanceEntity = 'Saisie2RegleEcheance';
            $tvaEntity = 'TvaSaisie2';
        }
        elseif ($image->getSaisie1() >= 2){
            $saisieEntity = 'Saisie1';
            $regleEcheanceEntity = 'Saisie1RegleEcheance';
            $tvaEntity = 'TvaSaisie1';
        }

        $saisie = null;
        if($saisieEntity !== '') {
            $saisies = $this->getDoctrine()
                ->getRepository('AppBundle:'.$saisieEntity)
                ->findBy(['image' => $image]);

            if(count($saisies) > 0){
                /** @var Saisie1 $saisie */
                $saisie = $saisies[0];
            }
        }

        $trouveRelCb = false;

        if($saisie !== null){
            $montant = $saisie->getMontantPaye();
            $dateReglement = $saisie->getDateReglement();

            if($montant !== null && $dateReglement !== null) {
                $trouveRelCb = (count($this->getDoctrine()
                        ->getRepository('AppBundle:BanqueSousCategorieAutre')
                        ->getObByReleve($dossier, $montant, $dateReglement, 5)) > 0) ? true : false;
            }
        }

        $tvas = null;
        if($tvaEntity !== ''){
            /** @var TvaImputation[] $tvas */
            $tvas = $this->getDoctrine()
                ->getRepository('AppBundle:'.$tvaEntity)
                ->findBy(['image' => $image]);
        }

        $regleEcheance = null;
        if($regleEcheanceEntity !== ''){
            $regleEcheances = $this->getDoctrine()
                ->getRepository('AppBundle:'.$regleEcheanceEntity)
                ->findBy(['image' => $image]);

            if(count($regleEcheances) > 0){
                /** @var ImputationRegleEcheance $regleEcheance */
                $regleEcheance = $regleEcheances[0];
            }
        }

        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);


        $pays = $this->getDoctrine()
            ->getRepository('AppBundle:Pays')
            ->findBy([], ['nom' => 'ASC']);

        $typePieces = $this->getDoctrine()
            ->getRepository('AppBundle:TypePiece')
            ->findAll();

        $typeAchatVentes = $this->getDoctrine()
            ->getRepository('AppBundle:TypeAchatVente')
            ->findAll();
        /** @var TypeVente[] $typeVentes */
        $typeVentes = $this->getDoctrine()
            ->getRepository('AppBundle:TypeVente')
            ->findAll();

        /** @var ModeReglement[] $modeReglements */
        $modeReglements = $this->getDoctrine()
            ->getRepository('AppBundle:ModeReglement')
            ->findBy([], ['libelle' => 'ASC']);

        $natures = [];
        $sousNatures = [];
        $organismes = [];


        $devises = $this->getDoctrine()
            ->getRepository('AppBundle:Devise')
            ->findBy([], ['nom' => 'ASC']);


        /** @var TvaTaux[] $tvatauxs */
        $tvatauxs = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->findBy(['actif' => 1], ['taux' => 'ASC']);

        /** @var Categorie[] $categories */
        $categories = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->getAllCategories();


        /** @var Sousnature[] $jfSousNatures */
        $jfSousNatures = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->findBy(['nature' => $this->getDoctrine()
            ->getRepository('AppBundle:Nature')
            ->find(393), 'actif' =>1 ], ['libelle' => 'ASC']);

        /** @var MentionManuscrite[] $jfMentions */
        $jfMentions = $this->getDoctrine()
            ->getRepository('AppBundle:MentionManuscrite')
            ->findBy([], ['libelle' => 'ASC']);

        $pccBilans = [];

        $tiers = [];
        $pccs = [];
        $souscategories = [];
        $soussouscategories = [];
        $categorie = null;
        $souscategorie = null;

        /** @var Cerfa[] $cerfas */
        $cerfas = $this->getDoctrine()
            ->getRepository('AppBundle:Cerfa')
            ->findBy([],['numero' => 'ASC']);

        $trouvePccTva = false;

        if($separation !== null) {

            $typeTiers = -1;
            $categorie = $separation->getCategorie();
            $souscategorie = $separation->getSouscategorie();

            switch ($categorie->getId()) {
                case 10:
                    $typeTiers = 0;
                    break;
                case 9:
                    $typeTiers = 1;
                    break;
                case 11:
                    $typeTiers = 3;
                    break;
            }

            /** @var Nature[] $natures */
            $natures = $this->getDoctrine()
                ->getRepository('AppBundle:Nature')
                ->findBy(['categorie' => $categorie, 'actif' => 1], ['libelle' => 'ASC']);

            /** @var Sousnature[] $sousNatures */
            $sousNatures = $this->getDoctrine()
                ->getRepository('AppBundle:Sousnature')
                ->getSousNaturesByCategorie($categorie);

            /** @var Souscategorie[] $souscategories */
            $souscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->getSouscategoriesByCategorie($categorie);

            /** @var Soussouscategorie[] $soussouscategories */
            $soussouscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySouscategories($souscategories);

            /** @var Tiers[] $tiers */
            $tiers = $this->getDoctrine()
                ->getRepository('AppBundle:Tiers')
                ->findBy(['dossier' => $dossier, 'type' => $typeTiers], ['compteStr' => 'ASC']);

            $pccTvas = [];

            if ($categorie->getId() === 10 || $categorie->getId() === 11) {

//                $pccTiers = $this->getDoctrine()
//                    ->getRepository('AppBundle:Pcc')
//                    ->getPccTiersByDossierArrayLikes($dossier, ['6','2','4'],[], $this->getUser());


                $pccTiers6 = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, '6');

                $pccTiers2 = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, '2');

                $pccTiers4 = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, '4');


                $pccs = [];

                foreach ($pccTiers6 as $pcc){
                    $pccs[] = $pcc;
                }

                foreach ($pccTiers2 as $pcc){
                    $pccs[] = $pcc;
                }

                foreach ($pccTiers4 as $pcc){
                    $pccs[] = $pcc;
                }

//                if(count($pccTiers) > 0) {
//                    /** @var Pcc[] $pccs */
//                    $pccs = $pccTiers['pccs'];
//                }

                $pccTvas = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier,'4456');

                $trouvePccTva = true;

            } else if($categorie->getId() === 9) {
                $pccs = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, '7');

                $pccTvas = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, '4457');

                $trouvePccTva = true;
            }

            if($categorie->getId() === 11){

                $tdPcgs = $this->getDoctrine()
                    ->getRepository('AppBundle:TdNdfBilanPcg')
                    ->findAll();

                $tds = [];

                foreach ($tdPcgs as $tdPcg){
                    $tds[] = $tdPcg->getPcg()->getCompte();
                }


                $pccBilans = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccTiersByDossierArrayLikes($dossier, $tds, [], $this->getUser());

                if(count($pccBilans) > 0) {
                    /** @var Pcc[] $pccBilans */
                    $pccBilans = $pccBilans['pccs'];
                }

            }

            /** @var Organisme[] $organismes */
            $organismes = $this->getDoctrine()
                ->getRepository('AppBundle:Organisme')
                ->getOrganismeByCategorie($categorie);

        }

        if(!$trouvePccTva) {
            /** @var Pcc[] $pccTvas */
            $pccTvas = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, '445');
        }

        $dossier = $image->getLot()->getDossier();
        $client = $dossier->getSite()->getClient();

        $dateCloture = null;
        $dateEcriture = null;
        $tenueComptabilite = null;
        $instructionDossier = null;
        $instructionSaisie = null;

        try {
            $dateCloture = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossier, $image->getExercice());
        } catch (\Exception $e) {
        }

        $historiqueUpload = $this->getDoctrine()
            ->getRepository('AppBundle:HistoriqueUpload')
            ->getLastUploadDossier($dossier);

        if($historiqueUpload !== null){
            $dateEcriture = $historiqueUpload->getDateUpload();
        }

        /** @var MethodeComptable $methodeComptable */
        $methodeComptable = $this->getDoctrine()
            ->getRepository('AppBundle:MethodeComptable')
            ->getMethodeComptableByDossier($dossier);

        if($methodeComptable !== null) {
            switch ($methodeComptable->getTenueComptablilite()) {
                case 1:
                    $tenueComptabilite = 'Mensuelle';
                    break;
                case 2:
                    $tenueComptabilite = 'Trimestrielle';
                    break;
                case 3:
                    $tenueComptabilite = 'Semestrielle';
                    break;
                case 4:
                    $tenueComptabilite = 'Annuelle';
                    break;
                case 5:
                    $tenueComptabilite = 'Ponctuelle';
                    break;
            }
        }

        $instructionDossiers = $this->getDoctrine()
            ->getRepository('AppBundle:InstructionDossier')
            ->findBy(['client' => $client]);

        if(count($instructionDossiers) > 0){
            /** @var InstructionDossier $instructionDossier */
            $instructionDossier = $instructionDossiers[0];
        }

        $instructionSaisies = $this->getDoctrine()
            ->getRepository('AppBundle:InstructionSaisie')
            ->findBy(['dossier' => $dossier]);

        if(count($instructionSaisies) > 0){
            /** @var InstructionSaisie $instructionSaisie */
            $instructionSaisie = $instructionSaisies[0];
        }

        /** @var NdfUtilisateur[] $beneficiaires */
        $beneficiaires = $this->getDoctrine()
            ->getRepository('AppBundle:NdfUtilisateur')
            ->findBy(['dossier' => $dossier], ['nom' => 'ASC']);

        /** @var CarteBleuBanqueCompte[] $carteBleuBanqueComptes */
        $carteBleuBanqueComptes = $this->getDoctrine()
            ->getRepository('AppBundle:CarteBleuBanqueCompte')
            ->getCarteBleuBanquesCompteByDossier($dossier);


        $params = [
            'categorie' => $categorie,
            'dossier' => $dossier,
            'dateCloture' => $dateCloture,
            'dateEcriture' => $dateEcriture,
            'tenueComptabilite' => $tenueComptabilite,
            'instructionDossier' => $instructionDossier,
            'instructionSaisie' => $instructionSaisie,
            'image' => $image,
            'saisie' => $saisie,
            'inImputation' => $inImputation,
            'regleEcheance' => $regleEcheance,
            'regleEcheanceEntity' => $regleEcheanceEntity,
            'payss' => $pays,
            'typePieces' => $typePieces,
            'typeAchatVentes' => $typeAchatVentes,
            'typeVentes' => $typeVentes,
            'modeReglements' => $modeReglements,
            'natures' => $natures,
            'sousnatures' => $sousNatures,
            'categories' => $categories,
            'souscategories' => $souscategories,
            'soussouscategories' => $soussouscategories,
            'devises' => $devises,
            'tvas' => $tvas,
            'tiers' => $tiers,
            'pccBilans' => $pccBilans,
            'pccs' => $pccs,
            'pccTvas' => $pccTvas,
            'tvaTauxs' => $tvatauxs,
            'tvaEntity' => $tvaEntity,
            'saisieEntity' => $saisieEntity,
            'separation' => $separation,
            'organismes' => $organismes,
            'cerfas' => $cerfas,
            'beneficiaires' => $beneficiaires,
            'jfSounatures' => $jfSousNatures,
            'jfMentions' => $jfMentions,
            'carteBleuBanqueComptes' => $carteBleuBanqueComptes,
            'trouveRelCb' => $trouveRelCb
        ];

        if($categorie === null){
            return $this->render('@Tenue/SaisieUniverselle/dataRecue.html.twig', $params);
        }
        switch ($categorie->getCode()){
            case 'CODE_FRNS':
            case 'CODE_CLIENT':
                return $this->render('@Tenue/SaisieUniverselle/dataFournisseur.html.twig',
                    $params);
                break;

            case 'CODE_NDF':
                if($souscategorie !== null){
                    if($souscategorie->getId() === 133 || $souscategorie->getId() === 134){
                        return $this->render('@Tenue/SaisieUniverselle/dataJustificatif.html.twig',
                            $params);
                    }
                }
                return $this->render('@Tenue/SaisieUniverselle/dataNoteDeFrais.html.twig',
                    $params);
                break;

            case 'CODE_FISC':
                return $this->render('@Tenue/SaisieUniverselle/dataFiscal.html.twig', $params);
                break;

            case 'CODE_SOC':
                return $this->render('@Tenue/SaisieUniverselle/dataSocial.html.twig', $params);
                break;

            case 'CODE_CAISSE':
                return $this->render('@Tenue/SaisieUniverselle/dataCaisse.html.twig', $params);
                break;

            default:
                return $this->render('@Tenue/SaisieUniverselle/dataRecue.html.twig', $params);
                break;
        }
    }

    public function ecritureAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $rowid = $request->query->get('rowid');

        $rowidArr = explode('-', $rowid);
        if(count($rowidArr) !== 3)
            throw new NotFoundHttpException('Comptes introuvables');

        $tiersId = $rowidArr[1];
        $pccId  = $rowidArr[0];
        $pccTvaId = $rowidArr[2];

        $ecritureid = $request->query->get('ecritureid');

        $imageid = $request->query->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);
        if($image === null)
            throw  new NotFoundHttpException('Image introuvable');

        /** @var Separation $separation */
        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);

        $souscategorie =null;
        $soussouscategorie = null;
        $soussouscategorieId = null;
        $souscategorieId = null;

        if($separation !== null){
            $souscategorie = $separation->getSouscategorie();
            $soussouscategorie = $separation->getSoussouscategorie();
        }

        if($soussouscategorie !== null && $souscategorie === null){
            $souscategorie = $soussouscategorie->getSouscategorie();
        }

        $tvaEntity = '';

        if($image->getCtrlImputation() >= 2) {
            $tvaEntity = 'TvaImputationControle';
        }
        elseif ($image->getImputation() >= 2){
            $tvaEntity = 'TvaImputation';
        }
        elseif ($image->getCtrlSaisie() >= 2){
            $tvaEntity = 'TvaSaisieControle';
        }
        elseif ($image->getSaisie2() >= 2){
            $tvaEntity = 'TvaSaisie2';
        }
        elseif ($image->getSaisie1() >= 2){
            $tvaEntity = 'TvaSaisie1';
        }

        $dossier = $image->getLot()->getDossier();

        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);

        if($separation === null)
            throw new NotFoundResourceException('Image non separée');


        $typeTiers = -1;
        $categorie = $separation->getCategorie();

        switch ($categorie->getId()){
            case 10:
            case 11:
                $typeTiers = 0;
                break;
            case 9:
                $typeTiers = 1;
                break;
        }

        $pays = $this->getDoctrine()
            ->getRepository('AppBundle:Pays')
            ->findBy([], ['nom'=>'ASC']);

        $typePieces = $this->getDoctrine()
            ->getRepository('AppBundle:TypePiece')
            ->findAll();

        $typeAchatVentes = $this->getDoctrine()
            ->getRepository('AppBundle:TypeAchatVente')
            ->findAll();
        /** @var TypeVente[] $typeVentes */
        $typeVentes = $this->getDoctrine()
            ->getRepository('AppBundle:TypeVente')
            ->findAll();

        /** @var ModeReglement[] $modeReglements */
        $modeReglements = $this->getDoctrine()
            ->getRepository('AppBundle:ModeReglement')
            ->findBy([], ['libelle'=> 'ASC']);

        $devises = $this->getDoctrine()
            ->getRepository('AppBundle:Devise')
            ->findBy([], ['nom'=> 'ASC']);


        $sousNature = null;
        $nature = null;

        $js = false;

        $natureId = null;
        $sousNatureId = $request->query->get('sousnatureid');
        $sousNature = null;
        if($sousNatureId !== null){
            $js = true;

            /** @var Sousnature $sousNature */
            $sousNature = $this->getDoctrine()
                ->getRepository('AppBundle:Sousnature')
                ->find($sousNatureId);
            if($sousNature){
                $natureId = $sousNature->getNature()->getId();
            }
        }

        /** @var Sousnature[] $sousNatures */
        $sousNatures = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->getSousNaturesByCategorie($categorie);

        if(!$js) {
            if ($soussouscategorie !== null) {
                $soussouscategorieId = $soussouscategorie->getId();

                $sousNature = $soussouscategorie->getSousnature();
                if($sousNature !== null)
                    $sousNatureId = $sousNature->getId();
            }
            if ($sousNature !== null) {
                $nature = $sousNature->getNature();
                $natureId = $nature->getId();

                $sousNatures = $this->getDoctrine()
                    ->getRepository('AppBundle:Sousnature')
                    ->findBy(['nature' => $nature, 'actif' => 1], ['libelle' => 'ASC']);
            }
        }

        /** @var Nature[] $natures */
        $natures = $this->getDoctrine()
            ->getRepository('AppBundle:Nature')
            ->findBy(['categorie' => $categorie, 'actif' => 1], ['libelle' => 'ASC']);

        /** @var Souscategorie[] $souscategories */
        $souscategories = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->getSouscategoriesByCategorie($categorie);

        if($souscategorie !== null) {
            $souscategorieId = $souscategorie->getId();

            $soussouscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySouscategorie($souscategorie);
        }
        else {
            /** @var Soussouscategorie[] $soussouscategories */
            $soussouscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySouscategories($souscategories);
        }

        /** @var Tiers[] $tiers */
        $tiers = $this->getDoctrine()
            ->getRepository('AppBundle:Tiers')
            ->findBy(['dossier' => $dossier, 'type' => $typeTiers ], ['compteStr' => 'ASC']);


        /** @var Pcc[] $pccs */
        $pccs = [];


        if($categorie->getCode() === 'CODE_NDF') {

            if ($sousNature !== null) {
                /** @var Pcg[] $pcgs */
                $pcgs = $this->getDoctrine()
                    ->getRepository('AppBundle:SoussouscategorieCompte')
                    ->getPcgsBySousnature($sousNature, 1);
                if (count($pcgs) > 0) {
                    $pccs = [];
                    foreach ($pcgs as $pcg) {
                        $pccTmps = $this->getDoctrine()
                            ->getRepository('AppBundle:Pcc')
                            ->getPccByDossierLike($dossier, $pcg->getCompte());

                        foreach ($pccTmps as $pccTmp) {
                            $pccs[] = $pccTmp;
                        }
                    }
                }
            }
            else{
                $pccs = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, '6');
            }
        }
        elseif($categorie->getCode() === 'CODE_CLIENT' || $categorie->getCode() === 'CODE_FRNS'){
            $like = '';


            if($souscategorie !== null){
                $like = $this->getCompteOnLibelle($souscategorie->getLibelleNew());
            }
            if($like === ''){
                $like = ($categorie->getCode() === 'CODE_CLIENT') ? '7' : '6';
            }

            $pccs = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, $like);
        }

            /** @var Pcc[] $pccTvas */
            $pccTvas = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, '445');
        /** @var TvaTaux[] $tvatauxs */
        $tvatauxs = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->findBy(['actif' => 1], ['taux'=>'ASC']);

        $params = [
            'ecritureid' => $ecritureid,
            'image' => $image,
            'payss' => $pays,
            'typePieces' => $typePieces,
            'typeAchatVentes' => $typeAchatVentes,
            'typeVentes' => $typeVentes,
            'modeReglements' => $modeReglements,
            'natures' => $natures,
            'sousnatures' => $sousNatures,
            'souscategories' => $souscategories,
            'soussouscategories' => $soussouscategories,
            'devises' => $devises,
            'tiers' => $tiers,
            'pccs' => $pccs,
            'pccTvas' => $pccTvas,
            'tvaTauxs' => $tvatauxs,
            'tiersId' => $tiersId,
            'pccTvaId' => $pccTvaId,
            'pccId' => $pccId,
            'tvaEntity' => $tvaEntity,
            'sousnatureId' => $sousNatureId,
            'natureId' => $natureId,
            'souscategorieId' => $souscategorieId,
            'soussouscategorieId' => $soussouscategorieId

        ];

        return $this->render('@Tenue/SaisieUniverselle/ecriture.html.twig', $params);
    }


    public function ecritureRecapAction(Request $request){

        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageid = $post->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            throw new NotFoundHttpException('Image introuvable');


        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);

        if($separation === null)
            throw new NotFoundHttpException('Image non categorisée');

        $categorie = $separation->getCategorie();

        $saisieEntity = '';
        $tvaEntity = '';

        if($image->getCtrlImputation() >= 2) {
            $saisieEntity = 'ImputationControle';
            $tvaEntity = 'TvaImputationControle';
        }
        elseif ($image->getImputation() >= 2){
            $saisieEntity = 'Imputation';
            $tvaEntity = 'TvaImputation';
        }
        elseif ($image->getCtrlSaisie() >= 2){
            $saisieEntity = 'SaisieControle';
            $tvaEntity = 'TvaSaisieControle';
        }
        elseif ($image->getSaisie2() >= 2){
            $saisieEntity = 'Saisie2';
            $tvaEntity = 'TvaSaisie2';
        }
        elseif ($image->getSaisie1() >= 2){
            $saisieEntity = 'Saisie1';
            $tvaEntity = 'TvaSaisie1';
        }

        $saisie = null;

        if($saisieEntity !== '') {
            $saisies = $this->getDoctrine()
                ->getRepository('AppBundle:'.$saisieEntity)
                ->findBy(['image' => $image]);

            if(count($saisies) > 0){
                /** @var Saisie1 $saisie */
                $saisie = $saisies[0];
            }
        }

        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:'.$tvaEntity)
        ;


        $tvaSaisie = $repository->createQueryBuilder('ti')
            ->where('ti.image = :image')
            ->setParameter('image', $image)
            ->groupBy('ti.pcc')
            ->addGroupBy('ti.tiers')
            ->addGroupBy('ti.pccTva')
            ->select('ti')
            ->addSelect('SUM(ti.montantTtc) AS ttc')
            ->addSelect('SUM(ti.montantHt) AS ht')
            ->addSelect('SUM(ROUND(ti.montantTtc - ti.montantHt, 2)) AS tva')
            ->getQuery()
            ->getResult();


        $listeTiersFinal = [];
        $listeTvaFinal = [];
        $listeResFinal = [];

        if($tvaSaisie !== null) {

            foreach ($tvaSaisie as $tvaG) {
                $tva = $tvaG[0];
                $trouve = false;
                for ($i = 0; $i < count($listeTiersFinal); $i++) {
                    if ($listeTiersFinal[$i]['compte'] === $tva->getTiers()) {
                        $listeTiersFinal[$i]['montant'] = $listeTiersFinal[$i]['montant'] + $tvaG['ttc'];
                        $listeTiersFinal[$i]['libelle'] = $tva->getLibelle();
                        $trouve = true;
                    }
                }
                if (!$trouve) {
                    $listeTiersFinal[] = [
                        'compte' => $tva->getTiers(),
                        'montant' => $tvaG['ttc'],
                        'libelle' => $tva->getLibelle()
                    ];
                }

                $trouve = false;
                for ($i = 0; $i < count($listeTvaFinal); $i++) {
                    if ($listeTvaFinal[$i]['compte'] === $tva->getPccTva()) {
                        $listeTvaFinal[$i]['montant'] = $listeTvaFinal[$i]['montant'] + $tvaG['tva'];
                        $listeTvaFinal[$i]['libelle'] = $tva->getLibelle();
                        $trouve = true;
                    }
                }
                if (!$trouve) {
                    $listeTvaFinal[] = [
                        'compte' => $tva->getPccTva(),
                        'montant' => $tvaG['tva'],
                        'libelle' => $tva->getLibelle()
                    ];
                }


                $trouve = false;
                for ($i = 0; $i < count($listeResFinal); $i++) {
                    if ($listeResFinal[$i]['compte'] === $tva->getPcc()) {
                        $listeResFinal[$i]['montant'] = $listeResFinal[$i]['montant'] + $tvaG['ht'];
                        $listeResFinal[$i]['libelle'] = $tva->getLibelle();
                        $trouve = true;
                    }
                }
                if (!$trouve) {
                    $listeResFinal[] = [
                        'compte' => $tva->getPcc(),
                        'montant' => $tvaG['ht'],
                        'libelle' => $tva->getLibelle()
                    ];
                }
            }
        }


        $typeEcriture = 0;

        /** @var Ecriture[] $ecritures */
        $ecritures = $this->getDoctrine()
            ->getRepository('AppBundle:Ecriture')
            ->getEcrituresByImage($image, $typeEcriture);

        $rows = [];
        $userData = [];

        $ecritureAction = $this->render('@Tenue/SaisieUniverselle/ndfGridAction.html.twig', ['categorie' => 'e'])->getContent();


        if(count($ecritures) <= 0){

            $title = '<i class="fa fa-pencil"></i>';

            if($saisie !== null) {

                if ($saisie->getTypePiece() === null) {
                    $typePiece = 2;
                } else {
                    $typePiece = $saisie->getTypePiece()->getId();
                }

                $totalTiers = 0;
                $totalRes = 0;
                $totalTvaf = 0;

                if(
                    ($typePiece === 1 && $categorie->getCode() === 'CODE_CLIENT') ||
                    ($typePiece === 2 && $categorie->getCode() === 'CODE_FRNS') ||
                    ($typePiece === 2 && $categorie->getCode() === 'CODE_NDF')
                ) {


                    $id = 0;

                    foreach ($listeTiersFinal as $tiers) {
                        if ($tiers['compte'] !== null) {
                            $totalTiers += $tiers['montant'];

                            /** @var Tiers $compte */
                            $compte = $tiers['compte'];

                            $rows[] = [
                                'id' => 'e_picdoc_' . $id,
                                'cell' => [
                                    'e_date' => $saisie->getDateFacture()->format('Y-m-d'),
                                    'e_compte' => $compte->getCompteStr() .' - '. $compte->getIntitule(),
//                                    'e_intitule' => $tiers['libelle'],
                                    'e_debit' => 0,
                                    'e_credit' => $tiers['montant'],
                                    'e_action' => $ecritureAction
                                ]
                            ];

                            $id++;
                        }
                    }


                    foreach ($listeResFinal as $res) {
                        if ($res['compte'] !== null) {
                            $totalRes += $res['montant'];

                            /** @var Pcc $compte */
                            $compte = $res['compte'];

                            $rows[] = [
                                'id' => 'e_picdoc_' . $id,
                                'cell' => [
                                    'e_date' => $saisie->getDateFacture()->format('Y-m-d'),
                                    'e_compte' => $compte->getCompte(). ' - '. $compte->getIntitule(),
//                                    'e_intitule' => $res['libelle'],
                                    'e_debit' => $res['montant'],
                                    'e_credit' => 0,
                                    'e_action' => $ecritureAction
                                ]
                            ];
                            $id++;
                        }
                    }

                    foreach ($listeTvaFinal as $tvaf) {
                        if ($tvaf['compte'] !== null) {
                            $totalTvaf += $tvaf['montant'];

                            /** @var Pcc $compte */
                            $compte = $tvaf['compte'];

                            $rows[] = [
                                'id' => 'e_picdoc_' . $id,
                                'cell' => [
                                    'e_date' => $saisie->getDateFacture()->format('Y-m-d'),
                                    'e_compte' => $compte->getCompte() .' - '. $compte->getIntitule(),
//                                    'e_intitule' => $tvaf['libelle'],
                                    'e_debit' => $tvaf['montant'],
                                    'e_credit' => 0,
                                    'e_action' => $ecritureAction
                                ]
                            ];
                            $id++;
                        }
                    }

                    $totalDebit = $totalTiers;
                    $totalCredit = $totalTvaf + $totalRes;

                    $userData = [
                        'e_credit' => $totalDebit,
                        'e_debit' => $totalCredit
                    ];
                }

                elseif (
                    ($typePiece === 2 && $categorie->getCode() === 'CODE_CLIENT') ||
                    ($typePiece === 1 && $categorie->getCode() === 'CODE_FRNS'))
                {
                    $id = 0;

                    foreach ($listeTiersFinal as $tiers) {
                        if ($tiers['compte'] !== null) {
                            $totalTiers += $tiers['montant'];

                            /** @var Tiers $compte */
                            $compte = $tiers['compte'];

                            $rows[] = [
                                'id' => 'e_picdoc_' . $id,
                                'cell' => [
                                    'e_date' => $saisie->getDateFacture()->format('Y-m-d'),
                                    'e_compte' => $compte->getCompteStr(). ' - '. $compte->getIntitule(),
//                                    'e_intitule' => $tiers['libelle'],
                                    'e_debit' => $tiers['montant'],
                                    'e_credit' => 0,
                                    'e_action' => $ecritureAction
                                ]
                            ];

                            $id++;
                        }
                    }

                    foreach ($listeResFinal as $res) {
                        if ($res['compte'] !== null) {
                            $totalRes += $res['montant'];

                            /** @var Pcc $compte */
                            $compte = $res['compte'];

                            $rows[] = [
                                'id' => 'e_picdoc_' . $id,
                                'cell' => [
                                    'e_date' => $saisie->getDateFacture()->format('Y-m-d'),
                                    'e_compte' => $compte->getCompte().' - '. $compte->getIntitule(),
//                                    'e_intitule' => $res['libelle'],
                                    'e_debit' => 0,
                                    'e_credit' => $res['montant'],
                                    'e_action' => $ecritureAction
                                ]
                            ];
                            $id++;
                        }
                    }

                    foreach ($listeTvaFinal as $tvaf) {
                        if ($tvaf['compte'] !== null) {
                            $totalTvaf += $tvaf['montant'];

                            /** @var Pcc $compte */
                            $compte = $tvaf['compte'];

                            $rows[] = [
                                'id' => 'e_picdoc_' . $id,
                                'cell' => [
                                    'e_date' => $saisie->getDateFacture()->format('Y-m-d'),
                                    'e_compte' => $compte->getCompte() .' - '. $compte->getIntitule(),
//                                    'e_intitule' => $tvaf['libelle'],
                                    'e_debit' => 0,
                                    'e_credit' => $tvaf['montant'],
                                    'e_action' => $ecritureAction
                                ]
                            ];
                            $id++;
                        }
                    }

                    $totalDebit = $totalTiers;
                    $totalCredit = $totalTvaf + $totalRes;

                    $userData = [
                        'e_debit' => $totalDebit,
                        'e_credit' => $totalCredit
                    ];
                }

            }
        }
        else{

            $title = '<i class="fa fa-book"></i>';

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($ecritures as $ecriture){
                $compte = '';
                $intitule = '';

                $totalDebit += $ecriture->getDebit();
                $totalCredit += $ecriture->getCredit();

                if($ecriture->getTiers() !== null){
                    $compte = $ecriture->getTiers()
                        ->getCompteStr();

                    $intitule = $ecriture->getTiers()
                        ->getIntitule();
                }
                else if($ecriture->getPcc() !== null){
                    $compte = $ecriture->getPcc()
                        ->getCompte();

                    $intitule = $ecriture->getPcc()
                        ->getIntitule();
                }

                $libelle = $ecriture->getLibelle();

                $rows[] = [
                    'id' => $ecriture->getId(),
                    'cell' => [
                        'e_date' => $ecriture->getDateEcr()->format('Y-m-d'),
                        'e_compte' => $compte. ' - '.$intitule,
                        'e_libelle' => $libelle,
                        'e_debit' => $ecriture->getDebit(),
                        'e_credit' => $ecriture->getCredit(),
                        'e_action' => $ecritureAction
                    ]
                ];

            }

            $userData = [
                'e_debit' => $totalDebit,
                'e_credit' => $totalCredit
            ];
        }


        return new JsonResponse([
            'rows' => $rows,
            'userdata' => $userData,
            'title' => $title,
            'typeecriture' => $typeEcriture
        ]);

    }

    public function ecritureRecapEditAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if(!$image)
            throw new NotFoundHttpException('Image introuvable');


        $typeEcriture = 0;

        /** @var Ecriture[] $ecritures */
        $ecritures = $this->getDoctrine()
            ->getRepository('AppBundle:Ecriture')
            ->getEcrituresByImage($image, $typeEcriture);

        if($typeEcriture === 1)
            return new JsonResponse(['type' => 'error', 'message' => 'Ecriture déjà dans la compta']);


        $dossier = $image->getLot()->getDossier();

        $error = [];

        $post = $request->request;

        $id = $post->get('id');

        $date = \DateTime::createFromFormat('d/m/Y', $post->get('e_date'));
        if($date === false){
            $error[] = 'Date';
        }

        $libelle = $post->get('e_libelle');
        if(trim($libelle) === ''){
            $error[] = 'libelle';
        }

        $debit = $post->get('e_debit');
        $credit = $post->get('e_credit');
        $compte = $post->get('e_compte');
        $pcc = null;
        $tiers = null;

        if($compte !== null) {
            $pcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($compte);

            $pccDossiers = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->findBy(['dossier' => $dossier]);

            if(!in_array($pcc, $pccDossiers) || $pcc === null){
                $pcc = null;

                $tiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Tiers')
                    ->find($compte);

                $tiersDossiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Tiers')
                    ->findBy(['dossier' => $dossier]);

                if(!in_array($tiers, $tiersDossiers)){
                    $tiers = null;
                }
            }
        }

        if($pcc === null && $tiers === null){
            $error[] = 'Compte';
        }

        if(count($error) > 0) {
            return new JsonResponse(['type' => 'error', 'message' => 'Les champs suivants sont obligatoires: ' . implode(', ', $error)]);
        }

        $em = $this->getDoctrine()->getManager();

        if($id === 'new_row'){
            $ecriture = new Ecriture();

            /** @var Ecriture[] $ecritureModeles */
            $ecritureModeles = $this->getDoctrine()
                ->getRepository('AppBundle:Ecriture')
                ->findBy(['image' => $image]);

            if(count($ecritureModeles) > 0) {
                $ecritureModele = $ecritureModeles[0];
                $ecriture->setDossier($ecritureModele->getDossier());
                $ecriture->setJournalDossier($ecritureModele->getJournalDossier());
                $ecriture->setImage($image);
                $ecriture->setDossier($dossier);
                $ecriture->setPcc($pcc);
                $ecriture->setTiers($tiers);
                $ecriture->setDateEcr($date);
                $ecriture->setLibelle($libelle);
                $ecriture->setDebit($debit);
                $ecriture->setCredit($credit);

                $em->persist($ecriture);
            }
        }
        else{
            $ecriture = $this->getDoctrine()
                ->getRepository('AppBundle:Ecriture')
                ->find($id);

            if($ecriture !== null){
                $ecriture->setPcc($pcc);
                $ecriture->setTiers($tiers);
                $ecriture->setDateEcr($date);
                $ecriture->setLibelle($libelle);
                $ecriture->setDebit($debit);
                $ecriture->setCredit($credit);
            }
        }

        $em->flush();

        return new JsonResponse(['message' => 'Enregistrement effectuée', 'type' => 'success']);

    }

    public function ecritureRecapDeleteAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $ecritureid = $request->request->get('id');

        $ecriture = $this->getDoctrine()
            ->getRepository('AppBundle:Ecriture')
            ->find($ecritureid);

        if($ecriture){
            $em = $this->getDoctrine()->getManager();

            $em->remove($ecriture);
            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'ecriture supprimée avec succès']);
        }

        return new JsonResponse(['type' => 'error', 'message' => 'erreur lors de la suppression']);
    }

    public function saveAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');


        $post = $request->request;

        $imageId = $post->get('image');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);

        if($image === null)
            return new JsonResponse(['type' => 'error', 'title' => 'Saisie', 'message' => 'Image introuvable']);


        $error = [];
        $paysId = $post->get('pays');
        $pays = $this->getDoctrine()
            ->getRepository('AppBundle:Pays')
            ->find($paysId);

        $siren = $post->get('siren');
        if($siren === ''){
            $siren = null;
            $error[] = 'SIREN';
        }

        $rs = $post->get('rs');
        if($rs === ''){
            $rs = null;
            $error[] = 'RS';
        }

        $typePieceId = $post->get('typepiece');
        $typePiece = $this->getDoctrine()
            ->getRepository('AppBundle:TypePiece')
            ->find($typePieceId);
        if($typePiece === null){
            $error[] = 'Type Pièces';
        }

        $typeAchatVenteId = $post->get('typeav');
        $typeAchatVente = $this->getDoctrine()
            ->getRepository('AppBundle:TypeAchatVente')
            ->find($typeAchatVenteId);
        if($typeAchatVente === null){
            $error[] = 'Type Achat Vente';
        }

        $dateFacture = $post->get('datefact');
        if($dateFacture !== ''){
            $dateFacture = \DateTime::createFromFormat('d/m/Y', $dateFacture);
        }
        else{
            $dateFacture = null;
            $error[] = 'Date Facture';
        }

        if($dateFacture === false){
            $dateFacture = null;
        }

        $numFact = $post->get('numfact');
        if($numFact === ''){
            $numFact = null;

            $error[] = 'Num Fact';
        }

        $typeEcheance = $post->get('typeecheance');

        $dateEcheance = $post->get('dateecheance');
        if($dateEcheance !== ''){
            $dateEcheance = \DateTime::createFromFormat('d/m/Y', $dateEcheance);
        }
        else{
            $dateEcheance = null;
        }

        $dateReglement = $post->get('datereglement');
        if($dateReglement !== ''){
            $dateReglement = \DateTime::createFromFormat('d/m/Y', $dateReglement);
        }
        else{
            $dateReglement = null;
        }

        if($dateReglement === false){
            $dateReglement = null;
        }

        $modeReglementId = $post->get('modereglement');
        $modeReglement = null;
        if($modeReglementId !== null) {
            $modeReglement = $this->getDoctrine()
                ->getRepository('AppBundle:ModeReglement')
                ->find($modeReglementId);
        }

        $numMoyenPaiement = $post->get('nummoyenpaiement');
        if($numMoyenPaiement === ''){
            $numMoyenPaiement = null;
        }

        $montantPaye = $post->get('montantpaye');
        if($montantPaye === ''){
            $montantPaye = 0;
        }
        else{
            $montantPaye = $this->stringToNumber($montantPaye);
        }

        $dateLivraison = $post->get('datelivraison');
        if($dateLivraison !== ''){
            $dateLivraison = \DateTime::createFromFormat('d/m/Y', $dateLivraison);
        }
        else{
            $dateLivraison = null;
            if($typeAchatVente !== null){
                if($typeAchatVente->getId() === 1){
                    $error[] = 'Date Livraison';
                }
            }
        }

        if($dateLivraison === false){
            $dateLivraison = null;
        }

        $periodeDebut = $post->get('periodedebut');
        if($periodeDebut !== ''){
            $periodeDebut = \DateTime::createFromFormat('d/m/Y', $periodeDebut);
        }
        else{
            $periodeDebut = null;
            if($typeAchatVente!== null){
                if($typeAchatVente->getId() === 2){
                    $error[] = 'Periode Debut';
                }
            }
        }

        if($periodeDebut === false){
            $periodeDebut = null;
        }

        $periodeFin = $post->get('periodefin');
        if($periodeFin !== ''){
            $periodeFin = \DateTime::createFromFormat('d/m/Y', $periodeFin);
        }
        else{
            $periodeFin = null;
            if($typeAchatVente!== null){
                if($typeAchatVente->getId() === 2){
                    $error[] = 'Periode Fin';
                }
            }
        }

        if($periodeFin === false){
            $periodeFin = null;
        }

        $deviseId = $post->get('devise');
        $devise = $this->getDoctrine()
            ->getRepository('AppBundle:Devise')
            ->find($deviseId);
        if($devise === null){
            $error[] = 'Devise';
        }

        $chrono = $post->get('chrono');
        if($chrono === ''){
            $chrono = null;
        }

        $sousnatureId = $post->get('jfsousnature');
        $sousnature = null;
        if($sousnatureId !== null){
            $sousnature = $this->getDoctrine()
                ->getRepository('AppBundle:Sousnature')
                ->find($sousnatureId);
        }

        $beneficiaireId = $post->get('beneficiaire');
        $beneficiaire = null;
        if($beneficiaireId !== null){
            $beneficiaire = $this->getDoctrine()
                ->getRepository('AppBundle:NdfUtilisateur')
                ->find($beneficiaireId);
        }

        $mentionId = $post->get('mention');
        $mention = null;
        if($mentionId !== null){
            $mention = $this->getDoctrine()
                ->getRepository('AppBundle:MentionManuscrite')
                ->find($mentionId);
        }

        $nbCouvert = $post->get('nbcouvert');

        $codePostal = $post->get('codepostal');


        $carteBleuBanqueCompte = null;
        $carteBleuBanqueCompteid = $post->get('numcb');
        if($carteBleuBanqueCompteid !== null){
            $carteBleuBanqueCompte = $this->getDoctrine()
                ->getRepository('AppBundle:CarteBleuBanqueCompte')
                ->find($carteBleuBanqueCompteid);
        }

        if(count($error) > 0){
            return new JsonResponse(['type' => 'error', 'title' => 'Saisie', 'message' => 'Les champs suivants sont obligatoires: '. implode(', ', $error)]);
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($this->saisieEntities as $saisieEntity){
            $persist = false;

            /** @var Saisie1[] $saisies */
            $saisies = $this->getDoctrine()
                ->getRepository('AppBundle:'.$saisieEntity)
                ->findBy(['image' => $image]);

            $saisie = null;
            if(count($saisies) > 0){
                $saisie = $saisies[0];
            }
            else {
                switch ($saisieEntity) {
                    case 'Saisie1':
                        $saisie = new Saisie1();
                        break;
                    case 'Saisie2':
                        $saisie = new  Saisie2();
                        break;
                    case 'SaisieControle':
                        $saisie = new SaisieControle();
                        break;
                    case 'Imputation':
                        $saisie = new Imputation();
                        break;
                    case 'ImputationControle':
                        $saisie = new ImputationControle();
                        break;
                }

                $persist = true;
                $saisie->setImage($image);
            }

            if($saisie !== null) {
                $saisie->setPays($pays);
                $saisie->setSiret($siren);
                $saisie->setRs($rs);
                $saisie->setTypePiece($typePiece);
                $saisie->setTypeAchatVente($typeAchatVente);
                $saisie->setDateFacture($dateFacture);
                $saisie->setNumFacture($numFact);
                $saisie->setDateEcheance($dateEcheance);
                $saisie->setDateReglement($dateReglement);
                $saisie->setModeReglement($modeReglement);
                $saisie->setNumPaiement($numMoyenPaiement);
                $saisie->setMontantPaye($montantPaye);
                $saisie->setDateLivraison($dateLivraison);
                $saisie->setPeriodeD1($periodeDebut);
                $saisie->setPeriodeF1($periodeFin);
                $saisie->setDevise($devise);
                $saisie->setChrono($chrono);

                $saisie->setSousnature($sousnature);
                $saisie->setMentionManuscrite($mention);
                $saisie->setNdfUtilisateur($beneficiaire);
                $saisie->setCodePostal($codePostal);
                $saisie->setNbreCouvert($nbCouvert);

                $saisie->setCarteBleuBanqueCompte($carteBleuBanqueCompte);

                if($persist){
                    $em->persist($saisie);
                }
            }
        }

        //Raha sur facture dia fafana ny regles
        if(intval($typeEcheance) === 2) {
            foreach ($this->regleEntities as $regleEntity) {
                $regles = $this->getDoctrine()
                    ->getRepository('AppBundle:' . $regleEntity)
                    ->findBy(['image' => $image]);

                foreach ($regles as $regle) {
                    $em->remove($regle);
                }
            }
        }

        $em->flush();

        $etapeTraitements = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getByCode('UNIVERSELLE', false);
        $etapeTraitement = null;
        if(count($etapeTraitements) > 0)
            $etapeTraitement = $etapeTraitements[0];

        $logService = new LogService($em);
        $logService->Save($image, $etapeTraitement, $this->getUser(), 'SAISIE UNIVERSELLE', $this->getIpAddress());

        $statuService = new StatusImageService($em);
        $statuService->SetStatusImage($image);

        return new JsonResponse(['type' => 'success', 'title' => 'Saisie', 'message' => 'Enregistrement effectué']);
    }

    public function saveFiscalSocialAction(Request $request, $categorie)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');


        $post = $request->request;

        $imageId = $post->get('image');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);

        if($image === null)
            return new JsonResponse(['type' => 'error', 'title' => 'Saisie', 'message' => 'Image introuvable']);

        $error = [];

        $typeSociale = null;
        if($categorie === 'social') {
            $typeSociale = $post->get('typesociale');
            if (intval($typeSociale) === -1) {
                $error[] = 'Type Pièce';
            }
        }

        $dateEcheance = $post->get('dateecheance');
        if($dateEcheance !== ''){
            $dateEcheance = \DateTime::createFromFormat('d/m/Y', $dateEcheance);
        }
        else{
            $dateEcheance = null;
        }

        if($dateEcheance === false){
            $dateEcheance = null;
        }

        $dateReglement = $post->get('datereglement');
        if($dateReglement !== ''){
            $dateReglement = \DateTime::createFromFormat('d/m/Y', $dateReglement);
        }
        else{
            $dateReglement = null;
        }

        if($dateReglement === false){
            $dateReglement = null;
        }

        $modeReglementId = $post->get('modereglement');
        $modeReglement = $this->getDoctrine()
            ->getRepository('AppBundle:ModeReglement')
            ->find($modeReglementId);


        $montant = $post->get('montant');
        if($montant === ''){
            $montant = 0;
        }
        else{
            $montant = $this->stringToNumber($montant);
        }

        $soussouscategorieId = $post->get('fssoussouscategorie');
        $soussouscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Soussouscategorie')
            ->find($soussouscategorieId);

        $sousnatureId = $post->get('sousnature');
        $sousnature = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->find($sousnatureId);

        $organismeId = $post->get('organisme');
        $organisme = null;
        if($organismeId !== null)
            $organisme = $this->getDoctrine()
                ->getRepository('AppBundle:Organisme')
                ->find($organismeId);

        if(count($error) > 0){
            return new JsonResponse(['type' => 'error', 'title' => 'Saisie', 'message' => 'Les champs suivants sont obligatoires: '. implode(', ', $error)]);
        }

        $cerfa = null;
        if($categorie === 'fiscal'){
            $cerfaId = $post->get('cerfa');
            $cerfa = $this->getDoctrine()
                ->getRepository('AppBundle:Cerfa')
                ->find($cerfaId);
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($this->saisieEntities as $saisieEntity){
            $persist = false;

            /** @var Saisie1[] $saisies */
            $saisies = $this->getDoctrine()
                ->getRepository('AppBundle:'.$saisieEntity)
                ->findBy(['image' => $image]);

            $saisie = null;
            if(count($saisies) > 0){
                $saisie = $saisies[0];
            }
            else {
                switch ($saisieEntity) {
                    case 'Saisie1':
                        $saisie = new Saisie1();
                        break;
                    case 'Saisie2':
                        $saisie = new  Saisie2();
                        break;
                    case 'SaisieControle':
                        $saisie = new SaisieControle();
                        break;
                    case 'Imputation':
                        $saisie = new Imputation();
                        break;
                    case 'ImputationControle':
                        $saisie = new ImputationControle();
                        break;
                }

                $persist = true;
                $saisie->setImage($image);
            }

            if($saisie !== null) {

                $saisie->setTypeSociale($typeSociale);
                $saisie->setOrganisme($organisme);
                $saisie->setSousnature($sousnature);
                $saisie->setSoussouscategorie($soussouscategorie);
                $saisie->setDateEcheance($dateEcheance);
                $saisie->setDateReglement($dateReglement);
                $saisie->setModeReglement($modeReglement);
                $saisie->setMontantTtc($montant);
                $saisie->setCerfa($cerfa);

                if($persist){
                    $em->persist($saisie);
                }
            }
        }

        $etapeTraitement = null;
        $etapeTraitements = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getByCode('UNIVERSELLE', false);

        if(count($etapeTraitements) > 0){
            $etapeTraitement = $etapeTraitements[0];
        }

        $em->flush();

        $logService = new LogService($em);
        $logService->Save($image, $etapeTraitement, $this->getUser(), 'SAISIE UNIVERSELLE', $this->getIpAddress());

        $statuService = new StatusImageService($em);
        $statuService->SetStatusImage($image);


        return new JsonResponse(['type' => 'success', 'title' => 'Saisie', 'message' => 'Enregistrement effectué']);
    }

    public function saveNdfCaisseAction(Request $request, $categorie)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageId = $post->get('image');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);


        if ($image === null)
            return new JsonResponse(['type' => 'error', 'title' => 'Saisie', 'message' => 'Image introuvable']);

        $error = [];

        $libelle = null;
        $beneficiaire = null;
        $facturable = null;
        $remboursable = null;
        $typeCaisse = null;

        if($categorie === 'ndf') {
            $libelle = $post->get('note');
            if (trim($libelle) === '') {
                $libelle = null;
                $error [] = 'Nom de la note';
            }

            $beneficiaireId = $post->get('beneficiaire');
            $beneficiaire = $this->getDoctrine()
                ->getRepository('AppBundle:NdfUtilisateur')
                ->find($beneficiaireId);
            if ($beneficiaire === null)
                $error [] = 'Beneficiaire';

            $facturable = 0;
            if($post->get('facturable') === 'on'){
                $facturable = 1;
            }
            $remboursable = 0;
            if($post->get('remboursable') === 'on'){
                $remboursable = 1;
            }
        }
        else{
            $typeCaisse = $post->get('typecaisse');
        }

        $moisDu = $post->get('moisdu');
        if(intval($moisDu) === -1) {
            $moisDu = null;
            $error []= 'Mois du';
        }

        $moisAu = $post->get('moisau');
        if(intval($moisAu) === -1) {
            $moisAu = null;
            $error []= 'Mois au';
        }

        $annee = $post->get('annee');
        if(intval($annee) === -1) {
            $annee = null;
            $error []= 'Année';
        }


        if(count($error) > 0){
            return new JsonResponse(['type' => 'error', 'title' => 'Saisie', 'message' => 'Les champs suivants sont obligatoires: '. implode(', ', $error)]);
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($this->saisieEntities as $saisieEntity) {
            $persist = false;

            /** @var Saisie1[] $saisies */
            $saisies = $this->getDoctrine()
                ->getRepository('AppBundle:' . $saisieEntity)
                ->findBy(['image' => $image]);

            $saisie = null;
            if (count($saisies) > 0) {
                $saisie = $saisies[0];
            } else {
                switch ($saisieEntity) {
                    case 'Saisie1':
                        $saisie = new Saisie1();
                        break;
                    case 'Saisie2':
                        $saisie = new  Saisie2();
                        break;
                    case 'SaisieControle':
                        $saisie = new SaisieControle();
                        break;
                    case 'Imputation':
                        $saisie = new Imputation();
                        break;
                    case 'ImputationControle':
                        $saisie = new ImputationControle();
                        break;
                }

                $persist = true;
                $saisie->setImage($image);
            }

            if ($saisie !== null) {

                $saisie->setTypeCaisse($typeCaisse);
                $saisie->setLibelle($libelle);
                $saisie->setMoisDu($moisDu);
                $saisie->setMoisAu($moisAu);
                $saisie->setAnnee($annee);
                $saisie->setNdfUtilisateur($beneficiaire);
                $saisie->setRemboursable($remboursable);
                $saisie->setFacturable($facturable);

                if ($persist) {
                    $em->persist($saisie);
                }
            }
        }


        $em->flush();

        $etapeTraitement = null;
        $etapeTraitements = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getByCode('UNIVERSELLE', false);

        if (count($etapeTraitements) > 0) {
            $etapeTraitement = $etapeTraitements[0];
        }

        $logService = new LogService($em);
        $logService->Save($image, $etapeTraitement, $this->getUser(), 'SAISIE UNIVERSELLE', $this->getIpAddress());

        $statuService = new StatusImageService($em);
        $statuService->SetStatusImage($image);


        return new JsonResponse(['type' => 'success', 'title' => 'Saisie', 'message' => 'Enregistrement effectué']);
    }

    public function saveEcritureAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $action = '';
        $post = $request->request;

        $imageId = $post->get('imageid');
        if($imageId === '')
            throw new NotFoundHttpException('Image introuvable');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);

        if($image === null)
            throw new NotFoundHttpException('Image introuvable');

        $tvaEntity = $post->get('tvaentity');
        $tvaSaisieId = $post->get('tvaid');

        $typeVenteId = $post->get('typevente');
        $typeVente = null;
        if($typeVenteId !== '' && $typeVenteId !== null){
            $typeVente = $this->getDoctrine()
                ->getRepository('AppBundle:TypeVente')
                ->find($typeVenteId);
        }
        $libelle = $post->get('libelle');
        if($libelle === ''){
            $libelle = null;
        }
        $natureId = $post->get('nature');
        $nature = null;
        if($natureId !== '' && $natureId !== null)
            $nature = $this->getDoctrine()
                ->getRepository('AppBundle:Nature')
                ->find($natureId);

        $sousnatureId = $post->get('sousnature');
        $sousnature = null;
        if($sousnatureId !== '' && $sousnatureId !== null){
            $sousnature = $this->getDoctrine()
                ->getRepository('AppBundle:Sousnature')
                ->find($sousnatureId);
        }
        $souscategorieId = $post->get('souscategorie');
        $souscategorie = null;
        if($souscategorieId !== '' && $souscategorieId !== null){
            $souscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->find($souscategorieId);
        }
        $soussouscategorieId = $post->get('soussouscategorie');
        $soussouscategorie = null;
        if($soussouscategorieId !== '' && $soussouscategorieId !== null){
            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find($soussouscategorieId);
        }
        $dateLivraison = $post->get('datelivraisontva');
        if($dateLivraison !== ''){
            $dateLivraison = \DateTime::createFromFormat('d/m/Y', $dateLivraison);
        }
        else{
            $dateLivraison = null;
        }

        if($dateLivraison === false){
            $dateLivraison = null;
        }
        $periodeDebut = $post->get('periodedebuttva');
        if($periodeDebut !== ''){
            $periodeDebut = \DateTime::createFromFormat('d/m/Y', $periodeDebut);
        }
        else{
            $periodeDebut = null;
        }
        if($periodeDebut === false){
            $periodeDebut = null;
        }

        $periodeFin = $post->get('periodefintva');
        if($periodeFin !== ''){
            $periodeFin = \DateTime::createFromFormat('d/m/Y', $periodeFin);
        }
        else{
            $periodeFin = null;
        }
        if($periodeFin === false){
            $periodeFin = null;
        }

        $tiersId = $post->get('pcctiers');
        $tiers = null;
        if($tiersId !== '' && $tiersId !== null){
            $tiers = $this->getDoctrine()
                ->getRepository('AppBundle:Tiers')
                ->find($tiersId);
        }
        $montantTiers = $post->get('montanttiers');
        if($montantTiers === ''){
            $montantTiers = 0;
        }
        else {
            $montantTiers = $this->stringToNumber($montantTiers);
        }
        $pccTvaId = $post->get('pcctva');
        $pccTva = null;
        if($pccTvaId !==  '' && $pccTvaId !== null){
            $pccTva = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($pccTvaId);
        }
        $montantTva = $post->get('montanttva');
        if($montantTva === ''){
            $montantTva = 0;
        }
        $tvaTauxId = $post->get('tauxtva');
        $tvaTaux = null;
        if($tvaTauxId !== '' && $tvaTauxId !== null){
            $tvaTaux = $this->getDoctrine()
                ->getRepository('AppBundle:TvaTaux')
                ->find($tvaTauxId);
        }
        $pccDossierId = $post->get('pccdossier');
        $pccDossier = null;
        if($pccDossierId !== '' && $pccDossierId !== null){
            $pccDossier = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($pccDossierId);
        }
        $montantPcc = $post->get('montantpcc');
        if($montantPcc === ''){
            $montantPcc = 0;
        }
        else{
            $montantPcc = $this->stringToNumber($montantPcc);
        }

        $tvaSaisie = null;
        $canPersist = false;
        $imputation = false;

        $em = $this->getDoctrine()
            ->getManager();

        if(strpos($tvaSaisieId, 'new') !== false){
            foreach ($this->tvaEntities as $tvaEntity) {

                if($tvaEntity === 'TvaImputation' || $tvaEntity === 'TvaImputationControle'){
                    $imputation = true;
                }

                switch ($tvaEntity) {
                    case 'TvaSaisie1':
                        $tvaSaisie = new TvaSaisie1();
                        break;
                    case 'TvaSaisie2':
                        $tvaSaisie = new TvaSaisie2();
                        break;
                    case 'TvaSaisieControle':
                        $tvaSaisie = new TvaSaisieControle();
                        break;
                    case 'TvaImputation':
                        $tvaSaisie = new TvaImputation();
                        break;
                    case 'TvaImputationControle':
                        $tvaSaisie = new TvaImputationControle();
                        break;
                }
                if ($tvaSaisie !== null) {
                    $tvaSaisie->setImage($image);
                    $canPersist = true;
                }

                if ($tvaSaisie !== null) {

                    if($tvaEntity === 'TvaImputationControle'){
                        if($tvaTaux !== null){
                            $tvaSaisie->setTauxTva($tvaTaux->getTaux());
                        }
                        else {
                            $tvaSaisie->setTauxTva(0);
                        }
                    }

                    $tvaSaisie->setTypeVente($typeVente)
                        ->setDateLivraison($dateLivraison)
                        ->setPeriodeDeb($periodeDebut)
                        ->setPeriodeFin($periodeFin)
                        ->setTvaTaux($tvaTaux)
                        ->setSoussouscategorie($soussouscategorie)
                        ->setSousnature($sousnature)
                        ->setMontantTtc($montantPcc)
                        ->setNature2($nature)
                        ->setTiers($tiers)
                        ->setPccTva($pccTva)
                        ->setPcc($pccDossier)
                        ->setMontantHt($montantTiers);

                    if (!$imputation) {
                        $tvaSaisie->setPrelibelle($libelle);
                    } else {
                        $tvaSaisie->setLibelle($libelle);
                        $tvaSaisie->setSouscategorie($souscategorie);
                    }

                    if ($canPersist) {
                        $em->persist($tvaSaisie);
                    }

                    if($tvaEntity=== 'TvaImputationControle'){
                        $em->flush();
                        $action = 'insert';
                        $em->refresh($tvaSaisie);
                        $tvaSaisieId = $tvaSaisie->getId();
                    }
                }

            }
        }
        else {
            if($tvaEntity === 'TvaImputation' || $tvaEntity === 'TvaImputationControle'){
                $imputation = true;
            }

            $tvaSaisie = $this->getDoctrine()
                ->getRepository('AppBundle:' . $tvaEntity)
                ->find($tvaSaisieId);

            if ($tvaSaisie !== null) {

                $tvaSaisie->setTypeVente($typeVente)
                    ->setDateLivraison($dateLivraison)
                    ->setPeriodeDeb($periodeDebut)
                    ->setPeriodeFin($periodeFin)
                    ->setTvaTaux($tvaTaux)
                    ->setSoussouscategorie($soussouscategorie)
                    ->setSousnature($sousnature)
                    ->setMontantTtc($montantPcc)
                    ->setNature2($nature)
                    ->setTiers($tiers)
                    ->setPccTva($pccTva)
                    ->setPcc($pccDossier)
                    ->setMontantHt($montantTiers);

                if (!$imputation) {
                    $tvaSaisie->setPrelibelle($libelle);
                } else {
                    $tvaSaisie->setLibelle($libelle);
                    $tvaSaisie->setSouscategorie($souscategorie);
                }

                $action = 'update';
            }
        }


        $em->flush();


        $etapeTraitements = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getByCode('UNIVERSELLE_DETAILS', false);
        $etapeTraitement = null;

        if(count($etapeTraitements) > 0)
            $etapeTraitement = $etapeTraitements[0];

        $logService = new LogService($em);
        $logService->Save($image, $etapeTraitement, $this->getUser(), 'SAISIE UNIVERSELLE DETAILS', $this->getIpAddress());


        $statuService = new StatusImageService($em);
        $statuService->SetStatusImage($image);

        return new JsonResponse([
            'type' => 'success',
            'action' => $action,
            'message' => ($canPersist) ? 'Insertion effectuée' : 'Mise à jour effectuée',
            'id' => $tvaSaisieId
        ]);
    }

    public function deleteEcritureAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $tvaId = $post->get('tvaId');
        $tvaEntity = $post->get('tvaEntity');

        $tva = $this->getDoctrine()
            ->getRepository('AppBundle:' . $tvaEntity)
            ->find($tvaId);

        $type = 'success';
        $message = 'Suppression effectuée';
        if ($tva !== null) {
            $em = $this->getDoctrine()
                ->getManager();

            $em->remove($tva);
            $em->flush();
        } else {
            $type = 'error';
            $message = 'Ligne introuvable';
        }

        return new JsonResponse(['type' => $type, 'message' => $message]);
    }

    public function reglePaiementAction(Request $request, $regle){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $categorieid = $request->query->get('categoreid');

        //Fournisseur
        if(intval($categorieid) === 10)
            $typeTiers = 0;
        //Client
        else
            $typeTiers = 1;

        $imageid = $request->query->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            throw new NotFoundHttpException('Accès refusé');

        if($typeTiers === -1)
            throw new NotFoundHttpException('Categorie introuvable');

        $dossier = $image->getLot()->getDossier();
        /** @var ReglePaiementDossier[] $reglePaiementDossiers */
        $reglePaiementDossiers = $this->getDoctrine()
            ->getRepository('AppBundle:ReglePaiementDossier')
            ->findBy(['dossier'=> $dossier, 'typeTiers' => $typeTiers]);

        //Standard
        $res = [
            'id' => $dossier->getId(),
            'type' => 'standard',
            'typetiers' => $typeTiers,
            'datele' => -1,
            'nbrejour' => 30,
            'typedate' => 0
        ];

        if($regle === 'dossier') {
            if (count($reglePaiementDossiers) > 0) {
                $reglePaiementDossier = $reglePaiementDossiers[0];
                $res = [
                    'id' => $reglePaiementDossier->getId(),
                    'type' => 'dossier',
                    'typetiers' => $typeTiers,
                    'datele' => ($reglePaiementDossier->getDateLe() != null) ? $reglePaiementDossier->getDateLe() : -1,
                    'nbrejour' => $reglePaiementDossier->getNbreJour(),
                    'typedate' => $reglePaiementDossier->getTypeDate()
                ];
            } else {
                $client = $dossier->getSite()->getClient();
                $reglePaiementClients = $this->getDoctrine()
                    ->getRepository('AppBundle:ReglePaiementClient')
                    ->findBy(['client' => $client, 'typeTiers' => $typeTiers]);

                if (count($reglePaiementClients) > 0) {
                    $reglePaiementClient = $reglePaiementClients[0];
                    $res = [
                        'id' => $reglePaiementClient->getId(),
                        'type' => 'client',
                        'typetiers' => $typeTiers,
                        'datele' => ($reglePaiementClient->getDateLe() != null) ? $reglePaiementClient->getDateLe() : -1,
                        'nbrejour' => $reglePaiementClient->getNbreJour(),
                        'typedate' => $reglePaiementClient->getTypeDate()
                    ];
                }
            }
        }
        else{
            $regleEntity = $request->query->get('regleentity');
            $regleid = $request->query->get('regleid');

            if($regleEntity !== '') {
                $regleSaisie = $this->getDoctrine()
                    ->getRepository('AppBundle:' . $regleEntity)
                    ->find($regleid);

                if ($regleSaisie !== null) {
                    $res = [
                        'id' => $regleSaisie->getId(),
                        'type' => 'saisie',
                        'typetiers' => $typeTiers,
                        'datele' => ($regleSaisie->getDateLe() != null) ? $regleSaisie->getDateLe() : -1,
                        'nbrejour' => $regleSaisie->getNbreJour(),
                        'typedate' => $regleSaisie->getTypeDate()
                    ];
                }
            }
        }

        return $this->render('@Tenue/SaisieUniverselle/reglePaiement.html.twig',
            ['regle' => new \ArrayObject($res)]
        );
    }

    public function reglePaiementSaveAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageid = $post->get('imageid');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            return new JsonResponse(['type' => 'error', 'message' => 'image introuvable']);

        $res = [];

        $rpdate = $post->get('rpdate');
        $rpnbjour = $post->get('rpnbjour');
        $rpdatele = $post->get('rpdatele');
        $dateimage = $post->get('dateimage');
        $typeecheance = $post->get('typeecheance');

        if($dateimage !== ''){
            $dateimage = \DateTime::createFromFormat('d/m/Y', $dateimage);
            if($dateimage !== false){
                $dateimage->modify('+'.$rpnbjour.' days');
                if( date('N', $dateimage->getTimestamp()) === 6){
                    $dateimage->modify('+2 days');
                }
                else if(date('N', $dateimage->getTimestamp() === 0)){
                    $dateimage->modify('+1 days');
                }
            }
            $res['dateecheance'] = $dateimage->format('d/m/Y');
        }

        $em  = $this->getDoctrine()->getManager();
        foreach ($this->regleEntities as $regleEntity){
            $regles = $this->getDoctrine()
                ->getRepository('AppBundle:'.$regleEntity)
                ->findBy(['image' => $image]);

            $regle = null;
            $persist = false;
            if(count($regles) > 0){
                $regle = $regles[0];
            }
            else{
                switch ($regleEntity) {
                    case 'Saisie1RegleEcheance':
                        $regle = new Saisie1RegleEcheance();
                        break;
                    case 'Saisie2RegleEcheance':
                        $regle = new Saisie2RegleEcheance();
                        break;
                    case 'ControleRegleEcheance':
                        $regle = new ControleRegleEcheance();
                        break;
                    case 'ImputationRegleEcheance':
                        $regle = new ImputationRegleEcheance();
                        break;
                    case 'ImputationControleRegleEcheance':
                        $regle = new ImputationControleRegleEcheance();
                        break;
                }

                $regle->setImage($image);
                $persist = true;
            }

            $regle->setDateLe($rpdatele);
            $regle->setNbreJour($rpnbjour);
            $regle->setTypeDate($rpdate);
            $regle->setTypeEcheance($typeecheance);

            if($persist){
                $em->persist($regle);
            }
        }

        $em->flush();

        $res['type'] = 'success';
        $res['message'] = 'Enregistrement effectué';

        return new JsonResponse($res);
    }

    public function ecritureDossierAction(Request $request, $typeRecherche)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageid = $post->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);
        if ($image === null)
            throw new NotFoundHttpException('Image introuvable');

        $dossierid = $image->getLot()->getDossier()->getId();

        $ecritures = [];

        if($typeRecherche === 'siren') {

            $siren = $post->get('champ');

            if (trim($siren) !== '') {
                $siren = str_replace(' ', '', $siren);

                if(is_numeric($siren)) {
                    if ($this->checkLuhn($siren)) {
                        $ecritures = $this->getDoctrine()
                            ->getRepository('AppBundle:Ecriture')
                            ->getEcrituresByDossierSiren($dossierid, $siren);
                    } else {
                        throw  new NotFoundHttpException('siren invalide');
                    }
                }
            }
        }
        elseif ($typeRecherche === 'tiers') {
            $tiersId = $post->get('champ');

            $tiers = $this->getDoctrine()
                ->getRepository('AppBundle:Tiers')
                ->find($tiersId);

            if ($tiers !== null)
                $ecritures = $this->getDoctrine()
                    ->getRepository('AppBundle:Ecriture')
                    ->getEcrituresByDossierTiers($dossierid, $tiers->getId());
        }
        elseif ($typeRecherche === 'libelle'){
            $libelle = $post->get('champ');

            $ecritures = $this->getDoctrine()
                ->getRepository('AppBundle:Ecriture')
                ->getEcrituresByDossierLibelle($dossierid, $libelle);
        }

        $ecritureTemps = [];
        $imageFound = [];
        $tempPcc = [];
        $tempTiers = [];
        $tempTva = [];

        foreach ($ecritures as $ecriture) {

            $imageid = $ecriture->image_id;

            if (!in_array($imageid, $imageFound)) {
                $imageFound[] = $imageid;
                $tempPcc = [];
                $tempTiers = [];
                $tempTva = [];
            }

            if ($ecriture->pcc_id !== null) {
                $pcc = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->find($ecriture->pcc_id);

                if ($pcc !== null) {
                    if (strpos($pcc->getCompte(), '445') === 0) {
                        $tempTva[] = $pcc;
                    } else {
                        $tempPcc[] = $pcc;
                    }
                }
            } else {
                if ($ecriture->tiers_id !== null) {
                    $tempTiers[] = $this->getDoctrine()
                        ->getRepository('AppBundle:Tiers')
                        ->find($ecriture->tiers_id);
                }
            }

            $ecritureTemps[$ecriture->image_id] = [
                'tiers' => $tempTiers,
                'tva' => $tempTva,
                'pcc' => $tempPcc,
                'image' => $ecriture->image_nom,
                'libelle' => $ecriture->libelle,
                'image_id' => $ecriture->image_id
            ];
        }

        $rows = [];
        $occurences = [];
        $cells = [];

        foreach ($ecritureTemps as $key => $ecriture) {

            /** @var Pcc[] $pccs */
            $pccs = $ecriture['pcc'];
            /** @var Pcc[] $tvas */
            $tvas = $ecriture['tva'];
            /** @var Tiers[] $tiers */
            $tiers = $ecriture['tiers'];

            if (count($tiers) === 1 && count($tvas) <= 1 && count($pccs) >= 1)
            {
                foreach ($pccs as $pcc) {

                    $idtva = 0;
                    if(count($tvas) >0){
                        $idtva  = $tvas[0]->getId();
                    }
                    $idtmp = $pcc->getId().'-'.$tiers[0]->getId().'-'. $idtva;

                    if(!in_array($idtmp, $occurences)){
                        $occurences[] = $idtmp;

                        $cells [$idtmp]= [
                            'd_resultat' => $pcc->getCompte() . ' - ' . $pcc->getIntitule(),
                            'd_tva' => (count($tvas) > 0) ? $tvas[0]->getCompte() . ' - ' . $tvas[0]->getIntitule() : '',
                            'd_bilan' => $tiers[0]->getCompteStr() . ' - ' . $tiers[0]->getIntitule(),
                            'd_image' => $ecriture['image'],
                            'd_image_id' => $ecriture['image_id'],
                            'd_libelle' => $ecriture['libelle'],
                            'd_occurence' => 1
                        ];
                    }
                    else{
                        $cells[$idtmp]['d_image'].= ','.$ecriture['image'];
                        $cells[$idtmp]['d_image_id'].= ','.$ecriture['image_id'];
                        $images = $cells[$idtmp]['d_image'];
                        $nbOcc = count(explode(',', $images));
                        $cells[$idtmp]['d_occurence'] = $nbOcc;
                    }
                }
            }
        }


        foreach ($cells as $key => $cell){
            $rows[] = ['id' => $key, 'cell' => $cell];
        }

        return new JsonResponse(['rows' => $rows]);
    }

    public function recategoriserFormAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->query->get('imageid');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            throw new NotFoundHttpException('Image introuvable');

        $separations = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->findBy(['image' => $image]);

        /** @var Separation $separation */
        $separation = null;
        $categorie = null;
        $souscategories = [];
        $soussouscategories = [];

        if(count($separations) > 0){
            $separation = $separations[0];
            $categorie = $separation->getCategorie();
        }
        else{
            return new JsonResponse(['type' => 'error', 'message' => 'Image non separée']);
        }

        /** @var Categorie[] $categories */
        $categories = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->getAllCategories();

        if($categorie !== null) {
            /** @var Souscategorie[] $souscategories */
            $souscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Souscategorie')
                ->findBy(['categorie' => $categorie, 'actif' => 1]);
            /** @var Soussouscategorie[] $soussouscategories */
            $soussouscategories = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySouscategories($souscategories);
        }

        return $this->render('@Tenue/SaisieUniverselle/recategorisation.html.twig', [
            'image' => $image,
            'separation' => $separation,
            'categories' => $categories,
            'souscategories' => $souscategories,
            'soussouscategories' => $soussouscategories
        ]);
    }

    public function recategoriserAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $separationId = $post->get('separation');
        /** @var Separation $separation */
        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->find($separationId);

        $oldCategorie = null;
        $oldSouscategorie = null;
        $oldSoussouscategorie = null;

        $categorieId = $post->get('reccategorie');
        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find($categorieId);
        $souscategorieId = $post->get('recsouscategorie');
        $souscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find($souscategorieId);
        $soussouscategorieId = $post->get('recsoussouscategorie');
        if($soussouscategorieId !== null) {
            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->find($soussouscategorieId);
        }

        $imageId = $post->get('image');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageId);

        $dossier = $image->getLot()->getDossier();

        $reloadScreen = false;

        $em = $this->getDoctrine()->getManager();

        if ($separation !== null) {
            $oldCategorie = $separation->getCategorie();
            $oldSouscategorie = $separation->getSouscategorie();
            $oldSoussouscategorie = $separation->getSoussouscategorie();


            if ($oldCategorie !== $categorie || $oldSouscategorie !== $souscategorie || $oldSoussouscategorie !== $soussouscategorie) {

                if($oldCategorie !== $categorie){
                    $reloadScreen = true;
                }
                else{
                    if($categorie->getCode() === 'CODE_NDF'){
                        if($oldSouscategorie !== $souscategorie){
                            $reloadScreen = true;
                        }
                    }
                }

                $historiqueCategorie = new HistoriqueCategorie();
                $historiqueCategorie->setCategorie($oldCategorie);
                $historiqueCategorie->setSouscategorie($oldSouscategorie);
                $historiqueCategorie->setSoussouscategorie($oldSoussouscategorie);
                $historiqueCategorie->setImage($image);
                $historiqueCategorie->setOperateur($this->getUser());
                $historiqueCategorie->setMotif('SAISIE UNIVERSELLE');
                $historiqueCategorie->setDateModification(new \DateTime('now'));

                $em->persist($historiqueCategorie);

            }

            $separation->setCategorie($categorie)
                ->setSouscategorie($souscategorie)
                ->setSoussouscategorie($soussouscategorie);
        }
        else{
            $reloadScreen = true;

            $separation = new Separation();
            $separation->setImage($image)
                ->setCategorie($categorie)
                ->setSouscategorie($souscategorie)
                ->setSoussouscategorie($soussouscategorie)
                ->setOperateur($this->getUser());

            $em->persist($separation);

            if($image->getStatus() === 0){
                $image->setStatus(1);
            }
            /** @var ImageATraiter $imageATraiter */
            $imageATraiter = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->getImageAtraiterByImage($image);

            if($imageATraiter !== null){
                if($imageATraiter->getStatus() < 4){
                    $imageATraiter->setStatus(4);
                }
            }
        }

        $em->flush();

        $em->refresh($separation);



        return new JsonResponse([
            'type'=> 'success',
            'message' => 'enregistrement effectuée',
            'reloadScreen' => $reloadScreen,
            'title' => $this->render('@Tenue/SaisieUniverselle/titleSaisie.html.twig', [
                'dossier' => $dossier,
                'separation' => $separation
            ])->getContent()
        ]);

    }

    public function validerImageAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->query
            ->get('imageid');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->getImageSu($imageid);

        return $this->render('@Tenue/SaisieUniverselle/imageItem.html.twig',['image' => $image]);
    }

    public function saveBeneficiaireAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $nom = $post->get('bennom');
        $prenom = $post->get('benprenom');
        $mandataire = $post->get('benmandataire');
        $id = $post->get('benid');
        $imageid = $post->get('benimageid');

        if($mandataire === 'on'){
            $mandataire = 1;
        }
        else{
            $mandataire = 0;
        }

        if (trim($nom) === '' && trim($prenom) === '')
            return new JsonResponse(['message' => 'les champs nom et prenom ne peuvent être vides', 'type' => 'error']);


        $beneficiaire = $this->getDoctrine()
            ->getRepository('AppBundle:NdfUtilisateur')
            ->find($id);


        $em = $this->getDoctrine()->getManager();

        if($beneficiaire !== null){
            $beneficiaire->setNom($nom);
            $beneficiaire->setPrenom($prenom);
            $beneficiaire->setIsManager($mandataire);
            $em->flush();
            $message = 'mise à jour effectuée';
            $action = 'update';
        }
        else {
            /** @var Image $image */
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageid);

            if ($image === null)
                return new JsonResponse(['message' => 'image introuvable', 'type' => 'error']);

            $dossier = $image->getLot()->getDossier();

            $beneficiaire = new NdfUtilisateur();
            $beneficiaire->setNom($nom);
            $beneficiaire->setPrenom($prenom);
            $beneficiaire->setDossier($dossier);
            $beneficiaire->setIsManager($mandataire);

            $em->persist($beneficiaire);
            $em->flush();

            $em->refresh($beneficiaire);

            $message = 'Insertion effectuée';
            $action = 'insert';
        }

        $option = $this->render('@Tenue/SaisieUniverselle/optionBeneficiaire.html.twig',['beneficiaire' => $beneficiaire])
            ->getContent()
        ;


        $ret = [
            'message' => $message,
            'type' => 'success',
            'id' => $beneficiaire->getId(),
            'option' => $option,
            'action' => $action
        ];

        return new JsonResponse($ret);
    }

    public function ndfCaisseDetailsAction(Request $request, $categorie)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->request->get('imageid');

        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if ($image === null)
            throw new NotFoundHttpException('Image introuvable');

        $tvaEntity = '';
        $isImputation = false;
        if ($image->getCtrlImputation() >= 2) {
            $tvaEntity = 'TvaImputationControle';
            $isImputation = true;
        } elseif ($image->getImputation() >= 2) {
            $isImputation = true;
            $tvaEntity = 'TvaImputation';
        } elseif ($image->getCtrlSaisie() >= 2) {
            $tvaEntity = 'TvaSaisieControle';
        } elseif ($image->getSaisie2() >= 2) {
            $tvaEntity = 'TvaSaisie2';
        } elseif ($image->getSaisie1() >= 2) {
            $tvaEntity = 'TvaSaisie1';
        }

        $rows = [];
        $userData = [];

        if ($tvaEntity !== '') {
            /** @var TvaImputationControle[] $tvas */
            $tvas = $this->getDoctrine()
                ->getRepository('AppBundle:' . $tvaEntity)
                ->findBy(['image' => $image], ['rowId' => 'ASC']);


            $totalTtc = 0;
            $totalHt = 0;
            $totaltva = 0;
            $totalDevise = 0;

            $totalTtcS = 0;
            $totalHtS = 0;
            $totaltvaS = 0;


            foreach ($tvas as $tva) {

                $engagementTresorerie = $tva->getEngagementTresorerie();
                $et = '';

                if($engagementTresorerie === 0){
                    $et = 'Engagement';
                }
                elseif($engagementTresorerie === 1){
                    $et = 'Tresorerie';
                }

                switch ($categorie) {
                    case 'ndf':

                        $ndfDate = ($tva->getDateLivraison() === null) ? '' : $tva->getDateLivraison()->format('Y-m-d');
                        $ndfMdr = ($tva->getModeReglement() === null) ? '' : $tva->getModeReglement()->getLibelle();
                        switch ($tva->getNbreCouvert()) {
                            case 1:
                                $ndfNbCouvert = '1 Participant';
                                break;
                            case 2:
                                $ndfNbCouvert = 'Sup à 1 Participant';
                                break;
                            default:
                                $ndfNbCouvert = '';
                                break;
                        }
                        switch ($tva->getDistance()) {
                            case 0:
                                $ndfDistance = 'Inférieur à 50Km';
                                break;
                            case 1:
                                $ndfDistance = 'Supérieur à 50Km';
                                break;
                            default:
                                $ndfDistance = '';
                                break;
                        }


                        switch ($tva->getGroupe()) {
                            case 0:
                                $ndfGroupe = 'NG';
                                break;
                            case 1:
                                $ndfGroupe = 'G';
                                break;
                            default:
                                $ndfGroupe = '';
                                break;
                        }

                        $ndfPays = ($tva->getPays() === null) ? '' : $tva->getPays()->getNom();
                        $ndfDevise = ($tva->getDevise() === null) ? '' : $tva->getDevise()->getNom();

                        $ndfTvaTaux = ($tva->getTvaTaux() === null) ? '' : $tva->getTvaTaux()->getTaux();
                        $ndfMontantTtcDevise = ($tva->getMontantTtcDevise() === null) ? 0 : $tva->getMontantTtcDevise();
                        $ndfMontantTtc = ($tva->getMontantTtc() === null) ? 0 : $tva->getMontantTtc();
                        $ndfMontantHt = ($tva->getMontantHt() === null) ? 0 : $tva->getMontantHt();
                        $ndfMontantTva = $ndfMontantTtc - $ndfMontantHt;

                        $sousnature = $tva->getSousnature();

                        $ndfCategorie = ($sousnature === null) ? '' : $sousnature->getLibelle();
                        $ndfAction = $this->render('@Tenue/SaisieUniverselle/ndfGridAction.html.twig', ['categorie' => $categorie])->getContent();

                        $ndfPccHt = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            [
                                'pcc' => $tva->getPcc(),
                                'tiers' => $tva->getTiers(),
                                'type' => 'ht',
                                'categorie' => $categorie,
                                'et' => $engagementTresorerie
                            ]
                        )->getContent();

                        $ndfPccHtId = ($tva->getPcc() === null) ? '' : $tva->getPcc()->getId();


                        $ns = $this->checkTvaByNs($sousnature);

                        $ndfPccTva = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            [
                                'pcc' => $tva->getPccTva(),
                                'tiers' => null,
                                'type' => 'tva',
                                'categorie' => $categorie,
                                'ns' => $ns,
                                'et' => $engagementTresorerie
                            ]
                        )->getContent();

                        $ndfPccTvaId = ($tva->getPccTva() === null) ? '' : $tva->getPccTva()->getId();

                        $ndfPccTTC = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            ['pcc' => $tva->getPccBilan(), 'tiers' => null, 'type' => 'ttc', 'categorie' => $categorie]
                        )->getContent();

                        $ndfPccTTCId = ($tva->getPccBilan() === null) ? '' : $tva->getPccBilan()->getId();

                        $ndfTrajetIk = $tva->getTrajet();
                        $ndfVehiculeIk = ($tva->getVehicule() === null) ? -1 : $tva->getVehicule()->getId();

                        $ndfPeriodeDebutIk = ($tva->getPeriodeDeb() === null) ? '' : $tva->getPeriodeDeb()->format('d/m/Y');
                        $ndfPeriodeFinIk = ($tva->getPeriodeFin() === null) ? '' : $tva->getPeriodeFin()->format('d/m/Y');

                        $ndfIk = $this->render('@Tenue/SaisieUniverselle/ndfGridIkButton.html.twig',
                            ['sousnature' => $tva->getSousnature(), 'trajet' => $ndfTrajetIk])->getContent();

                        $totalTtc += $ndfMontantTtc;
                        $totalHt += $ndfMontantHt;
                        $totaltva += $ndfMontantTva;
                        $totalDevise += $ndfMontantTtcDevise;

                        $rows[] = ['id' => $tva->getId(), 'cell' => [
                            'ndf_row_id' => ($tva->getRowId() === null) ? -1 : $tva->getRowId(),
                            'ndf_date' => $ndfDate,
                            'ndf_categorie' => $ndfCategorie,
                            'ndf_mode_reglement' => $ndfMdr,
                            'ndf_distance' => $ndfDistance,
                            'ndf_pays' => $ndfPays,
                            'ndf_devise' => $ndfDevise,
                            'ndf_tva_taux' => $ndfTvaTaux,
                            'ndf_ttc_devise' => $ndfMontantTtcDevise,
                            'ndf_ttc' => $ndfMontantTtc,
                            'ndf_tva' => $ndfMontantTva,
                            'ndf_ht' => $ndfMontantHt,
                            'ndf_nbre_couvert' => $ndfNbCouvert,
                            'ndf_pcc_ttc' => $ndfPccTTC,
                            'ndf_pcc_ttc_id' => $ndfPccTTCId,
                            'ndf_pcc_tva' => $ndfPccTva,
                            'ndf_pcc_tva_id' => $ndfPccTvaId,
                            'ndf_pcc_ht' => $ndfPccHt,
                            'ndf_pcc_ht_id' => $ndfPccHtId,
                            'ndf_action' => $ndfAction,
                            'ndf_groupe' => $ndfGroupe,
                            'ndf_ik' => $ndfIk,
                            'ndf_trajet_ik' => $ndfTrajetIk,
                            'ndf_vehicule_ik' => $ndfVehiculeIk,
                            'ndf_periode_deb_ik' => $ndfPeriodeDebutIk,
                            'ndf_periode_fin_ik' => $ndfPeriodeFinIk,
                            'ndf_et' => $et
                        ]
                        ];

                        break;

                    case 'vc':
                        $vcDate = ($tva->getDateLivraison() === null) ? '' : $tva->getDateLivraison()->format('Y-m-d');

                        if ($isImputation) {
                            $vcLibelle = $tva->getLibelle();
                        } else {
                            $vcLibelle = $tva->getPreLibelle();
                        }
                        $vcModeReglement = ($tva->getModeReglement() === null) ? '' : $tva->getModeReglement()->getLibelle();
                        $vcCaisseNature = ($tva->getCaisseNature() === null) ? '' : $tva->getCaisseNature()->getLibelle();
                        $vcCaisseNatureId = ($tva->getCaisseNature() === null) ? -1 : $tva->getCaisseNature()->getId();
                        $vcCaisseType = ($tva->getCaisseType() === null) ? '' : $tva->getCaisseType()->getLibelle();
                        $vcCodeAnalytique = ($tva->getCodeAnalytique() === null) ? '' : $tva->getCodeAnalytique()->getCode();
                        $vcTvaTaux = ($tva->getTvaTaux() === null) ? '' : $tva->getTvaTaux()->getTaux();
                        $vcTtc = ($tva->getMontantTtc() === null) ? 0 : $tva->getMontantTtc();
                        $vcHt = ($tva->getMontantHt() === null) ? 0 : $tva->getMontantHt();
                        $vcTva = $vcTtc - $vcHt;

                        $vcAction = $this->render('@Tenue/SaisieUniverselle/ndfGridAction.html.twig', ['categorie' => $categorie])->getContent();

                        $vcPccHt = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            [
                                'pcc' => $tva->getPcc(),
                                'tiers' => $tva->getTiers(),
                                'type' => 'ht', 'categorie' => $categorie,
                                'et' => $engagementTresorerie
                            ]
                        )->getContent();

                        $vcPccHtId = ($tva->getPcc() === null) ? '' : $tva->getPcc()->getId();

                        $vcPccTva = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            [
                                'pcc' => $tva->getPccTva(),
                                'tiers' => null,
                                'type' => 'tva',
                                'categorie' => $categorie,
                                'et' => $engagementTresorerie
                            ]
                        )->getContent();

                        $vcPcctvaId = ($tva->getPcc() === null) ? '' : $tva->getPcc()->getId();

                        $vcPccTTC = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            ['pcc' => $tva->getPccBilan(), 'tiers' => null, 'type' => 'ttc', 'categorie' => $categorie]
                        )->getContent();

                        $vcPccTTCId = ($tva->getPccBilan() === null) ? '' : $tva->getPccBilan()->getId();

                        $totalTtc += $vcTtc;
                        $totalHt += $vcHt;
                        $totaltva += $vcTva;

                        $rows[] = ['id' => $tva->getId(), 'cell' => [
                            'vc_row_id' => ($tva->getRowId() === null) ? -1 : $tva->getRowId(),
                            'vc_date' => $vcDate,
                            'vc_libelle' => $vcLibelle,
                            'vc_mode_reglement' => $vcModeReglement,
                            'vc_caisse_nature' => $vcCaisseNature,
                            'vc_caisse_nature_id' => $vcCaisseNatureId,
                            'vc_caisse_type' => $vcCaisseType,
                            'vc_code_analytique' => $vcCodeAnalytique,
                            'vc_tva_taux' => $vcTvaTaux,
                            'vc_ttc' => $vcTtc,
                            'vc_ht' => $vcHt,
                            'vc_tva' => $vcTva,
                            'vc_pcc_ttc' => $vcPccTTC,
                            'vc_pcc_ttc_id' => $vcPccTTCId,
                            'vc_pcc_tva' => $vcPccTva,
                            'vc_pcc_tva_id' => $vcPcctvaId,
                            'vc_pcc_ht' => $vcPccHt,
                            'vc_pcc_ht_id' => $vcPccHtId,
                            'vc_action' => $vcAction,
                            'vc_et' => $et
                        ]
                        ];

                        break;

                    case 'c':


                        $cDate = ($tva->getDateLivraison() === null) ? '' : $tva->getDateLivraison()->format('Y-m-d');

                        if ($isImputation) {
                            $cLibelle = $tva->getLibelle();
                        } else {
                            $cLibelle = $tva->getPreLibelle();
                        }

                        $cSoldeInitial = ($tva->getSoldeFinal() === null) ? '0' : $tva->getSoldeInitial();
                        $cSoldeFinal = ($tva->getSoldeFinal() === null) ? '0' : $tva->getSoldeFinal();

                        $cCodeAnalytique = ($tva->getCodeAnalytique() === null) ? '' : $tva->getCodeAnalytique()->getCode();
                        $cModeReglement = ($tva->getModeReglement() === null) ? '' : $tva->getModeReglement()->getLibelle();

                        $cCaisseNatureE = '';
                        $cCaisseNatureS = '';
                        $cCaisseNatureSId = '';
                        $cCaisseNatureEId = '';
                        $cCaisseTypeE = '';
                        $cTvaTauxE = '';
                        $cTtcE = '';
                        $cHtE = '';
                        $cTvaE = '';

                        $cCaisseTypeS = '';
                        $cTvaTauxS = '';
                        $cTtcS = '';
                        $cHtS = '';
                        $cTvaS = '';

                        if ($tva->getEntreeSortie() === 1) {
                            $cCaisseNatureE = ($tva->getCaisseNature() === null) ? '' : $tva->getCaisseNature()->getLibelle();
                            $cCaisseNatureEId = ($tva->getCaisseNature() === null) ? -1 : $tva->getCaisseNature()->getId();
                            $cCaisseTypeE = ($tva->getCaisseType() === null) ? '' : $tva->getCaisseType()->getLibelle();
                            $cTvaTauxE = ($tva->getTvaTaux() === null) ? '' : $tva->getTvaTaux()->getTaux();
                            $cTtcE = ($tva->getMontantTtc() === null) ? 0 : $tva->getMontantTtc();
                            $cHtE = ($tva->getMontantHt() === null) ? 0 : $tva->getMontantHt();
                            $cTvaE = $cTtcE - $cHtE;
                        } else {
                            $cCaisseNatureS = ($tva->getCaisseNature() === null) ? '' : $tva->getCaisseNature()->getLibelle();
                            $cCaisseNatureSId = ($tva->getCaisseNature() === null) ? -1 : $tva->getCaisseNature()->getId();
                            $cCaisseTypeS = ($tva->getCaisseType() === null) ? '' : $tva->getCaisseType()->getLibelle();
                            $cTvaTauxS = ($tva->getTvaTaux() === null) ? '' : $tva->getTvaTaux()->getTaux();
                            $cTtcS = ($tva->getMontantTtc() === null) ? 0 : $tva->getMontantTtc();
                            $cHtS = ($tva->getMontantHt() === null) ? 0 : $tva->getMontantHt();
                            $cTvaS = $cTtcS - $cHtS;
                        }


                        $cAction = $this->render('@Tenue/SaisieUniverselle/ndfGridAction.html.twig', ['categorie' => $categorie])->getContent();

                        $cPccHt = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            [
                                'pcc' => $tva->getPcc(),
                                'tiers' => $tva->getTiers(),
                                'type' => 'ht', 'categorie' => $categorie,
                                'et' => $engagementTresorerie
                            ]
                        )->getContent();

                        $cPccHtId = ($tva->getPcc() === null) ? '' : $tva->getPcc()->getId();

                        $cPccTva = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            [
                                'pcc' => $tva->getPccTva(),
                                'tiers' => null,
                                'type' => 'tva',
                                'categorie' => $categorie,
                                'et' => $engagementTresorerie
                            ]
                        )->getContent();

                        $cPcctvaId = ($tva->getPcc() === null) ? '' : $tva->getPcc()->getId();

                        $cPccTTC = $this->render('@Tenue/SaisieUniverselle/ndfGridPccButton.html.twig',
                            ['pcc' => $tva->getPccBilan(), 'tiers' => null, 'type' => 'ttc', 'categorie' => $categorie]
                        )->getContent();

                        $cPccTTCId = ($tva->getPccBilan() === null) ? '' : $tva->getPccBilan()->getId();


                        $totalTtc += $cTtcE;
                        $totalHt += $cHtE;
                        $totaltva += $cTvaE;

                        $totalTtcS += $cTtcS;
                        $totalHtS += $cHtS;
                        $totaltvaS += $cTvaS;

                        $rows[] = ['id' => $tva->getId(), 'cell' => [
                            'c_row_id' => ($tva->getRowId() === null) ? -1 : $tva->getRowId(),
                            'c_date' => $cDate,
                            'c_libelle' => $cLibelle,
                            'c_mode_reglement' => $cModeReglement,
                            'c_code_analytique' => $cCodeAnalytique,
                            'c_es' => ($tva->getEntreeSortie() === 0) ? 'S' : 'E',
                            'c_caisse_nature_e' => $cCaisseNatureE,
                            'c_caisse_nature_e_id' => $cCaisseNatureEId,
                            'c_caisse_type_e' => $cCaisseTypeE,
                            'c_tva_taux_e' => $cTvaTauxE,
                            'c_ttc_e' => $cTtcE,
                            'c_ht_e' => $cHtE,
                            'c_tva_e' => $cTvaE,
                            'c_caisse_nature_s' => $cCaisseNatureS,
                            'c_caisse_nature_s_id' => $cCaisseNatureSId,
                            'c_caisse_type_s' => $cCaisseTypeS,
                            'c_tva_taux_s' => $cTvaTauxS,
                            'c_ttc_s' => $cTtcS,
                            'c_ht_s' => $cHtS,
                            'c_tva_s' => $cTvaS,
                            'c_pcc_ttc' => $cPccTTC,
                            'c_pcc_ttc_id' => $cPccTTCId,
                            'c_pcc_tva' => $cPccTva,
                            'c_pcc_tva_id' => $cPcctvaId,
                            'c_pcc_ht' => $cPccHt,
                            'c_pcc_ht_id' => $cPccHtId,
                            'c_solde_init' => $cSoldeInitial,
                            'c_solde_fin' => $cSoldeFinal,
                            'c_action' => $cAction,
                            'c_et' => $et
                        ]
                        ];


                        break;
                }
            }

            switch ($categorie) {
                case 'ndf':
                    $userData = [
                        'ndf_ttc' => $totalTtc,
                        'ndf_ht' => $totalHt,
                        'ndf_tva' => $totaltva,
                        'ndf_ttc_devise' => $totalDevise
                    ];
                    break;
                case 'vc':
                    $userData = [
                        'vc_ttc' => $totalTtc,
                        'vc_ht' => $totalHt,
                        'vc_tva' => $totaltva
                    ];
                    break;

                case 'c':
                    $userData = [
                        'c_ttc_e' => $totalTtc,
                        'c_ht_e' => $totalHt,
                        'c_tva_e' => $totaltva,
                        'c_ttc_s' => $totalTtcS,
                        'c_ht_s' => $totalHtS,
                        'c_tva_s' => $totaltvaS
                    ];
                    break;
                default:
                    break;
            }
        }
        return new JsonResponse(['rows' => $rows, 'userdata' => $userData]);
    }

    public function ndfCaisseDetailsEditAction(Request $request, $imageid, $categorie){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;
        $error = [];

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            return new JsonResponse(['message' => 'Image Introuvable', 'type' => 'error']);

        $tvaEntity = '';
        $tva = null;

        if ($image->getCtrlImputation() >= 2) {
            $tvaEntity = 'TvaImputationControle';
        } elseif ($image->getImputation() >= 2) {
            $tvaEntity = 'TvaImputation';
        } elseif ($image->getCtrlSaisie() >= 2) {
            $tvaEntity = 'TvaSaisieControle';
        } elseif ($image->getSaisie2() >= 2) {
            $tvaEntity = 'TvaSaisie2';
        } elseif ($image->getSaisie1() >= 2) {
            $tvaEntity = 'TvaSaisie1';
        }

        $id = $post->get('id');

        $tva = $this->getDoctrine()
            ->getRepository('AppBundle:'.$tvaEntity)
            ->find($id);

        if($tva !== null){
            $rowid = $tva->getRowId();
        }
        else {
            $maxRows = $this->getDoctrine()
                ->getRepository('AppBundle:' . $tvaEntity)
                ->findBy(['image' => $image], ['rowId' => 'DESC']);

            if (count($maxRows) > 0) {
                $rowid = $maxRows[0]->getRowId() + 1;
            }
            else{
                $rowid = 1;
            }
        }

        $date = \DateTime::createFromFormat('d/m/Y', $post->get($categorie.'_date'));
        if($date === false){
            $error[] = 'Date';
        }

        $sousnature = null;
        $nbreCouvert = null;
        $distance = null;
        $devise = null;
        $pays = null;
        $montantTtcDevise = null;

        $vehicule = null;
        $trajet = null;
        $periodeDebut = null;
        $periodeFin = null;

        $caisseNature = null;
        $caisseType = null;
        $codeAnalytique = null;
        $libelle = null;

        $es = '';
        $entreSortie = null;

        $soldeInit = null;
        $soldeFin = null;

        $et = $post->get($categorie.'_et');
        if(intval($et) === -1){
            $et = null;
        }

        switch($categorie) {
            case 'ndf':
                $sousnatureid = $post->get('ndf_categorie');

                $sousnature = $this->getDoctrine()
                    ->getRepository('AppBundle:Sousnature')
                    ->find($sousnatureid);
                if ($sousnature === null) {
                    $error[] = 'Categorie';
                }

                $nbreCouvert = $post->get('ndf_nbre_couvert');
                if (intval($nbreCouvert) === -1)
                    $nbreCouvert = null;

                $distance = $post->get('ndf_distance');
                if (intval($distance) === -1)
                    $distance = null;

                $paysId = $post->get('ndf_pays');
                $pays = $this->getDoctrine()
                    ->getRepository('AppBundle:Pays')
                    ->find($paysId);

                $deviseId = $post->get('ndf_devise');
                $devise = $this->getDoctrine()
                    ->getRepository('AppBundle:Devise')
                    ->find($deviseId);

                $montantTtcDevise = $post->get('ndf_ttc_devise');
                if ($montantTtcDevise === '')
                    $montantTtcDevise = 0;

                $vehiculeId = $post->get('ndf_vehicule_ik');
                if($vehiculeId !== null){
                    $vehicule = $this->getDoctrine()
                        ->getRepository('AppBundle:Vehicule')
                        ->find($vehiculeId);
                }

                $trajet = $post->get('ndf_trajet_ik');
                if($trajet === ''){
                    $trajet = null;
                }

                $periodeDebut = \DateTime::createFromFormat('d/m/Y', $post->get('ndf_periode_deb_ik'));
                if($periodeDebut === false){
                    $periodeDebut = null;
                }

                $periodeFin = \DateTime::createFromFormat('d/m/Y', $post->get('ndf_periode_fin_ik'));
                if($periodeFin === false){
                    $periodeFin = null;
                }
                break;

            case 'vc':
            case 'c':

                $esId = $post->get($categorie.'_es');
                if($esId !== null){
                    $es = (intval($esId) === 1) ? '_e' : '_s';
                    $entreSortie = intval($esId);
                }
                else if($categorie === 'vc'){
                    $entreSortie = 1;
                }

                $caisseNatureId = $post->get($categorie.'_caisse_nature'.$es);
                $caisseNature = $this->getDoctrine()
                    ->getRepository('AppBundle:CaisseNature')
                    ->find($caisseNatureId);

                $caisseTypeId = $post->get($categorie.'_caisse_type'.$es);
                if($caisseTypeId !== null) {
                    $caisseType = $this->getDoctrine()
                        ->getRepository('AppBundle:CaisseType')
                        ->find($caisseTypeId);
                }

                $codeAnalytiqueId = $post->get($categorie.'_code_analytique');
                $codeAnalytique = $this->getDoctrine()
                    ->getRepository('AppBundle:CodeAnalytique')
                    ->find($codeAnalytiqueId);

                $libelle = $post->get($categorie.'_libelle');
                if(trim($libelle) === ''){
                    $libelle = null;
                }

                if($categorie === 'c'){
                    $soldeInit = $post->get('c_solde_init');
                    if($soldeInit === ''){
                        $soldeInit = null;
                    }

                    $soldeFin = $post->get('c_solde_fin');
                    if($soldeFin === ''){
                        $soldeFin = null;
                    }
                }
                break;
        }

        $tvaPcc = null;
        $htPcc = null;
        $ttcPcc = null;

        $tvaPccId = $post->get($categorie.'_pcc_tva_id');
        if($tvaPccId !== null) {
            $tvaPcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($tvaPccId);
        }
        $htPccId = $post->get($categorie.'_pcc_ht_id');

        if($htPccId !== null) {
            $htPcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($htPccId);
        }

        $ttcPccId = $post->get($categorie.'_pcc_ttc_id');
        if($ttcPccId !== null) {
            $ttcPcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($ttcPccId);
        }

        $modeReglementId = $post->get($categorie.'_mode_reglement');
        $modeReglement = $this->getDoctrine()
            ->getRepository('AppBundle:ModeReglement')
            ->find($modeReglementId);

        $tvaTauxId = $post->get($categorie.'_tva_taux'.$es);
        $tvaTaux = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->find($tvaTauxId);

        $montantHt = $post->get($categorie.'_ht'.$es);
        if($montantHt === '')
            $montantHt = 0;

        $montantTtc = $post->get($categorie.'_ttc'.$es);
        if($montantTtc === '')
            $montantTtc = 0;

        $em = $this->getDoctrine()->getManager();

        foreach ($this->tvaEntities as $tvaEntity){
            $persist = false;
            $inImputation = false;

            if($tvaEntity === 'TvaImputation' || $tvaEntity === 'TvaImputationControle'){
                $inImputation = true;
            }

            $tvas = $this->getDoctrine()
                ->getRepository('AppBundle:'.$tvaEntity)
                ->findBy(['image' => $image, 'rowId' => $rowid]);

            if(count($tvas) > 0){
                $tva = $tvas[0];
            }
            else{
                $persist = true;
                switch ($tvaEntity) {
                    case 'TvaSaisie1':
                        $tva = new TvaSaisie1();
                        break;
                    case 'TvaSaisie2':
                        $tva = new TvaSaisie2();
                        break;
                    case 'TvaSaisieControle':
                        $tva = new TvaSaisieControle();
                        break;
                    case 'TvaImputation':
                        $tva = new TvaImputation();
                        break;
                    case 'TvaImputationControle':
                        $tva = new TvaImputationControle();
                        break;
                }
            }

            $tva->setEntreeSortie($entreSortie);
            $tva->setSousnature($sousnature);
            $tva->setMontantTtc($montantTtc);
            $tva->setMontantHt($montantHt);
            $tva->setMontantTtcDevise($montantTtcDevise);
            $tva->setModeReglement($modeReglement);
            $tva->setNbreCouvert($nbreCouvert);
            $tva->setDistance($distance);
            $tva->setPays($pays);
            $tva->setDevise($devise);
            $tva->setTvaTaux($tvaTaux);
            $tva->setDateLivraison($date);
            $tva->setPcc($htPcc);
            $tva->setPccTva($tvaPcc);
            $tva->setPccBilan($ttcPcc);
            $tva->setSoldeInitial($soldeInit);
            $tva->setSoldeFinal($soldeFin);

            $tva->setVehicule($vehicule);
            $tva->setTrajet($trajet);
            $tva->setPeriodeDeb($periodeDebut);
            $tva->setPeriodeFin($periodeFin);
            $tva->setEngagementTresorerie($et);

            if(!$inImputation){
                $tva->setPrelibelle($libelle);
            }
            else{
                $tva->setLibelle($libelle);
            }

            $tva->setCaisseNature($caisseNature);
            $tva->setCaisseType($caisseType);
            $tva->setCodeAnalytique($codeAnalytique);

            if($persist){
                $tva->setImage($image);
                $tva->setRowId($rowid);
                $em->persist($tva);
            }
        }

        $em->flush();

        $etapeTraitements = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getByCode('UNIVERSELLE_DETAILS', false);
        $etapeTraitement = null;
        if(count($etapeTraitements) > 0)
            $etapeTraitement = $etapeTraitements[0];

        $logService = new LogService($em);
        $logService->Save($image, $etapeTraitement, $this->getUser(), 'SAISIE UNIVERSELLE DETAILS', $this->getIpAddress());


        $statuService = new StatusImageService($em);
        $statuService->SetStatusImage($image);

        //Enregistrement TD
        $dossier = $image->getLot()->getDossier();

        if($categorie === 'ndf'){
            if($htPcc !== null && $sousnature !== null) {
                /** @var TdNdfSousnaturePcc $td */
                $td = $this->getDoctrine()
                    ->getRepository('AppBundle:TdNdfSousnaturePcc')
                    ->getTdNdfSousnaturePccByCriteres($dossier, $sousnature, $nbreCouvert, $distance);

                if ($td === null) {
                    $td = new TdNdfSousnaturePcc();
                    $td->setSousnature($sousnature);
                    $td->setPccTva($tvaPcc);
                    $td->setPccResultat($htPcc);
                    $td->setDistance($distance);
                    $td->setNbParticipant($nbreCouvert);

                    $em->persist($td);
                }
                else{
                    $td->setPccTva($tvaPcc);
                    $td->setPccResultat($htPcc);
                }

                $em->flush();
            }

            $typeCompte = 0;

            if($dossier->getFormeJuridique() !== null){
                if($dossier->getFormeJuridique()->getCode() === 'CODE_ENTREPRISE_INDIVIDUELLE'){
                    $typeCompte =1;
                }
            }

            if($ttcPcc !== null) {
                /** @var TdNdfBilanPcc $tdBilan */
                $tdBilan = $this->getDoctrine()
                    ->getRepository('AppBundle:TdNdfBilanPcc')
                    ->getTdNdfBilanPccByDossierTypeCompte($dossier, $typeCompte);

                if ($tdBilan === null) {
                    $tdBilan = new TdNdfBilanPcc();
                    $tdBilan->setPcc($ttcPcc);
                    $tdBilan->setDossier($dossier);
                    $tdBilan->setTypeCompte($typeCompte);

                    $em->persist($tdBilan);
                } else {
                    $tdBilan->setPcc($ttcPcc);
                }
            }
            $em->flush();
        }

        else if($categorie === 'vc' || $categorie === 'c'){
            $typeCaisse = 0;

            if($categorie === 'c'){
                $typeCaisse = 1;
            }

            if($ttcPcc !== null) {
                /** @var TdCaisseBilanPcc $tdBilan */
                $tdBilan = $this->getDoctrine()
                    ->getRepository('AppBundle:TdCaisseBilanPcc')
                    ->getTdCaisseBilanPccByDossier($dossier, $typeCaisse);

                if ($tdBilan === null) {
                    $tdBilan = new TdCaisseBilanPcc();
                    $tdBilan->setTypeCaisse($typeCaisse);
                    $tdBilan->setDossier($dossier);
                    $tdBilan->setPcc($ttcPcc);

                    $em->persist($tdBilan);
                } else {
                    $tdBilan->setPcc($ttcPcc);
                }
            }

            if($tvaPcc !== null && $tvaTaux !== null) {
                /** @var TdTvaPcc $tdTva */
                $tdTva = $this->getDoctrine()
                    ->getRepository('AppBundle:TdTvaPcc')
                    ->getTdByDossierTauxType($dossier, $tvaTaux, $typeCaisse);

                if($tdTva === null){
                    $tdTva = new TdTvaPcc();
                    $tdTva->setTvaTaux($tvaTaux);
                    $tdTva->setTypeCaisse($typeCaisse);
                    $tdTva->setPcc($tvaPcc);

                    $em->persist($tdTva);
                }
                else{
                    $tdTva->setPcc($tvaPcc);
                }
            }

            if($htPcc !== null && $caisseNature !== null) {
                /** @var TdCaisseResultatPcc $td */
                $td = $this->getDoctrine()
                    ->getRepository('AppBundle:TdCaisseResultatPcc')
                    ->getTdCaisseResultatPccByDossierNature($dossier, $caisseNature, $caisseType);

                if($td === null){
                    $td = new TdCaisseResultatPcc();

                    $td->setPcc($htPcc);
                    $td->setCaisseNature($caisseNature);
                    $td->setCaisseType($caisseType);

                    $em->persist($td);
                }
                else{
                    $td->setPcc($htPcc);
                }
            }
            $em->flush();
        }

        return new JsonResponse(['message' => 'Mise à jour effectuée', 'type' => 'success']);
    }

    public function ndfCaisseDetailsPccEditAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageid = $post->get('imageid');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            throw new NotFoundHttpException('Image introuvable');

        $typepcc = $post->get('typepcc');
        $pccid = $post->get('pccid');
        $pcc = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->find($pccid);

        $rowid = $post->get('rowid');

        $em = $this->getDoctrine()->getManager();

        foreach ($this->tvaEntities as $tvaEntity){
            /** @var TvaSaisie1[] $tvas */
            $tvas = $this->getDoctrine()
                ->getRepository('AppBundle:'.$tvaEntity)
                ->findBy(['image' => $image, 'rowId' => $rowid]);

            if(count($tvas) > 0){
                $tva = $tvas[0];
                switch ($typepcc){
                    case 'ttc':
                        $tva->setPccBilan($pcc);
                        break;
                    case 'ht':
                        $tva->setPcc($pcc);
                        break;
                    case 'tva':
                        $tva->setPccTva($pcc);
                        break;
                    default:
                        break;
                }
            }
        }
        $em->flush();

        return new JsonResponse(['type' => 'success', 'message' => 'Mise à jour effectuée']);


    }

    public function ndfCaisseDetailsDeleteAction(Request $request, $imageid){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $id = $request->request->get('id');
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image === null)
            throw new NotFoundHttpException('Image introuvable');

        $tvaEntity = '';

        if ($image->getCtrlImputation() >= 2) {
            $tvaEntity = 'TvaImputationControle';
        } elseif ($image->getImputation() >= 2) {
            $tvaEntity = 'TvaImputation';
        } elseif ($image->getCtrlSaisie() >= 2) {
            $tvaEntity = 'TvaSaisieControle';
        } elseif ($image->getSaisie2() >= 2) {
            $tvaEntity = 'TvaSaisie2';
        } elseif ($image->getSaisie1() >= 2) {
            $tvaEntity = 'TvaSaisie1';
        }

        /** @var TvaSaisie1 $tva */
        $tva = $this->getDoctrine()
            ->getRepository('AppBundle:'.$tvaEntity)
            ->find($id);

        if($tva === null)
            throw new NotFoundHttpException('Ligne introuvable');

        $image = $tva->getImage();
        $rowid = $tva->getRowId();


        $em = $this->getDoctrine()->getManager();

        foreach ($this->tvaEntities as $tvaEntity){
            $tvas = $this->getDoctrine()
                ->getRepository('AppBundle:'.$tvaEntity)
                ->findBy(['image' => $image, 'rowId' => $rowid]);

            if(count($tvas) > 0){
                $em->remove($tvas[0]);
            }
        }

        $em->flush();


        $etapeTraitements = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getByCode('UNIVERSELLE_DETAILS', false);
        $etapeTraitement = null;
        if(count($etapeTraitements) > 0)
            $etapeTraitement = $etapeTraitements[0];

        $logService = new LogService($em);
        $logService->Save($image, $etapeTraitement, $this->getUser(), 'SAISIE UNIVERSELLE DETAILS', $this->getIpAddress());


        $statuService = new StatusImageService($em);
        $statuService->SetStatusImage($image);

        return new JsonResponse(['type' => 'success', 'message' => 'Ligne supprimée avec succès']);
    }

    public function modeReglementAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $modeReglements = $this->getDoctrine()
            ->getRepository('AppBundle:ModeReglement')
            ->findBy([],['libelle' => 'ASC']);

        return $this->render('@Tenue/SaisieUniverselle/optionModeReglement.html.twig', ['modeReglements' => $modeReglements]);
    }

    public function caisseNatureAction(Request $request, $type){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        /** @var CaisseNature[] $caisseNatures */
        $caisseNatures = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->getCaisseNature($type);

        return $this->render('@Tenue/SaisieUniverselle/optionCaisseNature.html.twig',['caisseNatures' => $caisseNatures]);
    }

    public function caisseTypeAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $caisseTypes = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseType')
            ->findBy([], ['libelle' => 'ASC']);

        return $this->render('@Tenue/SaisieUniverselle/optionCaisseType.html.twig',['caisseTypes' => $caisseTypes]);
    }

    public function codeAnalytiqueAction(Request $request, $imageid)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $analytiques = [];
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if ($image !== null) {

            $dossier = $image->getLot()->getDossier();
            /** @var CodeAnalytique[] $analytiques */
            $analytiques = $this->getDoctrine()
                ->getRepository('AppBundle:CodeAnalytique')
                ->getCodeAnalytiques($dossier);
        }

        return $this->render('@Tenue/SaisieUniverselle/optionCodeAnalytique.html.twig',['codeAnalytiques' => $analytiques]);
    }

    public function conditionDepenseAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $conditionDepenses = $this->getDoctrine()
            ->getRepository('AppBundle:ConditionDepense')
            ->findBy([],['libelle' => 'ASC']);

        return $this->render('@Tenue/SaisieUniverselle/optionConditionDepense.hmtl.twig',['conditionDepenses' => $conditionDepenses]);
    }

    public function paysAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Pays[] $pays */
        $pays = $this->getDoctrine()
            ->getRepository('AppBundle:Pays')
            ->findBy([],['nom' => 'ASC']);

        return $this->render('@Tenue/SaisieUniverselle/optionPays.html.twig',['pays' => $pays]);

    }

    public function deviseAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var Devise[] $devises */
        $devises = $this->getDoctrine()
            ->getRepository('AppBundle:Devise')
            ->findBy([],['nom' => 'ASC']);

        return $this->render('@Tenue/SaisieUniverselle/optionDevise.html.twig',['devises' => $devises]);

    }

    public function calculDeviseAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;
        $montant = $post->get('montant');
        $deviseid = $post->get('deviseid');

        $devise = $this->getDoctrine()
            ->getRepository('AppBundle:Devise')
            ->find($deviseid);

        if($devise !== null){
            /** @var DeviseTaux $tauxDevise */
            $tauxDevise = $this->getDoctrine()
                ->getRepository('AppBundle:DeviseTaux')
                ->getMostRecentDevise($devise);

            if($tauxDevise !== null){
                $taux = floatval($tauxDevise->getTaux());

                if($taux !== 0){
                    $montant = floatval($montant) / $taux;
                }

            }
        }

        return new Response(round(floatval($montant), 2));
    }

    public function calculTvaHtAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $montant = $post->get('montant');
        $tvatauxid = $post->get('tvatauxid');
        $montantHt = $montant;
        $montantTva = 0;

        if($montant !== ''){
            $montant = floatval($montant);
        }

        $tvataux = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->find($tvatauxid);

        if($tvataux !== null){
            $montantHt = round(($montant * 100) / (100+ $tvataux->getTaux()), 2);
            $montantTva = round(($montant - $montantHt) , 2);
        }
        return new JsonResponse(['ht' => $montantHt, 'tva' => $montantTva]);
    }

    public function tvaTauxAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        /** @var TvaTaux[] $tvaTauxs */
        $tvaTauxs = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->getTvaTaux(true);

        return $this->render('@Tenue/SaisieUniverselle/optionTvaTaux.html.twig', ['tvaTauxs' => $tvaTauxs]);
    }

    public function nbreCouvertAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accè refusé');

        return $this->render('@Tenue/SaisieUniverselle/optionNbreCouvert.html.twig');
    }
    
    public function checkTvaNsAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');
        
        $sousnatureid = $request->query->get('sousnatureid');
        
        $sousnature = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->find($sousnatureid);

        return new JsonResponse($this->checkTvaByNs($sousnature));
    }


    public function tdNdfPcgAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $imageid = $post->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if ($image === null)
            throw new NotFoundHttpException('Image introuvable');

        /** @var Dossier $dossier */
        $dossier = $image->getLot()->getDossier();

        $sousnatureid = $post->get('sousnature');
        $sousnature = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->find($sousnatureid);

        $nbCouvert = $post->get('nbcouvert');
        if (intval($nbCouvert) === -1)
            $nbCouvert = null;

        $distance = $post->get('distance');
        if (intval($distance) === -1)
            $distance = null;


        $type = 'success';
        $message = '';

        $typePcc = $post->get('typepcc');
        $data = [];

        $typeCompte = -1;
        $intituleTypeCompte = '';

        switch ($typePcc) {
            case 'tva':
                $typeCompte = 2;
                $intituleTypeCompte = 'TVA';
                break;

            case 'ht':
                $typeCompte = 1;
                $intituleTypeCompte = 'Resultat';
                break;

            case 'ttc':
                $typeCompte = 0;
                $intituleTypeCompte = 'Bilan';
                break;

            default:
                break;
        }

        /** @var Pcg[] $pcgs */
        $pcgs = $this->getDoctrine()
            ->getRepository('AppBundle:SoussouscategorieCompte')
            ->getPcgsBySousnature($sousnature, $typeCompte);


        if (count($pcgs) === 0) {
            $type = 'error';
            $message = 'Pas de compte '.$intituleTypeCompte.' dans la table pour ' . $sousnature->getLibelle();
        }

        foreach ($pcgs as $pcg) {

            $id = 'pcg_' . $pcg->getId();
            $data [] = [
                'id' => $id,
                'parent' => '#',
                'text' => $pcg->getCompte() . '-' . $pcg->getIntitule()
            ];
            /** @var Pcc[] $pccs */
            $pccs = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, $pcg->getCompte());

            foreach ($pccs as $pc) {
                $data[] = [
                    'id' => 'pcc_' . $pc->getId(),
                    'parent' => $id,
                    'text' => $pc->getCompte() . ' - ' . $pc->getIntitule()
                ];
            }
        }

        return new JsonResponse(['data' => $data,
            'type' => $type,
            'message' => $message
        ]);
    }

    public function tdNdfSousnaturePccEcritureAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $sousnatureId = $request->query
            ->get('sousnatureid');

        $imageid = $request->query
            ->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if(!$image)
            throw new NotFoundHttpException('Image non trouvée');

        $dossier = $image->getLot()->getDossier();

        $sousnature = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->find($sousnatureId);

        /** @var Pcg[] $pcgs */
        $pcgs = $this->getDoctrine()
            ->getRepository('AppBundle:SoussouscategorieCompte')
            ->getPcgsBySousnature($sousnature, 1);

        /** @var Pcc[] $pccs */
        $pccs = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->getPccByDossierLike($dossier, '6');

        if(count($pcgs) > 0){
            $pccs = [];

            foreach ($pcgs as $pcg){
                $pccTmps = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, $pcg->getCompte());


                foreach ($pccTmps as $pccTmp){
                    $pccs []= $pccTmp;
                }
            }
        }

        return $this->render('@Tenue/SaisieUniverselle/optionPcc.html.twig',['pccs' => $pccs]);
    }

    public function tdNdfSousnaturePccAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $imageid = $post->get('imageid');
        $sousnatureid  = $post->get('sousnatureid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $sousnature = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->find($sousnatureid);

        if($image === null || $sousnature === null)
            throw new NotFoundHttpException('Image et/ou sousnature introuvable');

        $nbcouvert = $post->get('nbcouvert');

        if(intval($nbcouvert) === -1){
            $nbcouvert = null;
        }

        $distance = $post->get('distance');
        if(intval($distance) === -1){
            $distance = null;
        }


        $typeCompte = 0;

        $dossier = $image->getLot()->getDossier();

        if($dossier->getFormeJuridique() !==null){
            if($dossier->getFormeJuridique()->getCode() === 'CODE_ENTREPRISE_INDIVIDUELLE'){
                $typeCompte = 1;
            }
        }

        /** @var TdNdfBilanPcc $tdBilan */
        $tdBilan = $this->getDoctrine()
            ->getRepository('AppBundle:TdNdfBilanPcc')
            ->getTdNdfBilanPccByDossierTypeCompte($dossier, $typeCompte);

        $bilanId = -1;
        $bilan = 'Clicker ici';

        $resultatId = -1;
        $resultat = 'Clicker ici';

        $tvaId = -1;
        $tva = 'Clicker ici';



        if($tdBilan !== null){
            $pccBilan = $tdBilan->getPcc();

            if($pccBilan !== null){
                $bilanId = $pccBilan->getId();
                $bilan = $pccBilan->getCompte();
            }
        }

        /** @var TdNdfSousnaturePcc $td */
        $td = $this->getDoctrine()
            ->getRepository('AppBundle:TdNdfSousnaturePcc')
            ->getTdNdfSousnaturePccByCriteres($dossier, $sousnature, $nbcouvert, $distance);


        if($td !== null){
            $pccResultat = $td->getPccResultat();


            if($pccResultat !== null){
                $resultatId = $pccResultat->getId();
                $resultat = $pccResultat->getCompte();
            }

            $pccTva = $td->getPccTva();

            if($pccTva !== null){
                $tvaId = $pccTva->getId();
                $tva = $pccTva->getCompte();
            }

        }


        return new JsonResponse([
            'type' => 'success',
            'resultatid' => $resultatId,
            'resultat' => $resultat,
            'tvaid' => $tvaId,
            'tva' => $tva,
            'bilan' => $bilan,
            'bilanid' => $bilanId
        ]);
    }


    public function tdVcPcgAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $imageid = $post->get('imageid');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if ($image === null)
            throw new NotFoundHttpException('Image introuvable');

        /** @var Dossier $dossier */
        $dossier = $image->getLot()->getDossier();

        $typePcc = $post->get('typepcc');
        $data = [];

        $caisseNatureId = $post->get('caissenatureid');
        $caisseNature = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->find($caisseNatureId);

        $pcgs = [];
        switch ($typePcc){
            case 'ht':
                if($caisseNature !== null) {
                    /** @var TdCaisseResultatPcg[] $tdPcgs */
                    $tdPcgs = $this->getDoctrine()
                        ->getRepository('AppBundle:TdCaisseResultatPcg')
                        ->getTdCaisseResultatPcgByNature($caisseNature);
                    foreach ($tdPcgs as $tdPcg){
                        $pcgs[] = $tdPcg->getPcg();
                    }
                }
                break;
            case 'ttc':
                $tdPcgs = $this->getDoctrine()
                    ->getRepository('AppBundle:TdCaisseBilanPcg')
                    ->findAll();
                foreach ($tdPcgs as $tdPcg){
                    $pcgs[] = $tdPcg->getPcg();

                }
                break;
            case 'tva':
                /** @var TdTvaPcg[] $tdPcgs */
                $tdPcgs = $this->getDoctrine()
                    ->getRepository('AppBundle:TdTvaPcg')
                    ->findBy(['typeCaisse' => 0]);
                foreach ($tdPcgs as $tdPcg){
                    $pcgs[] = $tdPcg->getPcg();
                }
                break;
        }

        foreach ($pcgs as $pcg) {
            if ($pcg !== null) {
                $id = 'pcg_' . $pcg->getId();
                $data [] = [
                    'id' => $id,
                    'parent' => '#',
                    'text' => $pcg->getCompte() . '-' . $pcg->getIntitule()
                ];
                /** @var Pcc[] $pccs */
                $pccs = $this->getDoctrine()
                    ->getRepository('AppBundle:Pcc')
                    ->getPccByDossierLike($dossier, $pcg->getCompte());

                foreach ($pccs as $pc) {
                    $data[] = [
                        'id' => 'pcc_' . $pc->getId(),
                        'parent' => $id,
                        'text' => $pc->getCompte() . ' - ' . $pc->getIntitule()
                    ];
                }
            }
        }

        return new JsonResponse(['data' => $data]);
    }


    public function tdTdCaissePccAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $imageid = $post->get('imageid');
        $caissenatureid  = $post->get('caissenatureid');
        $caissetypeid  = $post->get('caissetypeid');
        $tvaid = $post->get('tvaid');
        $categorie = $post->get('categorie');
        $typeCaisse = 0;

        if($categorie === '')
            $typeCaisse = 1;

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $caisseNature = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->find($caissenatureid);

        if($image === null || $caisseNature === null)
            throw new NotFoundHttpException('Image et/ou nature introuvable');

        $dossier = $image->getLot()->getDossier();

        $caisseType = null;
        if($caissetypeid !== null) {
            $caisseType = $this->getDoctrine()
                ->getRepository('AppBundle:CaisseType')
                ->find($caissetypeid);
        }

        $tva = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->find($tvaid);

        $tdResultat = null;
        
        if($caisseNature !== null) {
            /** @var TdCaisseResultatPcc $tdResultat */
            $tdResultat = $this->getDoctrine()
                ->getRepository('AppBundle:TdCaisseResultatPcc')
                ->getTdCaisseResultatPccByDossierNature($dossier, $caisseNature, $caisseType);
        }
        
        /** @var TdCaisseBilanPcc $tdCaisseBilan */
        $tdCaisseBilan = $this->getDoctrine()
            ->getRepository('AppBundle:TdCaisseBilanPcc')
            ->getTdCaisseBilanPccByDossier($dossier, $typeCaisse);

        $tdTva = null;
        if($tva !== null) {
            /** @var TdTvaPcc $tdTva */
            $tdTva = $this->getDoctrine()
                ->getRepository('AppBundle:TdTvaPcc')
                ->getTdByDossierTauxType($dossier, $tva, $typeCaisse);
        }
        $bilanId = -1;
        $bilan = 'Clicker ici';
        if($tdCaisseBilan !== null){
            if($tdCaisseBilan->getPcc() != null){
                $bilanId = $tdCaisseBilan->getPcc()->getId();
                $bilan = $tdCaisseBilan->getPcc()->getCompte();
            }
        }

        $resultatId = -1;
        $resultat = 'Clicker ici';
        if($tdResultat !== null){
            if($tdResultat->getPcc() !== null){
                $resultatId = $tdResultat->getPcc()->getId();
                $resultat = $tdResultat->getPcc()->getCompte();
            }
        }

        $tvaId = -1;
        $tva = 'Clicker ici';
        if($tdTva !== null) {
            if ($tdTva->getPcc() !== null) {
                $tvaId = $tdTva->getPcc()->getId();
                $tva = $tdTva->getPcc()->getCompte();
            }
        }

        return new JsonResponse([
            'type' => 'success',
            'resultatid' => $resultatId,
            'resultat' => $resultat,
            'tvaid' => $tvaId,
            'tva' => $tva,
            'bilan' => $bilan,
            'bilanid' => $bilanId
        ]);
    }

    public function vehiculeAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');


        $imageid = $request->query->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $dossier = $image->getLot()->getDossier();

        /** @var Vehicule[] $vehicules */
        $vehicules = $this->getDoctrine()
            ->getRepository('AppBundle:Vehicule')
            ->getVehiculeByDossier($dossier);


        return $this->render('@Tenue/SaisieUniverselle/optionVehicules.html.twig',['vehicules' => $vehicules]);

    }

    public function saveVehiculeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $error =[];

        $marqueId = $post->get('marque');
        $marque  = $this->getDoctrine()
            ->getRepository('AppBundle:VehiculeMarque')
            ->find($marqueId);

        $modele = $post->get('modele');
        $immatricule = $post->get('immatricule');

        $typeVehiculeId = $post->get('typevehicule');
        $typeVehicule = $this->getDoctrine()
            ->getRepository('AppBundle:TypeVehicule')
            ->find($typeVehiculeId);
        if(!$typeVehicule){
            $error[] = 'Type vehicule';
        }

        $ndfTypeVehiculeId = $post->get('ndftypevehicule');
        $ndfTypeVehicule = $this->getDoctrine()
            ->getRepository('AppBundle:NdfTypeVehicule')
            ->find($ndfTypeVehiculeId);

        if(!$ndfTypeVehicule){
            $error[] = 'NDF Type vehicule';
        }

        $carburantId = $post->get('carburant');
        $carburant = $this->getDoctrine()
            ->getRepository('AppBundle:Carburant')
            ->find($carburantId);

        if(!$carburant){
            $error[] = 'Carburant';
        }

        $puissance = $post->get('puissance');

        if($puissance === ''){
            $error[] = 'Puissance';
        }

        $id = $post->get('vehiculeid');
        $imageid = $post->get('vehiculeimageid');


        $vehicule = $this->getDoctrine()
            ->getRepository('AppBundle:Vehicule')
            ->find($id);


        $em = $this->getDoctrine()->getManager();

        if($vehicule !== null){
            $vehicule->setVehiculeMarque($marque);
            $vehicule->setModele($modele);
            $vehicule->setImmatricule($immatricule);
            $vehicule->setTypeVehicule($typeVehicule);
            $vehicule->setNdfTypeVehicule($ndfTypeVehicule);
            $vehicule->setCarburant($carburant);
            $vehicule->setNbCv($puissance);

            $em->flush();
            $message = 'mise à jour effectuée';
            $action = 'update';
        }
        else {
            /** @var Image $image */
            $image = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->find($imageid);

            if ($image === null)
                return new JsonResponse(['message' => 'image introuvable', 'type' => 'error']);

            $dossier = $image->getLot()->getDossier();

            $vehicule = new Vehicule();

            $vehicule->setDossier($dossier);
            $vehicule->setOperateur($this->getUser());

            $vehicule->setVehiculeMarque($marque);
            $vehicule->setModele($modele);
            $vehicule->setImmatricule($immatricule);
            $vehicule->setTypeVehicule($typeVehicule);
            $vehicule->setNdfTypeVehicule($ndfTypeVehicule);
            $vehicule->setCarburant($carburant);
            $vehicule->setNbCv($puissance);

            $em->persist($vehicule);
            $em->flush();

            $em->refresh($vehicule);

            $message = 'Insertion effectuée';
            $action = 'insert';
        }

        $option = $this->render('@Tenue/SaisieUniverselle/optionVehicule.html.twig',['vehicule' => $vehicule])
            ->getContent()
        ;


        $ret = [
            'message' => $message,
            'type' => 'success',
            'id' => $vehicule->getId(),
            'option' => $option,
            'action' => $action,
            'error' => implode(',', $error)
        ];

        return new JsonResponse($ret);
    }

    public function calculIkAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $vehiculeId = $post->get('vehiculeid');
        $vehicule = $this->getDoctrine()
            ->getRepository('AppBundle:Vehicule')
            ->find($vehiculeId);

        $trajet = $post->get('trajet');

        $annee = $post->get('annee');

        if($annee === null || $annee === ''){
            $annee = date('Y');
        }

        $ttc = 0;

        if($trajet !== '' && $vehicule !== null){
            $ttc = $this->getDoctrine()
                ->getRepository('AppBundle:NdfFraisKilometrique')
                ->calculTtcByVehiculeTrajet($vehicule, $trajet, $annee);
        }

        return new JsonResponse($ttc);
    }

    public function souscategorieAction(Request $request){

        $get = $request->query;

        $sousnatureid = $get->get('sousnatureid');

        $sousnature = $this->getDoctrine()
            ->getRepository('AppBundle:Sousnature')
            ->find($sousnatureid);

        $soussouscategorie = null;

        if($sousnature) {
            /** @var Soussouscategorie $soussouscategorie */
            $soussouscategorie = $this->getDoctrine()
                ->getRepository('AppBundle:Soussouscategorie')
                ->getSoussouscategorieBySousnature($sousnature);
        }

        $res = ['souscategorie' => -1, 'soussouscategorie' => -1];
        if($soussouscategorie){
            $res =[
                'souscategorie' => $soussouscategorie->getSouscategorie()->getId(),
                'soussouscategorie' => $soussouscategorie->getId()
            ];
        }

        return new JsonResponse($res);
    }


    public function compteCollectifAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->query->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if(!$image)
            throw new NotFoundHttpException('Image Introuvable');

        $dossier = $image->getLot()->getDossier();

        /** @var Separation $separation */
        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);

        if(!$separation) {
            return new JsonResponse(['type' => 'error', 'message' => 'Pas de categorie']);
        }

        $categorie = $separation->getCategorie();

        $pccs = [];
        if($categorie->getCode() === 'CODE_CLIENT'){
            $pccs = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, '411', 1);
        }
        elseif($categorie->getCode() === 'CODE_FRNS'){
            $pccs = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, '401', 0);
        }

        return $this->render('@Tenue/SaisieUniverselle/optionPcc.html.twig', ['pccs' => $pccs]);
    }

    public function tiersAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $imageid = $request->query->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if ($image === null)
            throw new NotFoundHttpException('Image Introuvable');

        $dossier = $image->getLot()->getDossier();

        $separation = $this->getDoctrine()
            ->getRepository('AppBundle:Separation')
            ->getSeparationByImage($image);

        $categorie = null;

        if ($separation !== null)
            $categorie = $separation->getCategorie();

        $tiers = $this->getDoctrine()
            ->getRepository('AppBundle:Tiers')
            ->getTiersByCategorie($dossier, $categorie);

        return $this->render('@Tenue/SaisieUniverselle/optionTiers.html.twig', ['tiers' => $tiers]);
    }

    public function saveTiersAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->request;

        $imageid = $post->get('tiersimage');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        $error = [];
        if (!$image) {
            return new JsonResponse(['type' => 'error', 'message' => 'image introuvable']);
        }

        $dossier = $image->getLot()->getDossier();

        $intitule = $post->get('tiersintitule');
        if (trim($intitule) === '') {
            $error[] = 'Intitulé';
        }

        $compte = $post->get('tierscompte');
        if (trim($compte) === '') {
            $error[] = 'Compte';
        }

        $collectifTiersId = $post->get('tierspcc');
        $collectifTiers = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->find($collectifTiersId);

        if ($collectifTiers === null) {
            $error[] = 'Collectif Tiers';
        }

        if (count($error) > 0) {
            return new JsonResponse(['type' => 'error', 'message' => 'Les champs ' . implode(', ', $error) . ' sont vides']);
        }

        $tiersDossiers = $this->getDoctrine()
            ->getRepository('AppBundle:Tiers')
            ->findBy(['dossier' => $dossier, 'compteStr' => $compte]);

        if(count($tiersDossiers) > 0){
            return new JsonResponse(['type' => 'error', 'message' =>  'Ce tiers existe déjà']);
        }

        $tiers = new Tiers();
        $tiers->setDossier($dossier);
        $tiers->setCompteStr($compte);
        $tiers->setIntitule($intitule);
        $tiers->setPcc($collectifTiers);
        $tiers->setType($collectifTiers->getCollectifTiers());

        $em = $this->getDoctrine()->getManager();

        $em->persist($tiers);
        $em->flush();

        $em->refresh($tiers);

        $option = $this->render('@Tenue/SaisieUniverselle/optionTiers.html.twig', ['tiers' => [$tiers]])
            ->getContent();

        return new JsonResponse(['type' => 'success', 'option' => $option, 'id' => $tiers->getId()]);

    }

    public function pccDossierAction(Request $request){

        $post = $request->query;

        $imageid = $post->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if(!$image)
            throw new NotFoundHttpException('Image introuvable');

        $pccid = $post->get('pccid');

        $dossier = $image->getLot()->getDossier();

        $souscategorieid = $post->get('souscategorieid');

        $souscategorie = $this->getDoctrine()
            ->getRepository('AppBundle:Souscategorie')
            ->find($souscategorieid);

        $categorie = null;
        if($souscategorie !== null){
            $categorie = $souscategorie->getCategorie();
        }
        else{
           /** @var Separation $separation */
            $separation = $this->getDoctrine()
               ->getRepository('AppBundle:Separation')
               ->getSeparationByImage($image);

            if($separation !== null){
                $categorie = $separation->getCategorie();
            }
        }

        $pccs = $this->getPccsBySouscategorie($dossier, $categorie, $souscategorie);

        if($pccid !== null){
            $pcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($pccid);

            if($pcc !== null){
                if(!in_array($pcc, $pccs)){
                    $pccs = $this->getPccsBySouscategorie($dossier, $categorie, null);
                }
            }
        }
        return $this->render('@Tenue/SaisieUniverselle/optionPcc.html.twig', ['pccs' => $pccs]);
    }


    public function allPccDossierAction(Request $request){

        $post = $request->query;

        $imageid = $post->get('imageid');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if(!$image)
            throw new NotFoundHttpException('Image introuvable');

        $dossier = $image->getLot()->getDossier();

        $pccs = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->getPccByDossierLike($dossier, '');

        $tiers = $this->getDoctrine()
            ->getRepository('AppBundle:Tiers')
            ->findBy(['dossier' => $dossier], ['compteStr' => 'ASC']);

        return $this->render('@Tenue/SaisieUniverselle/optionPccTiers.hmtl.twig', [
            'pccs' => $pccs,
            'tiers' => $tiers
        ]);
    }

    public function carteBleuBanqueCompteAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $carteBleuBanqueCompteId = $request->query->get('carteBleuBanqueCompte');

        $res = ['banque' => '', 'numcpt' => ''];

        /** @var CarteBleuBanqueCompte $carteBleuBanqueCompte */
        $carteBleuBanqueCompte = $this->getDoctrine()
            ->getRepository('AppBundle:CarteBleuBanqueCompte')
            ->find($carteBleuBanqueCompteId);

        if($carteBleuBanqueCompte !== null){
            $res['banque'] = $carteBleuBanqueCompte
                ->getBanqueCompte()
                ->getBanque()
                ->getNom();

            $res['numcpt'] = $carteBleuBanqueCompte
                ->getBanqueCompte()
                ->getNumcompte();
        }

        return new JsonResponse($res);
    }

    public function checkRelCbAction(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $post = $request->query;

        $imageid = $post->get('imageId');
        $montantPaye = $post->get('montantPaye');
        $datePaiement = $post->get('dateReglement');

        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if ($image === null)
            throw new NotFoundHttpException('Image Introuvable');

        $trouve = -1;

        $dossier = $image->getLot()->getDossier();

        if (trim($datePaiement) === '' || trim($montantPaye) === '')
            return new JsonResponse(['trouve' => $trouve]);

        $datePaiement = \DateTime::createFromFormat('d/m/Y', $datePaiement)
            ->setTime(0,0,0);
        $montantPaye = floatval(str_replace(' ', '', $montantPaye));

        $obs = $this->getDoctrine()
            ->getRepository('AppBundle:BanqueSousCategorieAutre')
            ->getObByReleve($dossier, $montantPaye, $datePaiement, 5);

        $trouve = (count($obs) > 0) ? 1 : 0;

        return new JsonResponse(['trouve' => $trouve]);

    }

    public function importAction(Request $request){
        if(!$request->isXmlHttpRequest())
            throw new AccessDeniedHttpException('Accès refusé');

        $file =  $request->files->get('upload');

        $post = $request->request;

        $type = 'success';
        $message = 'Import effectué avec succès';


        if(!is_null($file)){
            $originale = $file->getClientOriginalName();

            $filename = uniqid() . "." . $file->getClientOriginalExtension();

            $path = $this->get('kernel')->getRootDir() . '/../web/ocr';
            $file->move($path, $filename);

            $path_file = $path . '/' . $filename;

            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

            $excelObj = $this->get('phpexcel')->createPHPExcelObject($path_file);
            $sheet = $excelObj->getActiveSheet()->toArray(null, true, true, true);

            $em = $this->getDoctrine()->getManager();

            $newLot = null;

            $dossierId = $post->get('dossierId');
            $exercice = $post->get('exercice');
            $categorieId = $post->get('categorieId');

            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierId);

            $categorie = $this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find($categorieId);

            $sourceImage = $this->getDoctrine()
                ->getRepository('AppBundle:SourceImage')
                ->find(15);

            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find(559);

            $utilisateur = $this->getDoctrine()
                ->getRepository('AppBundle:Utilisateur')
                ->find(3345);

            $rowId = 0;

            $columns = ['pays', 'siren', 'raison social', 'type pièce', 'type A|V', 'date facture', 'num facture', 'echeance',
                'date echeance', 'date reglement', 'mode reglement', 'num moyen paiement', 'Num Pieces', 'libelle', 'periode debut',
                'periode fin', 'type vente', 'nature', 'sousnature', 'souscategorie', 'soussouscategorie', 'date livraison',
                'devise', 'montant ttc', 'montant tva', 'montant ht', 'journal', 'Résultat pcc', 'pcc tva', 'bilan pcc'
            ];



            foreach ($sheet as $i => $row) {

                if($rowId === 0){
                    $columns = Fonction::initColumns($columns, $row);
                    $rowId++;
                    continue;
                }

                $paysNom = $row[$columns['pays']];
                $pays = null;
                if (trim($paysNom) !== '') {
                    $pays = $this->getDoctrine()
                        ->getRepository('AppBundle:Pays')
                        ->getPaysByNom($paysNom);
                }
                else{
                    $type = 'error';
                    break;
                }

                $siren = trim($row[$columns['siren']]);
                if ($siren === '') {
                    $siren = null;
                }
                else{
                    $type = 'error';
                    break;
                }

                $raisonSocial = trim($row[$columns['raison social']]);
                if ($raisonSocial === '') {
                    $raisonSocial = null;
                }
                else{
                    $type = 'error';
                    break;
                }

                $typePieceLibelle = trim($row[$columns['type pièce']]);
                $typePiece = null;
                if ($typePieceLibelle !== '') {
                    $typePiece = $this->getDoctrine()
                        ->getRepository('AppBundle:TypePiece')
                        ->getTypePieceByLibelle($typePieceLibelle);
                }
                if($typePiece === null){
                    $type = 'error';
                    break;
                }

                $typeAchatVenteLibelle = trim($row[$columns['type A|V']]);
                $typeAchatVente = null;
                if ($typeAchatVenteLibelle !== '') {
                    $typeAchatVente = $this->getDoctrine()
                        ->getRepository('AppBundle:TypeAchatVente')
                        ->getTypeAchatVenteByLibelle($typeAchatVenteLibelle);
                }
                if($typeAchatVente === null){
                    $type = 'error';
                    break;
                }

                $numPiece = trim($row[$columns['Num Pieces']]);
                if ($numPiece === '')
                    $numPiece = null;

                $numFacture = trim($row[$columns['num facture']]);
                if ($numFacture === '') {
                    $numFacture = null;
                }
                else{
                    $type = 'error';
                    break;
                }

                $dateFacture = null;
                if (trim($row[$columns['date facture']]) !== '') {
                    $dateFacture = Fonction::getDateFromExcel($row[$columns['date facture']]);
                }

                $dateEcheance = null;
                if (trim($row[$columns['date echeance']]) !== '')
                    $dateEcheance = Fonction::getDateFromExcel($row[$columns['date echeance']]);

                $dateReglement = null;
                if (trim($row[$columns['date reglement']]) !== '')
                    $dateReglement = Fonction::getDateFromExcel($row[$columns['date reglement']]);

                $modeReglementLibelle = $row[$columns['mode reglement']];
                $modeReglement = null;
                if (trim($modeReglementLibelle) !== '') {
                    $modeReglement = $this->getDoctrine()
                        ->getRepository('AppBundle:ModeReglement')
                        ->getModeReglementByLibelle($modeReglementLibelle);
                }

                $numMoyenPaiment = $row[$columns['num moyen paiement']];
                if (trim($numMoyenPaiment) === '')
                    $numMoyenPaiment = null;



                $libelleEcriture = $row[$columns['libelle']];
                if ($libelleEcriture === '')
                    $libelleEcriture = null;

                $periodeDebut = null;
                if (trim($row[$columns['periode debut']]) !== '')
                    $periodeDebut = Fonction::getDateFromExcel($row[$columns['periode debut']]);

                $periodeFin = null;
                if (trim($row[$columns['periode fin']]) !== '')
                    $periodeFin = Fonction::getDateFromExcel($row[$columns['periode fin']]);


                $typeVenteLibelle = $row[$columns['type vente']];
                $typeVente = null;
                if (trim($typeVenteLibelle) !== '') {
                    $typeVente = $this->getDoctrine()
                        ->getRepository('AppBundle:TypeVente')
                        ->getTypeVenteByLibelle($typeVenteLibelle);
                }


                $natureLibelle = $row[$columns['nature']];
                $nature = null;
                if (trim($natureLibelle) !== '') {
                    $nature = $this->getDoctrine()
                        ->getRepository('AppBundle:Nature')
                        ->getNatureByLibelle($natureLibelle);
                }

                $sousNatureLibelle = $row[$columns['sousnature']];
                $sousNature = null;
                if (trim($sousNatureLibelle) !== '') {
                    $sousNature = $this->getDoctrine()
                        ->getRepository('AppBundle:Sousnature')
                        ->getSousNatureByLibelle($natureLibelle);
                }

                $souscategorieLibelle = $row[$columns['souscategorie']];
                $souscategorie = null;
                if (trim($souscategorieLibelle) !== '') {
                    $souscategorie = $this->getDoctrine()
                        ->getRepository('AppBundle:Souscategorie')
                        ->getSouscategorieByLibelle($souscategorieLibelle);
                }

                $soussouscategorieLibelle = $row[$columns['soussouscategorie']];
                $soussouscategorie = null;
                if (trim($soussouscategorieLibelle) !== '') {
                    $soussouscategorie = $this->getDoctrine()
                        ->getRepository('AppBundle:Soussouscategorie')
                        ->getSoussouscategorieByLibelle($souscategorieLibelle);
                }

                $dateLivraison = null;
                if (trim($row[$columns['date livraison']]) !== '') {
                    $dateLivraison = Fonction::getDateFromExcel($row[$columns['date livraison']]);
                }

                $deviseLibelle = $row[$columns['devise']];
                $devise = null;
                if (trim($deviseLibelle) !== '') {
                    $devise = $this->getDoctrine()
                        ->getRepository('AppBundle:Devise')
                        ->getDeviseByNom($deviseLibelle);
                }
                if($devise === null){
                    $type = 'error';
                    break;
                }


                $montantTtc = $row[$columns['montant ttc']];
                if (trim($montantTtc) === '')
                    $montantTtc = 0;

                $montantTva = $row[$columns['montant tva']];
                if (trim($montantTva) === '')
                    $montantTva = 0;

                $pccDossierCompte = $row[$columns['Résultat pcc']];
                $pccDossier = null;
                if (trim($pccDossierCompte) !== '') {
                    $pccDossier = $this->getDoctrine()
                        ->getRepository('AppBundle:Pcc')
                        ->getPccByDossierCompte($dossier, $pccDossierCompte);
                }

                $journalCode = $row[$columns['journal']];
                $journalDossier = null;
                if (trim($journalCode) !== '') {
                    $journalDossier = $this->getDoctrine()
                        ->getRepository('AppBundle:JournalDossier')
                        ->getJournalDossierByCode($dossier, $journalCode);
                }

                $pcctvaCompte = $row[$columns['pcc tva']];
                $pccTva = null;
                if (trim($pcctvaCompte) !== '') {
                    $pccTva = $this->getDoctrine()
                        ->getRepository('AppBundle:Pcc')
                        ->getPccByDossierCompte($dossier, $pcctvaCompte);
                }

                $pccTiersCompte = $row[$columns['Résultat pcc']];
                $pccTiers = null;
                $tiers = null;
                if (trim($pccTiersCompte) !== '') {
                    $pccTiers = $this->getDoctrine()
                        ->getRepository('AppBundle:Pcc')
                        ->getPccByDossierCompte($dossier, $pccTiersCompte);

                    if ($pccTiers === null) {
                        $tiers = $this->getDoctrine()
                            ->getRepository('AppBundle:Tiers')
                            ->getTiersByLibelle($dossier, $pccTiersCompte);
                    }
                }


                $montantHt = round(floatval($montantTtc) - floatval($montantTva), 2);


                $taux = 0;
                if ($montantTva !== 0) {
                    $taux = round((($montantTtc / $montantTva) - 1), 2);
                }

                $tvaTaux = null;
                if ($taux > 0) {
                    $tvaTaux = $this->getDoctrine()
                        ->getRepository('AppBundle:TvaTaux')
                        ->getTvaTauxByTaux($taux);
                }

                if ($newLot === null) {
                    $newLot = new Lot();
                    $newLot->setDateScan(new \DateTime('now'))
                        ->setDossier($dossier)
                        ->setStatus(4)
                        ->setUtilisateur($utilisateur);

                    $em->persist($newLot);
                }

                $image = new Image();

                if (trim($numPiece) === '') {
                    $numPiece = $originale . '_' . $rowId;
                }

                $image->setLot($newLot)
                    ->setExercice($exercice)
                    ->setNumerotationLocal(1)
                    ->setSourceImage($sourceImage)
                    ->setRenommer(1)
                    ->setDownload(new \DateTime('now'))
                    ->setNbpage(1)
                    ->setOriginale($numPiece)
                    ->setSaisie1(3)
                    ->setSaisie2(3)
                    ->setCtrlSaisie(3)
                    ->setImputation(2)
                    ->setCtrlImputation(2);

                $em->persist($image);

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


                foreach ($this->saisieEntities as $saisieEntity) {
                    $saisie = null;

                    switch ($saisieEntity) {
                        case 'Saisie1':
                            $saisie = new Saisie1();
                            break;
                        case 'Saisie2':
                            $saisie = new Saisie2();
                            break;
                        case 'SaisieControle':
                            $saisie = new SaisieControle();
                            break;
                        case 'Imputation':
                            $saisie = new Imputation();
                            break;
                        case 'ImputationControle':
                            $saisie = new ImputationControle();
                            break;
                    }

                    if ($saisie !== null) {
                        $saisie->setImage($image)
                            ->setPays($pays)
                            ->setSiret($siren)
                            ->setRs($raisonSocial)
                            ->setTypePiece($typePiece)
                            ->setTypeAchatVente($typeAchatVente)
                            ->setDateFacture($dateFacture)
                            ->setNumFacture($numFacture)
                            ->setDateEcheance($dateEcheance)
                            ->setDateReglement($dateReglement)
                            ->setModeReglement($modeReglement)
                            ->setNumPaiement($numMoyenPaiment)
                            ->setPeriodeD1($periodeDebut)
                            ->setPeriodeF1($periodeFin)
                            ->setDevise($devise)
                            ->setDateLivraison($dateLivraison)
                            ->setJournalDossier($journalDossier);

                        $em->persist($saisie);
                    }
                }

                foreach ($this->tvaEntities as $tvaEntity) {
                    /** @var TvaSaisie1 $tva */
                    $tva = null;

                    switch ($tvaEntity) {
                        case 'TvaSaisie1':
                            $tva = new TvaSaisie1();
                            break;
                        case 'TvaSaisie2':
                            $tva = new TvaSaisie2();
                            break;
                        case 'TvaSaisieControle':
                            $tva = new TvaSaisieControle();
                            break;
                        case 'TvaImputation':
                            $tva = new TvaImputation();
                            break;
                        case 'TvaImputationControle':
                            $tva = new TvaImputationControle();
                            break;
                    }

                    if ($tva !== null) {
                        $tva->setImage($image)
                            ->setTypeVente($typeVente)
                            ->setMontantTtc($montantTtc)
                            ->setMontantHt($montantHt)
                            ->setSousnature($sousNature)
                            ->setDateLivraison($dateLivraison)
                            ->setPeriodeDeb($periodeDebut)
                            ->setPeriodeFin($periodeFin)
                            ->setTvaTaux($tvaTaux)
                            ->setSoussouscategorie($soussouscategorie)
                            ->setTiers($tiers)
                            ->setPccBilan($pccTiersCompte)
                            ->setPccTva($pccTva)
                            ->setPcc($pccDossier);

                        if ($tvaEntity === 'TvaSaisie1' || $tvaEntity === 'TvaSaisie2' || $tvaEntity === 'TvaSaisieControle') {
                            $tva->setPrelibelle($libelleEcriture);
                        } else {
                            $tva->setLibelle($libelleEcriture);
                            $tva->setSouscategorie($souscategorie);
                        }
                        $em->persist($tva);
                    }
                }
            }
            $em->flush();
        }
        else{
            $type = 'error';
        }

        if($type === 'error')
            $message = 'Il y a des champs obligatoires non renseignés';
        return new JsonResponse(['type' => $type, 'message' => $message]);
    }

    function stringToNumber($nombre){
        return floatval(str_replace(',', '.' ,str_replace(' ','',$nombre)));
    }

    public function getIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[count($ips) - 1]);
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function checkTvaByNs(Sousnature $sousnature){

        /** @var Pcg[] $pcgTvas */
        $pcgTvas = $this->getDoctrine()
            ->getRepository('AppBundle:SoussouscategorieCompte')
            ->getPcgsBySousnature($sousnature, 2);

        $ns = false;

        if(count($pcgTvas) === 1){
            if($pcgTvas[0]->getCompte() === 'NS'){
                $ns = true;
            }
        }

        return $ns;
    }

    public function getCompteOnLibelle($libelleNew){
        $res = '';
        if(strlen($libelleNew) > 3){
            $like = substr($libelleNew, 0, 3);
            $tmp = str_split($like);

            foreach ($tmp as $char){
                if(is_numeric($char)){
                    $res .= $char;
                }
                else{
                    break;
                }
            }
        }

        return $res;
    }

    public function getPccsBySouscategorie(Dossier $dossier, Categorie $categorie, Souscategorie $souscategorie = null)
    {
        $like = '';

        if ($souscategorie !== null) {
            $like = $this->getCompteOnLibelle($souscategorie->getLibelleNew());
        }
        if ($like === '') {
            $like = ($categorie->getCode() === 'CODE_CLIENT') ? '7' : '6';
        }

        $pccs = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->getPccByDossierLike($dossier, $like);

        return $pccs;
    }


    function checkLuhn($number) {

        // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
        $number=preg_replace('/\D/', '', $number);

        // Set the string length and parity
        $number_length=strlen($number);
        $parity=$number_length % 2;

        // Loop through each digit and do the maths
        $total=0;
        for ($i=0; $i<$number_length; $i++) {
            $digit=$number[$i];
            // Multiply alternate digits by two
            if ($i % 2 == $parity) {
                $digit*=2;
                // If the sum is two digits, add them together (in effect)
                if ($digit > 9) {
                    $digit-=9;
                }
            }
            // Total up the digits
            $total+=$digit;
        }

        // If the total mod 10 equals 0, the number is valid
        return ($total % 10 == 0) ? TRUE : FALSE;

    }


}
