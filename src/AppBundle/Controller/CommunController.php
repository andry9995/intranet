<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\InstructionDossier;
use AppBundle\Entity\InstructionSaisie;
use AppBundle\Entity\MethodeComptable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Controller\ConnectionPicdata;
use \DateTime;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CommunController extends Controller
{
    public function siteParClientAction($client)
    {
        $sites = array();

        if ($client != "") {
            $_client = $this->getDoctrine()
                ->getRepository('AppBundle:Client')
                ->find($client);
            if ($_client) {
                $sites = $this->getDoctrine()
                    ->getRepository('AppBundle:Site')
                    ->getSiteByClient($_client);
            }
        }

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        $data = $serializer->serialize($sites, 'json');

        return JsonResponse::fromJsonString($data);
    }

    public function dossierParSiteAction($client, $site)
    {
        $dossiers = array();

        if ($site == "" || $site == "0") {
            $_client = $this->getDoctrine()
                ->getRepository('AppBundle:Client')
                ->find($client);
            if ($_client) {
                $dossiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Dossier')
                    ->getDossierByClient($_client);
            }
        } else {
            $_site = $this->getDoctrine()
                ->getRepository('AppBundle:Site')
                ->find($site);
            if ($_site) {
                $dossiers = $this->getDoctrine()
                    ->getRepository('AppBundle:Dossier')
                    ->getDossierBySite($_site);
            }
        }

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        $data = $serializer->serialize($dossiers, 'json');

        return JsonResponse::fromJsonString($data);
    }

    /**
     * @param $conteneur "0 si retourner HTML, 1 si JSON"
     * @return JsonResponse|Response
     */
    public function clientsAction($conteneur)
    {
        $user = $this->getUser();

        if($this->get('security.authorization_checker')->isGranted('ROLE_CLIENT'))
        {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
            $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");

            if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
                $query = $query->andWhere('c = :client')->setParameter('client',$user->getClient());

            $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();

            if($conteneur == 0)
            {
                $clients = $query->getResult();
                return $this->render('AppBundle:Commun:clients.html.twig',array('clients'=>$clients, 'conteneur'=>$conteneur));
            }
            if($conteneur == 1)
            {
                $clients = $query->getArrayResult();
                return new JsonResponse($clients);
            }
        }

        return new Response('');
    }

    /**
     * @param $conteneur "0 si retourner HTML, 1 si JSON"
     * @param $client "id du client"
     * @param $tous "1 si ajouter 'Tous' dans la liste"
     * @return JsonResponse|Response
     */
    public function sitesAction($conteneur,$client,$tous)
    {
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($client);
        $sites = $this->getDoctrine()->getRepository('AppBundle:Site')->getSiteByClient($client);

        if($conteneur == 0)
            return $this->render('AppBundle:Commun:sites.html.twig',array('sites'=>$sites,'tous'=>$tous));
        if($conteneur == 1)
            return new JsonResponse($sites);
    }

    /**
     * @param $conteneur "0 si retourner HTML, 1 si JSON"
     * @param $site "id du site"
     * @param $tous "1 si ajouter 'Tous' dans la liste"
     * @param $client "id du client"
     * @return JsonResponse|Response
     */
    public function dossiersAction($conteneur,$site,$tous,$client)
    {
        $client = $this->getDoctrine()->getRepository('AppBundle:Client')->find($client);

        $dossiers = $this->getDoctrine()
                        ->getRepository('AppBundle:Dossier')
                        ->getAllDossierObject($client);

        if($conteneur == 0)
            return $this->render('AppBundle:Commun:dossiers.html.twig',array('dossiers'=>$dossiers,'tous'=>$tous));
        if($conteneur == 1)
        {
            $serializer = $this->get('serializer');
            $json = $serializer->serialize($dossiers, 'json');
            return new JsonResponse($json);
        }
    }


    /**
     * @param $tous "1 si ajouter 'Tous' dans la liste"
     * @return Response
     */
    public function exercicesAction($tous = 0, $attr_id = 'exercice', $in_form = true, $select_col = 6, $label_col = 4, $classes = '')
    {
        $date_now = new DateTime();
        $current_year = intval($date_now->format('Y'));
        $exercices = array();

        for($i=0;$i<5;$i++)
            $exercices[] = $current_year + 1 - $i;

        return $this->render('AppBundle:Commun:exercices.html.twig',array(
            'exercices'=>$exercices , 
            'tous'=>$tous,
            'attr_id' => $attr_id,
            'in_form' => $in_form,
            'select_col' => $select_col,
            'label_col' => $label_col,
            'classes' => $classes
        ));
    }

    public function datePickerAction($dossier)
    {
        $date_now = new DateTime();
        $current_year = intval($date_now->format('Y'));
        $exercices = array();

        for($i=0;$i<4;$i++)
            $exercices[] = $current_year - 2 + $i;

        $dossier_sel = null;
        $dossier_sel = $this->getDoctrine()->getRepository('AppBundle:Dossier')->createQueryBuilder('d')
                        ->where('d.id = :id')
                        ->setParameter('id',$dossier)
                        ->getQuery()
                        ->getOneOrNullResult();

        $cloture = 12;
        if($dossier_sel != null) $cloture = $dossier_sel->getCloture();

        $moiss = $this->getMois($cloture);

        return $this->render('AppBundle:Commun:datePicker.html.twig',array('exercices'=>$exercices, 'moiss'=>$moiss));
    }

    private function getMois($mois_cloture)
    {
        if($mois_cloture == 0) $mois_cloture = 12;
        $moiss = array();

        for($i = 1;$i <= 12;$i++)
        {
            $mois_cloture++;
            if($mois_cloture == 13) $mois_cloture = 1;

            $moiss[$mois_cloture] = Boost::getMoisLettre($mois_cloture);
        }
        return $moiss;
    }

    //afficher utilisateurs return combow si $conteneur = 0 json si 1
    public function utilisateursAction($conteneur,$client = '')
    {
        $user = $this->getUser();
        $acces_utilisateur = $user->getAccesUtilisateur()->getCode();

        if($this->get('security.authorization_checker')->isGranted('ROLE_CLIENT'))
        {
            $client_sel = null;
            if($client != '')
                $client_sel = $this->getDoctrine()->getRepository('AppBundle:Client')->createQueryBuilder('c')
                                ->where('c.id = :id')
                                ->setParameter('id',$client)
                                ->getQuery()
                                ->getOneOrNullResult();

            $utilisateurs  = array();

            if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            {
                $repository = $this->getDoctrine()->getRepository('AppBundle:Utilisateur');
                if($client_sel == null)
                    $query = $repository->createQueryBuilder('u')
                        ->addOrderBy('u.client', 'ASC')
                        ->addOrderBy('u.nom', 'ASC')
                        ->getQuery();
                else
                    $query = $repository->createQueryBuilder('u')
                        ->where('u.client = :client')
                        ->setParameter('client', $client_sel)
                        ->addOrderBy('u.client', 'ASC')
                        ->addOrderBy('u.nom', 'ASC')
                        ->getQuery();
            }
            else
            {
                $repository = $this->getDoctrine()->getRepository('AppBundle:Utilisateur');
                $query = $repository->createQueryBuilder('u')
                        ->where('u.client = :client')
                        ->setParameter('client', $client_sel)
                        ->addOrderBy('u.client', 'ASC')
                        ->addOrderBy('u.nom', 'ASC')
                        ->getQuery();
            }

            if($conteneur == 0)
            {
                $utilisateurs = $query->getResult();
                return $this->render('AppBundle:Commun:utilisateurs.html.twig',array('conteneur'=>$conteneur));
            }
            if($conteneur == 1)
            {
                $utilisateurs = $query->getArrayResult();
                return new JsonResponse($utilisateurs);
            }
        }

        return new Response('Accès refusé');
    }

    //afficher dossiers return combow si $conteneur = 0 json si 1
    public function regimeFiscalsAction($conteneur,$tous)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:RegimeFiscal');
        $query = $repository->createQueryBuilder('r')
                            ->orderBy('r.libelle', 'ASC')->getQuery();
        $regimeFiscals = $query->getArrayResult();

        if($conteneur == 0)
            return $this->render('AppBundle:Commun:regimeFiscals.html.twig',array('regimeFiscals'=>$regimeFiscals, 'tous'=>$tous));
        if($conteneur == 1)
            return new JsonResponse($regimeFiscals);
    }

    //get cloture dossier
    public function clotureAction($dossier)
    {
        $dossier = $this->getDoctrine()->getRepository('AppBundle:Dossier')->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        return new Response(($dossier != null) ? $dossier->getCloture() : 12);
    }

    public function imagePicdataAction(Request $request)
    {
        $post = $request->request;
        $nom_image = $post->get('nom_image');

        $imageMere = $this->getDoctrine()->getRepository('AppBundle:ImageMere')->createQueryBuilder('im')
            ->where('im.nom = :nom')
            ->setParameter('nom',$nom_image)
            ->getQuery()
            ->getOneOrNullResult();

        return new Response('images/'.
            $imageMere->getDossier()->getSite()->getClient()->getNom().'/'.
            $imageMere->getDossier()->getNom().'/'.
            $imageMere->getExercice().'/'.
            $imageMere->getDateScan()->format('Y-m-d').'/'.
            $imageMere->getLot().'/'.
            $imageMere->getNom());
    }

    public function getDossiersActifByClientAction($client,$exercice)
    {
        $dossiers = $this->getDoctrine()
                        ->getRepository('AppBundle:Image')
                        ->getListDosierByExo($client,$exercice);

        return new JsonResponse($dossiers);
    }


    public function infoPerdosAction(Request $request){
        $dossierid = $request->query->get('dossierid');
        $exercice = $request->query->get('exercice');

        /** @var Dossier $dossier */
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $instructionSaisies = $this->getDoctrine()
            ->getRepository('AppBundle:InstructionSaisie')
            ->findBy(['dossier' => $dossier]);

        /** @var InstructionSaisie $instructionSaisie */
        $instructionSaisie = null;

        if(count($instructionSaisies) > 0){
            $instructionSaisie = $instructionSaisies[0];
        }

        $instructionDossiers = $this->getDoctrine()
            ->getRepository('AppBundle:InstructionDossier')
            ->findBy(['client' => $dossier->getSite()->getClient()]);

        /** @var InstructionDossier $instructionDossier */
        $instructionDossier = null;
        if(count($instructionDossiers) > 0){
            $instructionDossier = $instructionDossiers[0];
        }

        $dateCloture = null;
        $dateEcriture = null;
        $tenueComptabilite = null;


        try {
            $dateCloture = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->getDateCloture($dossier, $exercice);
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


//        return new Response(1);

        return $this->render('AppBundle:Commun:infoperdos.html.twig', [
            'instructionSaisie' => $instructionSaisie,
            'dossier' => $dossier,
            'instructionDossier' => $instructionDossier,
            'dateEcriture' => $dateEcriture,
            'tenueComptabilite' => $tenueComptabilite,
            'dateCloture' => $dateCloture
            ] );
    }
}
