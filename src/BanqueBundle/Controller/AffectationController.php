<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 26/02/2019
 * Time: 16:49
 */

namespace BanqueBundle\Controller;


use AppBundle\Entity\Lot;
use AppBundle\Entity\Operateur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Panier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Entity\Image;


class AffectationController extends Controller
{
    public function affectationAction($json)
    {
        $user_app = $this->get('user_app_check')->check();

        $exercice = 2019;

//        if ($user_app != "ok") {
//            if ($user_app == "missing") {
//                if ($json == 0) {
//                    return $this->redirectToRoute('user_app_missing');
//                } else {
//                    $data = array(
//                        'erreur' => true,
//                        'erreur_text' => "Interface non connectée."
//                    );
//
//                    return new JsonResponse(json_encode($data));
//                }
//            } elseif ($user_app == "mismatch") {
//                if ($json == 0) {
//                    return $this->redirectToRoute('user_app_mismatch');
//                } else {
//                    $data = array(
//                        'erreur' => true,
//                        'erreur_text' => "Interface connectée sur une autre machine."
//                    );
//
//                    return new JsonResponse(json_encode($data));
//                }
//            }
//        }

//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CGP_BANQUE')) {
//            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page");
//        }

        $now = new \DateTime();

        $liste_jour = array();
        if ($now->format('w') == 0) {
            $now->add(new \DateInterval('P1D'));
        }
        $tmp = new \DateTime($now->format('Y-m-d'));
        $liste_jour[] = $tmp;
        for ($i = 0; $i < 5; $i++) {
            $now->add(new \DateInterval('P1D'));
            if ($now->format('w') == 0) {
                $now->add(new \DateInterval('P1D'));
            }
            $tmp = new \DateTime($now->format('Y-m-d'));
            $liste_jour[] = $tmp;
        }

        $image_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Image');
        $nb_image_S1 = $image_repo
            ->imageSaisie1Banque($nb_lot_S1);

        $nb_image_S2 = $image_repo
            ->imageSaisie2Banque($nb_lot_S2, $exercice);

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');

        $etape_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'BANQUES',
            ));
        $etape_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'BANQUES',
            ));

        $em =  $this->getDoctrine()
            ->getManager();

        //fafana ao anaty panier ny image'n'ny operateur tsy ao @banque intsony
        $panier_S1_fantomes = $panier_repo->getPanierFantome(16, $etape_S1->getId(), 11);

        /** @var Panier[] $panier_S1_fantomes */
        foreach ($panier_S1_fantomes as $panier) {
            /** @var \AppBundle\Entity\Image $image */
            $image = $panier->getImage();
            $item = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->findBy(array(
                    'image' => $image
                ));
            /** @var \AppBundle\Entity\ImageATraiter $image_a_traiter */
            $image_a_traiter = null;
            if ($item && count($item) > 0) {
                $image_a_traiter = $item[0];

                $image_a_traiter->setSaisie1(0);
                $image_a_traiter->setSaisie2(0);

            }
            $image->setSaisie1(0);
            $image->setSaisie2(0);
            $image->setCtrlSaisie(0);
            $image->setImputation(0);
            $image->setCtrlImputation(0);

            $em->remove($panier);

        }

        $em->flush();

        $panier_S2_fantomes = $panier_repo->getPanierFantome(16, $etape_S2->getId(), 26);

        /** @var Panier[] $panier_S2_fantomes */
        foreach ($panier_S2_fantomes as $panier) {
            /** @var \AppBundle\Entity\Image $image */
            $image = $panier->getImage();
            $item = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->findBy(array(
                    'image' => $image
                ));
            /** @var \AppBundle\Entity\ImageATraiter $image_a_traiter */
            $image_a_traiter = null;
            if ($item && count($item) > 0) {
                $image_a_traiter = $item[0];
                $image_a_traiter->setStatus(6);
            }

            $image->setImputation(0);
            $image->setCtrlImputation(0);

            $em->remove($panier);

        }

        $em->flush();


        $lot_s1_rb = $lot_repo
            ->lotSaisie1Banque(true);

        $lot_s1_encours_rb = $lot_repo
            ->lotSaisie1Banque(true, true);

        $lot_s1_ob = $lot_repo
            ->lotSaisie1Banque(false);

        $lot_s1_encours_ob = $lot_repo
            ->lotSaisie1Banque(false, true);

        $lot_S1 = array_merge($lot_s1_rb, $lot_s1_ob);

        $lot_s1_encours = array_merge($lot_s1_encours_rb, $lot_s1_encours_ob);

        $lot_S2  = $lot_repo
            ->lotSaisie2Banque($exercice);

        $lot_S2_encours  = $lot_repo
            ->lotSaisie2Banque($exercice, true);



        $operateurs_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_S1);

        $operateurs_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_S2);

        $panier_S1_rb = $panier_repo
            ->getPanierBanque(true);

        $panier_S1_ob = $panier_repo
            ->getPanierBanque(false);

        $panier_S1 = array_merge($panier_S1_rb, $panier_S1_ob);

        $panier_S2 = $panier_repo
            ->getPanierSaisie2Banque($exercice);

        //LISTE CLIENTS - DOSSIERS
        $liste_client_S1 = array();
        $liste_dossier_S1 = array();

        $liste_client_encours_S1 = array();
        $liste_dossier_encours_S1 = array();

        $liste_client_S2 = array();
        $liste_dossier_S2 = array();

        $liste_client_encours_S2 = array();
        $liste_dossier_encours_S2 = array();

        foreach ($lot_S1 as $lot) {
            if (!in_array($lot->client, $liste_client_S1)) {
                $liste_client_S1[] = $lot->client;
                $liste_dossier_S1[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_S1[$lot->client])) {
                    $liste_dossier_S1[$lot->client][] = $lot->dossier;
                }
            }
        }


        foreach ($lot_s1_encours as $lot) {

            if (!in_array($lot->client, $liste_client_encours_S1)) {
                $liste_client_encours_S1[] = $lot->client;
                $liste_dossier_encours_S1[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_encours_S1[$lot->client])) {
                    $liste_dossier_encours_S1[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_S2 as $lot) {
            if (!in_array($lot->client, $liste_client_S2)) {
                $liste_client_S2[] = $lot->client;
                $liste_dossier_S2[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_S2[$lot->client])) {
                    $liste_dossier_S2[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_S2_encours as $lot) {
            if (!in_array($lot->client, $liste_client_encours_S2)) {
                $liste_client_encours_S2[] = $lot->client;
                $liste_dossier_encours_S2[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_encours_S2[$lot->client])) {
                    $liste_dossier_encours_S2[$lot->client][] = $lot->dossier;
                }
            }
        }


        ksort($liste_dossier_S1);
        ksort($liste_dossier_encours_S1);
        ksort($liste_dossier_S2);
        ksort($liste_dossier_encours_S2);

        foreach ($liste_dossier_S1 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_encours_S1 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_S2 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_encours_S2 as $key => &$value) {
            sort($value);
        }


        if ($json == 0) {
            $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            return $this->render('@Banque/Banque/affectation.html.twig', array(
                'nb_lot_S1' => $nb_lot_S1,
                'nb_image_S1' => $nb_image_S1,
                'nb_lot_S2' => $nb_lot_S2,
                'nb_image_S2' => $nb_image_S2,
                'liste_jour' => $liste_jour,
                'operateurs_S1' => $operateurs_S1,
                'operateurs_S2' => $operateurs_S2,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'lot_S1' => $lot_S1,
                'lot_encours_S1' => $lot_s1_encours,
                'lot_S2' => $lot_S2,
                'lot_encours_S2' => $lot_S2_encours,
                'liste_client_S1' => $liste_client_S1,
                'liste_client_encours_S1' => $liste_client_encours_S1,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_dossier_encours_S1' => $liste_dossier_encours_S1,
                'liste_client_S2' => $liste_client_S2,
                'liste_client_encours_S2' => $liste_client_encours_S2,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_dossier_encours_S2' => $liste_dossier_encours_S2,
                'mois' => $mois,
            ));
        } else {
            $panier_S1 = $panier_repo
                ->getPanierSaisie1(true);
            $panier_S2 = $panier_repo
                ->getPanierSaisie2Banque($exercice);

            $lot_s1_ob = $lot_repo
                ->lotSaisie1Banque(false);

            $lot_S1 = array_merge($lot_s1_rb, $lot_s1_ob);

            $lot_S2 = $lot_repo
                ->lotSaisie2Banque($exercice);


            $data = array(
                'erreur' => false,
                'nb_lot_S1' => $nb_lot_S1,
                'nb_image_S1' => $nb_image_S1,
                'nb_lot_S2' => $nb_lot_S2,
                'nb_image_S2' => $nb_image_S2,
                'liste_jour' => $liste_jour,
                'operateurs_S1' => $operateurs_S1,
                'operateurs_S2' => $operateurs_S2,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'lot_S1' => $lot_S1,
                'lot_encours_S1' => $lot_s1_encours,
                'lot_S2' => $lot_S2,
                'lot_encours_S2' => $lot_S2_encours,
                'liste_client_S1' => $liste_client_S1,
                'liste_client_encours_S1' => $liste_client_encours_S1,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_dossier_encours_S1' => $liste_dossier_encours_S1,
                'liste_client_S2' => $liste_client_S2,
                'liste_client_encours_S2' => $liste_client_encours_S2,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_dossier_encours_S2' => $liste_dossier_encours_S2,
            );

            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });

            $serializer = new Serializer(array($normalizer), array($encoder));
            return new JsonResponse($serializer->serialize($data, 'json'));
        }
    }

    public function addToPanierAction(Request $request)
    {
        $em = $this->getDoctrine()
            ->getManager();
        $ip = $_SERVER['REMOTE_ADDR'];

        $post = $request->request;


        $operateurid = $post->get('operateur');
        $operateur = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->find($operateurid);

        $dossierid = $post->get('dossier');
        $dossier = null;

        if($dossierid !== ''){
            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierid);
        }

        $banquecompteid = $post->get('banque_compte');
        $banquecompte = null;
        if($banquecompteid !== '') {
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteid);
        }

        $etape = $post->get('etape');


        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Categorie')
            ->find(16);

        $etape = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array('code' => $etape));

        $isReleve =  true;

        if($request->request->get('is_releve') === 'false') {
            $isReleve = false;
        }

        $date_panier = $request->request->get('date_panier');

        if($etape->getCode() === 'OS_1') {
            $images = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->getImageATraiterByDossierAndCategorie($dossier, $categorie, $etape, $isReleve);


            /** @var \AppBundle\Entity\Image $image */
            foreach ($images as $image) {
                $categorie_separation = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->imageCategorieSeparation($image);
                if ($categorie_separation) {
                    $panier = new Panier();
                    $panier
                        ->setDatePanier(new \DateTime($date_panier))
                        ->setImage($image)
                        ->setEtapeTraitement($etape)
                        ->setOperateur($operateur)
                        ->setDossier($image->getLot()->getDossier())
                        ->setCategorie($categorie_separation)
                        ->setOpPartageId($this->getUser()->getId());

                    if ($etape->getCode() == 'OS_1') {
                        $image->setSaisie1(1);
                        $image->setSaisie2(1);
                        $image->setCtrlSaisie(1);
                    } elseif ($etape->getCode() == 'IMP') {
                        $image->setImputation(1);
                        $image->setCtrlImputation(1);
                    }


                    $logs = new Logs();
                    $logs
                        ->setOperateur($this->getUser())
                        ->setDateDebut(new \DateTime())
                        ->setDateFin(new \DateTime())
                        ->setIp($ip)
                        ->setRemarque('PARTAGE LOT ' . $etape->getLibelle() . '(' . $operateur->getId() . ')')
                        ->setDossier($dossier)
                        ->setImage($panier->getImage())
                        ->setEtapeTraitement($etape);

                    $image_a_traiter = $this->getDoctrine()
                        ->getRepository('AppBundle:ImageATraiter')
                        ->findBy([
                            'image' => $image
                        ]);
                    if ($image_a_traiter && count($image_a_traiter) > 0) {
                        /** @var \AppBundle\Entity\ImageATraiter $item */
                        $item = $image_a_traiter[0];
                        if ($etape->getCode() == 'OS_1') {
                            $item->setSaisie1(1);
                            $item->setSaisie2(1);
                        } elseif ($etape->getCode() == 'BQ_DETAILS') {
                            $item->setStatus(7);
                            $item->setStatus(9);
                        }
                    }
                    $em->persist($logs);
                    $em->persist($panier);
                }
            }
        }
        else {
            $images = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->getImageATraiterSaisie2Banque($banquecompte);

            $categorie_separation = $this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find(16);

            /** @var Image $image */
            foreach ($images as $image) {

                $panier = new Panier();
                $panier
                    ->setDatePanier(new \DateTime($date_panier))
                    ->setImage($image)
                    ->setEtapeTraitement($etape)
                    ->setOperateur($operateur)
                    ->setDossier($image->getLot()->getDossier())
                    ->setCategorie($categorie_separation)
                    ->setOpPartageId($this->getUser()->getId());

                $logs = new Logs();
                $logs
                    ->setOperateur($this->getUser())
                    ->setDateDebut(new \DateTime())
                    ->setDateFin(new \DateTime())
                    ->setIp($ip)
                    ->setRemarque('PARTAGE LOT ' . $etape->getLibelle() . '(' . $operateur->getId() . ')')
                    ->setDossier($banquecompte->getDossier())
                    ->setImage($panier->getImage())
                    ->setEtapeTraitement($etape);

                $em->persist($logs);
                $em->persist($panier);
            }
        }
        $em->flush();
        $data = array(
            'erreur' => false,
        );
        return new JsonResponse(json_encode($data));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function returnFromPanierAction(Request $request)
    {

        $post = $request->request;

        $etape_code = $post->get('etape');
        $etape = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array('code' => $etape_code));

        $operateurId = $post->get('operateur');
        $operateur = null;
        if($operateurId !== ''){
            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find($operateurId);
        }

        $date_panier = new \DateTime($request->request->get('date_panier'));

        $dossierId = $post->get('dossier');
        $dossier = null;
        if($dossierId !== ''){
            $dossier = $this->getDoctrine()
                ->getRepository('AppBundle:Dossier')
                ->find($dossierId);
        }

        $banquecompteId = $post->get('banquecompte');
        $banquecompte = null;
        if($banquecompteId !== ''){
            $banquecompte = $this->getDoctrine()
                ->getRepository('AppBundle:BanqueCompte')
                ->find($banquecompteId);
        }

        if($dossier === null){
            $dossier = $banquecompte->getDossier();
        }

        try {
            $em = $this->getDoctrine()
                ->getManager();

            $paniers = $this->getDoctrine()
                ->getRepository('AppBundle:Panier')
                ->getOneLotBanque($operateur, $etape, $dossier, $date_panier);

            /** @var Panier $panier */
            foreach ($paniers as $panier) {
                /** @var \AppBundle\Entity\Image $image */
                $image = $panier->getImage();
                $item = $this->getDoctrine()
                    ->getRepository('AppBundle:ImageATraiter')
                    ->findBy(array(
                        'image' => $image
                    ));
                /** @var \AppBundle\Entity\ImageATraiter $image_a_traiter */
                $image_a_traiter = null;
                if ($item && count($item) > 0) {
                    $image_a_traiter = $item[0];
                }
                switch ($etape_code) {
                    case 'OS_1' :
                        $image->setSaisie1(0);
                        $image->setSaisie2(0);
                        $image->setCtrlSaisie(0);
                        $image->setImputation(0);
                        $image->setCtrlImputation(0);

                        break;

                    case 'BQ_DETAILS' :
                        $image->setImputation(0);
                        $image->setCtrlImputation(0);
                        break;

                }
                if ($image_a_traiter) {
                    switch ($etape_code) {
                        case 'OS_1' :
                            $image_a_traiter->setSaisie1(0);
                            $image_a_traiter->setSaisie2(0);
                            break;


                        case 'BQ_DETAILS' :
                            $image_a_traiter->setStatus(6);
                            break;

                    }
                }
                $em->remove($panier);
            }

            $em->flush();

            $data = array(
                'erreur' => false,
            );
        } catch (\Exception $ex) {
            $data = array(
                'erreur' => true,
                'erreur_text' => $ex->getMessage(),
            );
            return new JsonResponse(json_encode($data));
        }
        return new JsonResponse(json_encode($data));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function moveToPanierAction(Request $request)
    {

        $post = $request->request;

        $operateurId = $post->get('operateur');
        $operateur = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->find($operateurId);

        $oldoperateurId = $post->get('oldoperateur');
        $oldoperateur = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->find($oldoperateurId);

        $etape = $post->get('etape');
        $etape =  $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(['code' =>$etape]);

        $dossierId = $post->get('dossier');
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierId);


        $date_panier = new \DateTime($post->get('date_panier'));
        $date_panier_org = new \DateTime($post->get('date_panier_org'));

        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');
        $paniers = $panier_repo
            ->getOneLotBanque($oldoperateur, $etape, $dossier, $date_panier_org);
        $ip = $_SERVER['REMOTE_ADDR'];
        $em = $this->getDoctrine()
            ->getManager();

        if ($paniers) {
            /** @var Panier $panier */
            foreach ($paniers as $panier) {
                $panier->setOperateur($operateur);
                $panier->setDatePanier($date_panier);

                $logs = new Logs();
                $logs->setOperateur($this->getUser());
                $logs->setDateDebut(new \DateTime());
                $logs->setDateFin(new \DateTime());
                $logs->setIp($ip);
                $logs->setRemarque('DEPLACER LOT ' . $etape->getLibelle());
                $logs->setDossier($dossier);
                $logs->setImage($panier->getImage());
                $logs->setEtapeTraitement($etape);

                $em->persist($logs);
            }
        }

        $em->flush();

        $data = array(
            'panier' => $panier,
            'operateur' => $operateur->getId(),
            'oldoperateur' => $oldoperateur->getId(),
            'etapeTraitement' => $etape->getId(),
            'datePanier' => $date_panier->format('Y-m-d'),
            'datePanierOrg' => $date_panier_org->format('Y-m-d'),
        );

        return new JsonResponse(json_encode($data));
    }

}