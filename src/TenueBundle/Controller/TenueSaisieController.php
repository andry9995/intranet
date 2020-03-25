<?php

namespace TenueBundle\Controller;

use AppBundle\Entity\Categorie;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Lot;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Panier;
use AppBundle\Entity\LotUserGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TenueSaisieController extends Controller
{
    /**
     * Accueil Tenu
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /*if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() == 'POST') {
                $image_repo = $this->getDoctrine()
                    ->getRepository('AppBundle:Image');
                $nb_image_S1 = $image_repo
                    ->imageSaisie1($nb_lot_S1, $this->getUser()->getId());
                $nb_image_S2 = $image_repo
                    ->imageSaisie2($nb_lot_S2, $this->getUser()->getId());
                $nb_image_CTRL_OS = $image_repo
                    ->imageCtrlSaisie($nb_lot_CTRL_OS, $this->getUser()->getId());
                $nb_image_IMP = $image_repo
                    ->imageImputation($nb_lot_IMP);
                $nb_image_CTRL_IMP = $image_repo
                    ->imageCtrlImputation($nb_lot_CTRL_IMP);

                $data = array(
                        'nb_image_S1' => $nb_image_S1,
                        'nb_image_S2' => $nb_image_S2,
                        'nb_image_CTRL_OS' => $nb_image_CTRL_OS,
                        'nb_image_IMP' => $nb_image_IMP,
                        'nb_image_CTRL_IMP' => $nb_image_CTRL_IMP,
                        'nb_lot_S1' => $nb_lot_S1,
                        'nb_lot_S2' => $nb_lot_S2,
                        'nb_lot_CTRL_OS' => $nb_lot_CTRL_OS,
                        'nb_lot_IMP' => $nb_lot_IMP,
                        'nb_lot_CTRL_IMP' => $nb_lot_CTRL_IMP
                    );

                return new JsonResponse($data);
            }else{
                return $this->render('TenueBundle:Tenue:index.html.twig', array(
                    'statut' => false
                ));
            }
        }else{
            return $this->render('TenueBundle:Tenue:index.html.twig', array(
                'statut' => false
            ));
        }*/
        return $this->render('TenueBundle:Tenue:index.html.twig', array(
            'statut' => false
        ));
    }

    /**
     * Tableau de bord tenue
     *
     * @return Response
     */
    public function dashBordAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->getMethod() == 'POST') {
                $image_repo = $this->getDoctrine()
                    ->getRepository('AppBundle:Image');
                $nb_image_S1 = $image_repo
                    ->imageSaisie1($nb_lot_S1, $this->getUser()->getId());
                $nb_image_S2 = $image_repo
                    ->imageSaisie2($nb_lot_S2, $this->getUser()->getId());
                $nb_image_CTRL_OS = $image_repo
                    ->imageCtrlSaisie($nb_lot_CTRL_OS, $this->getUser()->getId());
                $nb_image_IMP = $image_repo
                    ->imageImputation($nb_lot_IMP);
                $nb_image_CTRL_IMP = $image_repo
                    ->imageCtrlImputation($nb_lot_CTRL_IMP);

                $data = array(
                    'nb_image_S1' => $nb_image_S1,
                    'nb_image_S2' => $nb_image_S2,
                    'nb_image_CTRL_OS' => $nb_image_CTRL_OS,
                    'nb_image_IMP' => $nb_image_IMP,
                    'nb_image_CTRL_IMP' => $nb_image_CTRL_IMP,
                    'nb_lot_S1' => $nb_lot_S1,
                    'nb_lot_S2' => $nb_lot_S2,
                    'nb_lot_CTRL_OS' => $nb_lot_CTRL_OS,
                    'nb_lot_IMP' => $nb_lot_IMP,
                    'nb_lot_CTRL_IMP' => $nb_lot_CTRL_IMP
                );

                return new JsonResponse($data);
            }else{
                return $this->render('TenueBundle:Tenue:dashBord.html.twig', array(
                    'statut' => false
                ));

            }
        }else{
            return $this->render('TenueBundle:Tenue:dashBord.html.twig', array(
                'statut' => false
            ));

        }

    }

    /**
     * Affichage d'un lot par catégorie
     *
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("etape", class="AppBundle:EtapeTraitement", options={"mapping": {"etape": "code"}})
     *
     * @param Lot $lot
     * @param $etape
     * @return JsonResponse
     */
    public function categorieShowAction(Lot $lot, $etape)
    {
        $categorie = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->imagePerCateg($lot, $etape);
        $data = array(
            'erreur' => false,
            'categorie' => $categorie,
        );
        return new JsonResponse($data);
    }


    /**
     * Pilotage saisie
     *
     * @return Response
     */
    public function pilotageAction()
    {
        return $this->render('TenueBundle:Tenue:index.html.twig');
    }



    /**
     * Affectation lot par tenue
     *
     * @param $json
     * @return Response|JsonResponse
     * @throws \Exception
     */
    public function affectationLotTenueAction($json)
    {
        $user_app = $this->get('user_app_check')->check();

        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée."
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine."
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CGP_IMPUTATION')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page");
        }

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
            ->imageTenueSaisie1($nb_lot_S1, $this->getUser()->getId());
        $nb_image_S2 = $image_repo
            ->imageTenueSaisie2($nb_lot_S2, $this->getUser()->getId());
        $nb_image_CTRL = $image_repo
            ->imageTenueCtrlSaisie($nb_lot_CTRL, $this->getUser()->getId());
        $nb_image_IMP = $image_repo
            ->imageImputationTenue($nb_lot_IMP, $this->getUser()->getId());

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');

        $lotTenue_S1 = $lot_repo
            ->lotTenueSaisie1($this->getUser()->getId());

        $lotTenue_S2 = $lot_repo
            ->lotTenueSaisie2($this->getUser()->getId());
        $lotTenue_Ctrl = $lot_repo
            ->lotTenueCtrlSaisie($this->getUser()->getId());
        $lotTenue_IMP = $lot_repo
            ->lotTenueImputation($this->getUser()->getId());

        $etape_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_1',
            ));
        $etape_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_2',
            ));
        $etape_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'CTRL_OS',
            ));
        $operateurs_TENUE = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getUserTenue($this->getUser()->getId());

        /*$pourcentage_groupe = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getPourcentageSaisieGroupe($this->getUser()->getId());*/

        $panier_S1 = $panier_repo
            ->getPanierTenueSaisie1($this->getUser()->getId());
        $panier_S2 = $panier_repo
            ->getPanierTenueSaisie2($this->getUser()->getId());
        $panier_CTRL = $panier_repo
            ->getPanierTenueCtrl($this->getUser()->getId());

        $panier_IMP = $panier_repo
            ->getPanierTenueCtrl($this->getUser()->getId());

        //LISTE CLIENTS - DOSSIERS
        $liste_client_S1 = array();
        $liste_dossier_S1 = array();
        $liste_client_S2 = array();
        $liste_dossier_S2 = array();
        $liste_client_CTRL = array();
        $liste_dossier_CTRL = array();
        $liste_dossier_IMP = array();
        $liste_client_IMP = array();

        foreach ($lotTenue_S1 as $lot) {
            if (!in_array($lot->client, $liste_client_S1)) {
                $liste_client_S1[] = $lot->client;
                $liste_dossier_S1[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_S1[$lot->client])) {
                    $liste_dossier_S1[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lotTenue_S2 as $lot) {
            if (!in_array($lot->client, $liste_client_S2)) {
                $liste_client_S2[] = $lot->client;
                $liste_dossier_S2[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_S2[$lot->client])) {
                    $liste_dossier_S2[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lotTenue_Ctrl as $lot) {
            if (!in_array($lot->client, $liste_client_CTRL)) {
                $liste_client_CTRL[] = $lot->client;
                $liste_dossier_CTRL[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_CTRL[$lot->client])) {
                    $liste_dossier_CTRL[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($liste_dossier_IMP as $lot) {
            if (!in_array($lot->client, $liste_client_IMP)) {
                $liste_client_IMP[] = $lot->client;
                $liste_dossier_IMP[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_IMP[$lot->client])) {
                    $liste_dossier_IMP[$lot->client][] = $lot->dossier;
                }
            }
        }

        ksort($liste_dossier_S1);
        ksort($liste_dossier_S2);
        ksort($liste_dossier_CTRL);
        ksort($liste_dossier_IMP);

        foreach ($liste_dossier_S1 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_S2 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_CTRL as $key => &$value) {
            sort($value);
        }

        foreach ($liste_dossier_IMP as $key => &$value) {
            sort($value);
        }


        if ($json == 0) {
            $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            return $this->render('@Tenue/Tenue/affectation_tenue.html.twig', array(
                'nb_lot_S1' => $nb_lot_S1,
                'nb_image_S1' => $nb_image_S1,
                'nb_lot_S2' => $nb_lot_S2,
                'nb_image_S2' => $nb_image_S2,
                'nb_lot_CTRL' => $nb_lot_CTRL,
                'nb_image_CTRL' => $nb_image_CTRL,
                'nb_lot_IMP' => $nb_lot_IMP,
                'nb_image_IMP' => $nb_image_IMP,
                'liste_jour' => $liste_jour,
                'operateurs_TENUE' => $operateurs_TENUE,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'panier_CTRL' => $panier_CTRL,
                'panier_IMP' => $panier_IMP,
                'lotTenue_S1' => $lotTenue_S1,
                'lotTenue_S2' => $lotTenue_S2,
                'lotTenue_Ctrl' => $lotTenue_Ctrl,
                'lotTenue_IMP' => $lotTenue_IMP,
                'liste_client_S1' => $liste_client_S1,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_client_S2' => $liste_client_S2,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'liste_dossier_IMP' => $liste_dossier_IMP,
                'liste_client_IMP' => $liste_client_IMP,
                'mois' => $mois,
                //'pourcentage_groupe' => $pourcentage_groupe,

            ));
        } else {
            $panier_S1 = $panier_repo
                ->getPanierSaisie1(true);
            $panier_S2 = $panier_repo
                ->getPanierSaisie2(true);
            $panier_CTRL = $panier_repo
                ->getPanierCtrlSaisie(true);
            /*$lot_S1 = $lot_repo
                ->lotSaisie1($this->getUser()->getId());
            $lot_S2 = $lot_repo
                ->lotSaisie2($this->getUser()->getId());
            $lot_CTRL = $lot_repo
                ->lotCtrlSaisie($this->getUser()->getId());*/

            $data = array(
                'erreur' => false,
                'nb_lot_S1' => $nb_lot_S1,
                'nb_image_S1' => $nb_image_S1,
                'nb_lot_S2' => $nb_lot_S2,
                'nb_image_S2' => $nb_image_S2,
                'nb_lot_CTRL' => $nb_lot_CTRL,
                'nb_image_CTRL' => $nb_image_CTRL,
                'nb_lot_IMP' => $nb_lot_IMP,
                'nb_image_IMP' => $nb_image_IMP,
                'liste_jour' => $liste_jour,
                'operateurs_TENUE' => $operateurs_TENUE,

                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'panier_CTRL' => $panier_CTRL,
                'panier_IMP' => $panier_IMP,
                'lotTenue_S1' => $lotTenue_S1,
                'lotTenue_S2' => $lotTenue_S2,
                'lotTenue_Ctrl' => $lotTenue_Ctrl,
                'lotTenue_IMP' => $lotTenue_IMP,
                'liste_client_S1' => $liste_client_S1,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_client_S2' => $liste_client_S2,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'liste_dossier_IMP' => $liste_dossier_IMP,
                'liste_client_IMP' => $liste_client_IMP,
                //'pourcentage_groupe' => $pourcentage_groupe,

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


    /**
     * Affectation saisie Index
     *
     * @param $json
     * @return Response|JsonResponse
     * @throws \Exception
     */
    public function affectionGroupeAction($json)
    {
        $user_app = $this->get('user_app_check')->check();

        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée."
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine."
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CGP_IMPUTATION')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page");
        }

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
            ->imageSaisie1($nb_lot_S1, $this->getUser()->getId());
        $nb_image_S2 = $image_repo
            ->imageSaisie2($nb_lot_S2, $this->getUser()->getId());
        $nb_image_CTRL = $image_repo
            ->imageCtrlSaisie($nb_lot_CTRL, $this->getUser()->getId());

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');

        $lot_S1 = $lot_repo
            ->lotSaisie1($this->getUser()->getId());
        $lot_S2 = $lot_repo
            ->lotSaisie2($this->getUser()->getId());
        $lot_CTRL = $lot_repo
            ->lotCtrlSaisie($this->getUser()->getId());
        $etape_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_1',
            ));
        $etape_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_2',
            ));
        $etape_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'CTRL_OS',
            ));
        $operateurs_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurControleSaisie();

        $operateurs_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurControleSaisie();

        $operateurs_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurControleSaisie();

        $pourcentage_groupe = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getPourcentageSaisieGroupe($this->getUser()->getId());

        $panier_S1 = $panier_repo
            ->getPanierGroupeSaisie1();
        $lotGroupe_S1 = $lot_repo
            ->lotGroupe('OS_1');
        $panier_S2 = $panier_repo
            ->getPanierGroupeSaisie2();
        $lotGroupe_S2 = $lot_repo
            ->lotGroupe('OS_2');
        $panier_CTRL = $panier_repo
            ->getPanierCtrlSaisie();

        //LISTE CLIENTS - DOSSIERS
        $liste_client_S1 = array();
        $liste_dossier_S1 = array();
        $liste_client_S2 = array();
        $liste_dossier_S2 = array();
        $liste_client_CTRL = array();
        $liste_dossier_CTRL = array();

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

        foreach ($lot_CTRL as $lot) {
            if (!in_array($lot->client, $liste_client_CTRL)) {
                $liste_client_CTRL[] = $lot->client;
                $liste_dossier_CTRL[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_CTRL[$lot->client])) {
                    $liste_dossier_CTRL[$lot->client][] = $lot->dossier;
                }
            }
        }

        ksort($liste_dossier_S1);
        ksort($liste_dossier_S2);
        ksort($liste_dossier_CTRL);

        foreach ($liste_dossier_S1 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_S2 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_CTRL as $key => &$value) {
            sort($value);
        }


        if ($json == 0) {
            $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            return $this->render('@Tenue/Tenue/affectation_saisie_groupe.html.twig', array(
                'nb_lot_S1' => $nb_lot_S1,
                'nb_image_S1' => $nb_image_S1,
                'nb_lot_S2' => $nb_lot_S2,
                'nb_image_S2' => $nb_image_S2,
                'nb_lot_CTRL' => $nb_lot_CTRL,
                'nb_image_CTRL' => $nb_image_CTRL,
                'liste_jour' => $liste_jour,
                'operateurs_S1' => $operateurs_S1,
                'operateurs_S2' => $operateurs_S2,
                'operateurs_CTRL' => $operateurs_CTRL,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'panier_CTRL' => $panier_CTRL,
                'lot_S1' => $lot_S1,
                'lot_S2' => $lot_S2,
                'lot_CTRL' => $lot_CTRL,
                'liste_client_S1' => $liste_client_S1,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_client_S2' => $liste_client_S2,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'mois' => $mois,
                'pourcentage_groupe' => $pourcentage_groupe,
                'lotGroupe_S1' => $lotGroupe_S1,
                'lotGroupe_S2' => $lotGroupe_S2,
            ));
        } else {
            $panier_S1 = $panier_repo
                ->getPanierSaisie1(true);
            $panier_S2 = $panier_repo
                ->getPanierSaisie2(true);
            $panier_CTRL = $panier_repo
                ->getPanierCtrlSaisie(true);
            $lot_S1 = $lot_repo
                ->lotSaisie1($this->getUser()->getId());
            $lot_S2 = $lot_repo
                ->lotSaisie2($this->getUser()->getId());
            $lot_CTRL = $lot_repo
                ->lotCtrlSaisie($this->getUser()->getId());

            $data = array(
                'erreur' => false,
                'nb_lot_S1' => $nb_lot_S1,
                'nb_image_S1' => $nb_image_S1,
                'nb_lot_S2' => $nb_lot_S2,
                'nb_image_S2' => $nb_image_S2,
                'nb_lot_CTRL' => $nb_lot_CTRL,
                'nb_image_CTRL' => $nb_image_CTRL,
                'liste_jour' => $liste_jour,
                'operateurs_S1' => $operateurs_S1,
                'operateurs_S2' => $operateurs_S2,
                'operateurs_CTRL' => $operateurs_CTRL,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'panier_CTRL' => $panier_CTRL,
                'lot_S1' => $lot_S1,
                'lot_S2' => $lot_S2,
                'lot_CTRL' => $lot_CTRL,
                'liste_client_S1' => $liste_client_S1,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_client_S2' => $liste_client_S2,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'pourcentage_groupe' => $pourcentage_groupe,
                'lotGroupe_S1' => $lotGroupe_S1,
                'lotGroupe_S2' => $lotGroupe_S2,
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



    /**
     * Affectation saisie Index
     *
     * @param $json
     * @return Response|JsonResponse
     * @throws \Exception
     */
    public function affectionAction($json)
    {
        $user_app = $this->get('user_app_check')->check();

        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée."
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine."
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CGP_SAISIE') AND
            !$this->get('security.authorization_checker')->isGranted('ROLE_CGP_IMPUTATION')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page DE MERDE");
        }

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

//        $image_repo = $this->getDoctrine()
//            ->getRepository('AppBundle:Image');
//        $nb_image_S1 = $image_repo
//            ->imageSaisie1($nb_lot_S1,$this->getUser()->getId());
//
//        $nb_image_S2 = $image_repo
//            ->imageSaisie2($nb_lot_S2,$this->getUser()->getId());
//        $nb_image_CTRL = $image_repo
//            ->imageCtrlSaisie($nb_lot_CTRL, $this->getUser()->getId());

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');

        $lot_S1 = $lot_repo
            ->lotSaisie1($this->getUser()->getId());

        $lot_S1_encours = $lot_repo
            ->lotSaisie1($this->getUser()->getId(), true);

        $lot_S2 = $lot_repo
            ->lotSaisie2($this->getUser()->getId());

        $lot_S2_encours = $lot_repo
            ->lotSaisie2($this->getUser()->getId(), true);

        $lot_CTRL = $lot_repo
            ->lotCtrlSaisie($this->getUser()->getId());

        $lot_CTRL_encours = $lot_repo
            ->lotCtrlSaisie($this->getUser()->getId(), true);

        $etape_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_1',
            ));
        $etape_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_2',
            ));
        $etape_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'CTRL_OS',
            ));
        $operateurs_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_S1, $this->getUser()->getId());
        $operateurs_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_S2, $this->getUser()->getId());
        $operateurs_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_CTRL, $this->getUser()->getId());

        $panier_S1 = $panier_repo
            ->getPanierSaisie1($this->getUser()->getId());
        $panier_S2 = $panier_repo
            ->getPanierSaisie2($this->getUser()->getId());
        $panier_CTRL = $panier_repo
            ->getPanierCtrlSaisie($this->getUser()->getId());

        //LISTE CLIENTS - DOSSIERS
        $liste_client_S1 = array();
        $liste_client_S1_encours = array();
        $liste_dossier_S1 = array();
        $liste_dossier_S1_encours= array();
        $liste_client_S2 = array();
        $liste_client_S2_encours = array();
        $liste_dossier_S2 = array();
        $liste_dossier_S2_encours = array();
        $liste_client_CTRL = array();
        $liste_client_CTRL_encours = array();
        $liste_dossier_CTRL = array();
        $liste_dossier_CTRL_encours = array();

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

        foreach ($lot_S1_encours as $lot) {
            if (!in_array($lot->client, $liste_client_S1_encours)) {
                $liste_client_S1_encours[] = $lot->client;
                $liste_dossier_S1_encours[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_S1_encours[$lot->client])) {
                    $liste_dossier_S1_encours[$lot->client][] = $lot->dossier;
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
            if (!in_array($lot->client, $liste_client_S2_encours)) {
                $liste_client_S2_encours[] = $lot->client;
                $liste_dossier_S2_encours[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_S2_encours[$lot->client])) {
                    $liste_dossier_S2_encours[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_CTRL as $lot) {
            if (!in_array($lot->client, $liste_client_CTRL)) {
                $liste_client_CTRL[] = $lot->client;
                $liste_dossier_CTRL[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_CTRL[$lot->client])) {
                    $liste_dossier_CTRL[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_CTRL_encours as $lot) {
            if (!in_array($lot->client, $liste_client_CTRL_encours)) {
                $liste_client_CTRL_encours[] = $lot->client;
                $liste_dossier_CTRL_encours[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_CTRL_encours[$lot->client])) {
                    $liste_dossier_CTRL_encours[$lot->client][] = $lot->dossier;
                }
            }
        }


        ksort($liste_dossier_S1);
        ksort($liste_dossier_S1_encours);
        ksort($liste_dossier_S2);
        ksort($liste_dossier_S2_encours);
        ksort($liste_dossier_CTRL);
        ksort($liste_dossier_CTRL_encours);

        foreach ($liste_dossier_S1 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_S1_encours as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_S2 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_S2_encours as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_CTRL as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_CTRL_encours as $key => &$value) {
            sort($value);
        }


        if ($json == 0) {
            $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            return $this->render('@Tenue/Tenue/affectation-saisie.html.twig', array(
//                'nb_lot_S1' => $nb_lot_S1,
//                'nb_image_S1' => $nb_image_S1,
//                'nb_lot_S2' => $nb_lot_S2,
//                'nb_image_S2' => $nb_image_S2,
//                'nb_lot_CTRL' => $nb_lot_CTRL,
//                'nb_image_CTRL' => $nb_image_CTRL,
                'liste_jour' => $liste_jour,
                'operateurs_S1' => $operateurs_S1,
                'operateurs_S2' => $operateurs_S2,
                'operateurs_CTRL' => $operateurs_CTRL,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'panier_CTRL' => $panier_CTRL,
                'lot_S1' => $lot_S1,
                'lot_S1_encours' => $lot_S1_encours,
                'lot_S2' => $lot_S2,
                'lot_S2_encours' => $lot_S2_encours,
                'lot_CTRL' => $lot_CTRL,
                'lot_CTRL_encours' => $lot_CTRL_encours,
                'liste_client_S1' => $liste_client_S1,
                'liste_client_S1_encours' => $liste_client_S1_encours,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_dossier_S1_encours' => $liste_dossier_S1_encours,
                'liste_client_S2' => $liste_client_S2,
                'liste_client_S2_encours' => $liste_client_S2_encours,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_dossier_S2_encours' => $liste_dossier_S2_encours,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_client_CTRL_encours' => $liste_client_CTRL_encours,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'liste_dossier_CTRL_encours' => $liste_dossier_CTRL_encours,
                'mois' => $mois,
            ));
        } else {
            $panier_S1 = $panier_repo
                ->getPanierSaisie1(true);
            $panier_S2 = $panier_repo
                ->getPanierSaisie2(true);
            $panier_CTRL = $panier_repo
                ->getPanierCtrlSaisie(true);
            $lot_S1 = $lot_repo
                ->lotSaisie1($this->getUser()->getId());
            $lot_S2 = $lot_repo
                ->lotSaisie2($this->getUser()->getId());
            $lot_CTRL = $lot_repo
                ->lotCtrlSaisie($this->getUser()->getId());

            $data = array(
                'erreur' => false,
//                'nb_lot_S1' => $nb_lot_S1,
//                'nb_image_S1' => $nb_image_S1,
//                'nb_lot_S2' => $nb_lot_S2,
//                'nb_image_S2' => $nb_image_S2,
//                'nb_lot_CTRL' => $nb_lot_CTRL,
//                'nb_image_CTRL' => $nb_image_CTRL,
                'liste_jour' => $liste_jour,
                'operateurs_S1' => $operateurs_S1,
                'operateurs_S2' => $operateurs_S2,
                'operateurs_CTRL' => $operateurs_CTRL,
                'panier_S1' => $panier_S1,
                'panier_S2' => $panier_S2,
                'panier_CTRL' => $panier_CTRL,
                'lot_S1' => $lot_S1,
                'lot_S1_encours' => $lot_S1_encours,
                'lot_S2' => $lot_S2,
                'lot_S2_encours' => $lot_S2_encours,
                'lot_CTRL' => $lot_CTRL,
                'lot_CTRL_encours' => $lot_CTRL_encours,
                'liste_client_S1' => $liste_client_S1,
                'liste_client_S1_encours' => $liste_client_S1_encours,
                'liste_dossier_S1' => $liste_dossier_S1,
                'liste_dossier_S1_encours' => $liste_dossier_S1_encours,
                'liste_client_S2' => $liste_client_S2,
                'liste_client_S2_encours' => $liste_client_S2_encours,
                'liste_dossier_S2' => $liste_dossier_S2,
                'liste_dossier_S2_encours' => $liste_dossier_S2_encours,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_client_CTRL_encours' => $liste_client_CTRL_encours,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'liste_dossier_CTRL_encours' => $liste_dossier_CTRL_encours,
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


    /**
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     * @ParamConverter("etape", class="AppBundle:EtapeTraitement", options={"mapping": {"etape": "code"}})
     *
     * @param $operateur
     * @param $lot
     * @param $categorie
     * @param \AppBundle\Entity\EtapeTraitement $etape
     *
     * @return JsonResponse
     */

    public function addToPanierGroupeAction(Request $request, Operateur $operateur, Lot $lot, $categorie, $etape)
    {
        $operateurId = $request->request->get('operateurId');
        $lotId = $request->request->get('lotId');
        $categorieId = $request->request->get('categorieId');
        $codeEtape = $request->request->get('etape');
        $this->getDoctrine()
            ->getRepository('AppBundle:LotUserGroup')
            ->addToLotGroupe($operateurId, $lotId, $categorieId, $codeEtape);

        $data = array(
            'erreur' => false,
            'operateurId' => $operateurId,
            'lotId' => $lotId,
            'categorieId' => $categorieId,
            'etape' => $codeEtape,
        );
        return new JsonResponse(json_encode($data));
    }

    /**
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     * @ParamConverter("etape", class="AppBundle:EtapeTraitement", options={"mapping": {"etape": "code"}})
     *
     * @param $operateur
     * @param $lot
     * @param $categorie
     * @param \AppBundle\Entity\EtapeTraitement $etape
     *
     * @return JsonResponse
     */

    public function addToPanierTenueAction(Request $request, Operateur $operateur, Lot $lot, $categorie, $etape)
    {
        $operateurId = $request->request->get('operateurId');
        $lotId = $request->request->get('lotId');
        $categorieId = $request->request->get('categorieId');
        $codeEtape = $request->request->get('etape');

        $this->getDoctrine()
            ->getRepository('AppBundle:LotUserGroup')
            ->addToLotTenue($operateurId, $lotId, $categorieId, $codeEtape);

        $data = array(
            'erreur' => false,
            'operateurId' => $operateurId,
            'lotId' => $lotId,
            'categorieId' => $categorieId,
            'etape' => $codeEtape,
        );
        return new JsonResponse(json_encode($data));
    }

    /**
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     * @ParamConverter("etape", class="AppBundle:EtapeTraitement", options={"mapping": {"etape": "code"}})
     *
     * @param $operateur
     * @param $lot
     * @param $categorie
     * @param \AppBundle\Entity\EtapeTraitement $etape
     *
     * @return JsonResponse
     */

    public function addToPanierAction(Request $request, Operateur $operateur, Lot $lot, $categorie, $etape)
    {

        $em = $this->getDoctrine()
            ->getManager();
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($categorie != 0) {
            $categorie = $this->getDoctrine()
                ->getRepository('AppBundle:Categorie')
                ->find($categorie);
        } else {
            $categorie = null;
        }

        $images = $this->getDoctrine()
            ->getRepository('AppBundle:ImageATraiter')
            ->getImageATraiterByLotAndCategorie($lot, $categorie, $etape);
        $date_panier = $request->request->get('date_panier');

        /** @var \AppBundle\Entity\Image $image */
        foreach ($images as $image) {
            $categorie_separation = $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->imageCategorieSeparation($image);
            //return new JsonResponse($categorie_separation);

            if ($categorie_separation) {
                if ($categorie_separation->getId() != 16) {
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
                    } elseif ($etape->getCode() == 'OS_2') {
                        $image->setSaisie2(1);
                    } elseif ($etape->getCode() == 'CTRL_OS') {
                        $image->setCtrlSaisie(1);
                    } elseif ($etape->getCode() == 'IMP') {
                        $image->setImputation(1);
                    } elseif ($etape->getCode() == 'CTRL_IMP') {
                        $image->setCtrlImputation(1);
                    }

                    $logs = new Logs();
                    $logs
                        ->setOperateur($this->getUser())
                        ->setDateDebut(new \DateTime())
                        ->setDateFin(new \DateTime())
                        ->setIp($ip)
                        ->setRemarque('PARTAGE LOT ' . $etape->getLibelle() . '(' . $operateur->getId() . ')')
                        ->setLot($lot)
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
                        } elseif ($etape->getCode() == 'OS_2') {
                            $item->setSaisie2(1);
                        } elseif ($etape->getCode() == 'CTRL_OS') {
                            $item->setStatus(5);
                        } elseif ($etape->getCode() == 'IMP') {
                            $item->setStatus(7);
                        } elseif ($etape->getCode() == 'CTRL_IMP') {
                            $item->setStatus(9);
                        }
                    }
                    $em->persist($logs);
                    $em->persist($panier);
                }
            }
        }
        $em->flush();
        $data = array(
            'erreur' => false,
        );
        return new JsonResponse(json_encode($data));
    }




    /**
     * Déplacement d'un lot d'un panier vers un autre panier
     *
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     * @ParamConverter("oldoperateur", class="AppBundle:Operateur")
     * @ParamConverter("etape", class="AppBundle:EtapeTraitement", options={"mapping": {"etape": "code"}})
     *
     * @param Request $request
     * @param Operateur $operateur
     * @param Operateur $oldoperateur
     * @param Lot $lot
     * @param $categorie
     * @param $etape
     *
     * @return JsonResponse
     */
    public function moveToPanierAction(Request $request, Operateur $operateur, Operateur $oldoperateur, Lot $lot, $categorie, $etape)
    {
        $date_panier = new \DateTime($request->request->get('date_panier'));
        $date_panier_org = new \DateTime($request->request->get('date_panier_org'));
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');
        $paniers = $panier_repo
            ->getOneLot($oldoperateur, $etape, $lot, $date_panier_org);
        $ip = $_SERVER['REMOTE_ADDR'];
        $em = $this->getDoctrine()
            ->getManager();

        if ($paniers) {
            foreach ($paniers as $panier) {
                $panier->setOperateur($operateur);
                $panier->setDatePanier($date_panier);

                $logs = new Logs();
                $logs->setOperateur($this->getUser());
                $logs->setDateDebut(new \DateTime());
                $logs->setDateFin(new \DateTime());
                $logs->setIp($ip);
                $logs->setRemarque('DEPLACER LOT ' . $etape->getLibelle());
                $logs->setLot($lot);
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

    /**
     * Retourner un lot déjà partagé vers la liste des lots à partager
     *
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     * @ParamConverter("etape", class="AppBundle:EtapeTraitement", options={"mapping": {"etape": "code"}})
     *
     * @param Request $request
     * @param Operateur $operateur
     * @param Lot $lot
     * @param $etape
     *
     * @return JsonResponse
     */
    public function returnFromPanierAction(Request $request, Operateur $operateur, Lot $lot, $etape)
    {
        try {
            $em = $this->getDoctrine()
                ->getManager();
            $ip = $_SERVER['REMOTE_ADDR'];
            $etape_code = $etape->getCode();
            $date_panier = new \DateTime($request->request->get('date_panier'));
            $paniers = $this->getDoctrine()
                ->getRepository('AppBundle:Panier')
                ->getOneLot($operateur, $etape, $lot, $date_panier);

            $suivis = $this->getDoctrine()
                ->getRepository('AppBundle:PartageSuivi')
                ->getFromLotArray($paniers, $etape);

            foreach ($suivis as $suivi) {
                $suivi->setStatus(0);
            }

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
                        break;
                    case 'OS_2' :
                        $image->setSaisie2(0);
                        break;
                    case 'CTRL_OS' :
                        $image->setCtrlSaisie(0);
                        break;
                    case 'IMP' :
                        $image->setImputation(0);
                        break;
                    case 'CTRL_IMP' :
                        $image->setCtrlImputation(0);
                        break;
                }
                if ($image_a_traiter) {
                    switch ($etape_code) {
                        case 'OS_1' :
                            $image_a_traiter->setSaisie1(0);
                            break;
                        case 'OS_2' :
                            $image_a_traiter->setSaisie2(0);
                            break;
                        case 'CTRL_OS' :
                            $image_a_traiter->setStatus(4);
                            break;
                        case 'IMP' :
                            $image_a_traiter->setStatus(6);
                            break;
                        case 'CTRL_IMP' :
                            $image_a_traiter->setStatus(8);
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


    public function returnFromLotAction(Request $request, Operateur $operateur, Lot $lot, $etape)
    {

        try {
            $userId = $request->request->get('operateur_id');
            $lotId =  $request->request->get('lot_id');
            $codeEtape = $request->request->get('etape');
            $this->getDoctrine()
                ->getRepository('AppBundle:LotUserGroup')
                ->removeLotGroupe($userId, $lotId, $codeEtape);
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
     * Panier Saisie et/ou Imputation
     *
     * @param Request $request
     * @param $json
     * @return Response
     */
    public function panierAction(Request $request, $json)
    {
        $user_app = $this->get('user_app_check')->check();

        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée."
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine."
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SAISIE')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page");
        }

        $current_date = new \DateTime();
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');
        $etape_S1 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_1'
            ));
        $etape_S2 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'OS_2'
            ));
        $etape_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'CTRL_OS'
            ));
        $nbimage_S1 = 0;
        $nbimage_S2 = 0;
        $nbimage_CTRL = 0;
        if ($etape_S1) {
            $nbimage_S1 = $panier_repo
                ->getNbImagePanier($this->getUser(), $etape_S1);
        }
        if ($etape_S2) {
            $nbimage_S2 = $panier_repo
                ->getNbImagePanier($this->getUser(), $etape_S2);
        }
        if ($etape_CTRL) {
            $nbimage_CTRL = $panier_repo
                ->getNbImagePanier($this->getUser(), $etape_CTRL);
        }
        $panier_OS = $panier_repo
            ->getPanierSaisiePerUser($this->getUser());
        if ($json == 0) {
            return $this->render('TenueBundle:Tenue:panier-saisie.html.twig', [
                'current_date' => $current_date,
                'panier_OS' => $panier_OS,
                'nbimage_S1' => $nbimage_S1,
                'nbimage_S2' => $nbimage_S2,
                'nbimage_CTRL' => $nbimage_CTRL,
            ]);
        } else {
            $data = [
                'current_date' => $current_date,
                'panier_OS' => $panier_OS,
                'nbimage_S1' => $nbimage_S1,
                'nbimage_S2' => $nbimage_S2,
                'nbimage_CTRL' => $nbimage_CTRL,
            ];

            return new JsonResponse($data);
        }
    }
}
