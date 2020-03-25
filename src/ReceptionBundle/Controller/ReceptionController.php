<?php

namespace ReceptionBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Lot;
use AppBundle\Entity\LotATelecharger;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\PanierReception;
use AppBundle\Entity\UserAControler;
use AppBundle\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class ReceptionController extends Controller
{
    public function indexAction($json)
    {
        $user_app = $this->get('user_app_check')->check();
        //return new Response(var_dump($_SERVER['REMOTE_ADDR']));
        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }
        return $this->render('ReceptionBundle:Reception:index.html.twig', array(
            'image_non_descendu' => 0,
            'lot_non_descendu' => 0,
            'image_attente_traitement' => 0,
            'lot_attente_traitement' => 0,
            'image_remonte_current' => 0,
            'lot_remonte_current' => 0,
            'image_niv1' => 0,
            'lot_niv1' => 0,
            'image_niv2' => 0,
            'lot_niv2' => 0,
            'current_date' => 0,
        ));
        /*if ($this->get('security.authorization_checker')->isGranted('ROLE_CGP_RECEPTION')) {

            $image_repo = $this->getDoctrine()
                ->getRepository('AppBundle:Image');
            $current_date = new \DateTime();

            if ($json == 0) {
                return $this->render('ReceptionBundle:Reception:index.html.twig', array(
                    'image_non_descendu' => 0,
                    'lot_non_descendu' => 0,
                    'image_attente_traitement' => 0,
                    'lot_attente_traitement' => 0,
                    'image_remonte_current' => 0,
                    'lot_remonte_current' => 0,
                    'image_niv1' => 0,
                    'lot_niv1' => 0,
                    'image_niv2' => 0,
                    'lot_niv2' => 0,
                    'current_date' => 0,
                ));
            } else {
                $image_non_descendu = $image_repo
                    ->imageNonDescendu($lot_non_descendu);
                $image_attente_traitement = $image_repo
                    ->imageAttenteTraitement($lot_attente_traitement);
                $image_remonte_current = $image_repo
                    ->imageRemonteCurrent($lot_remonte_current);
                $image_niv1 = $image_repo
                    ->imageTraitementN1($lot_niv_1);
                $image_niv2 = $image_repo
                    ->imageTraitementN2($lot_niv_2);

                $data = array(
                    'erreur' => false,
                    'image_non_descendu' => $image_non_descendu,
                    'lot_non_descendu' => $lot_non_descendu,
                    'image_attente_traitement' => $image_attente_traitement,
                    'lot_attente_traitement' => $lot_attente_traitement,
                    'image_remonte_current' => $image_remonte_current,
                    'lot_remonte_current' => $lot_remonte_current,
                    'image_niv1' => $image_niv1,
                    'lot_niv1' => $lot_niv_1,
                    'image_niv2' => $image_niv2,
                    'lot_niv2' => $lot_niv_2,
                    'current_date' => $current_date,
                );

                return new JsonResponse($data);
            }
        } else {
            return $this->redirectToRoute('reception_panier');

        }
        //return new Response("");*/
    }


    public function dashBordAction($json)
    {
        $user_app = $this->get('user_app_check')->check();
        //return new Response(var_dump($_SERVER['REMOTE_ADDR']));
        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CGP_RECEPTION')) {

            $image_repo = $this->getDoctrine()
                ->getRepository('AppBundle:Image');
            $current_date = new \DateTime();

            if ($json == 0) {
                return $this->render('ReceptionBundle:Reception:dashBord.html.twig', array(
                    'image_non_descendu' => 0,
                    'lot_non_descendu' => 0,
                    'image_attente_traitement' => 0,
                    'lot_attente_traitement' => 0,
                    'image_remonte_current' => 0,
                    'lot_remonte_current' => 0,
                    'image_niv1' => 0,
                    'lot_niv1' => 0,
                    'image_niv2' => 0,
                    'lot_niv2' => 0,
                    'current_date' => 0,
                ));
            } else {
                $image_non_descendu = $image_repo
                    ->imageNonDescendu($lot_non_descendu);
                $image_attente_traitement = $image_repo
                    ->imageAttenteTraitement($lot_attente_traitement);
                $image_remonte_current = $image_repo
                    ->imageRemonteCurrent($lot_remonte_current);
                $image_niv1 = $image_repo
                    ->imageTraitementN1($lot_niv_1);
                $image_niv2 = $image_repo
                    ->imageTraitementN2($lot_niv_2);

                $data = array(
                    'erreur' => false,
                    'image_non_descendu' => $image_non_descendu,
                    'lot_non_descendu' => $lot_non_descendu,
                    'image_attente_traitement' => $image_attente_traitement,
                    'lot_attente_traitement' => $lot_attente_traitement,
                    'image_remonte_current' => $image_remonte_current,
                    'lot_remonte_current' => $lot_remonte_current,
                    'image_niv1' => $image_niv1,
                    'lot_niv1' => $lot_niv_1,
                    'image_niv2' => $image_niv2,
                    'lot_niv2' => $lot_niv_2,
                    'current_date' => $current_date,
                );

                return new JsonResponse($data);
            }
        } else {
            return $this->redirectToRoute('reception_panier');

        }

    }

    public function fluxAction()
    {
        return $this->redirectToRoute('reception_dashboard');
    }

    public function traiteAction()
    {
        return $this->redirectToRoute('reception_dashboard');
    }

    public function separationAction()
    {
        return $this->redirectToRoute('reception_dashboard');
    }

    public function histogrammeAction()
    {
        return $this->redirectToRoute('reception_dashboard');
    }

    public function priorisationAction()
    {
        return $this->redirectToRoute('reception_dashboard');
    }


    public function telechargerAction(Request $request)
    {
        $dateDown = \DateTime::createFromFormat('d/m/Y', $request->request->get('dateDown'));
        $tele = $this->getDoctrine()->getRepository('AppBundle:Lot')
            ->lotTelecharger($dateDown);
        return new JsonResponse($tele);
    }

    public function retelechargerAction(Request $request)
    {
        $lotId = $request->request->get('lot_id');
        $ret = $this->getDoctrine()->getRepository('AppBundle:Lot')
            ->retelechargerLot($lotId);
        return new JsonResponse($lotId);
    }



    public function tirageAction(Request $request, $json)
    {
        $user_app = $this->get('user_app_check')->check();
        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CGP_RECEPTION')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page");
        }

        $now = new \DateTime();


        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');

        $lot_N1 = $lot_repo
            ->getLotTirage($nb_lot_niv1, $nb_image_niv1);

        $liste_client_N1 = array();
        $liste_dossier_N1 = array();
        $liste_client_N2 = array();
        $liste_dossier_N2 = array();

        foreach ($lot_N1 as $lot) {
            if (!in_array($lot->client, $liste_client_N1)) {
                $liste_client_N1[] = $lot->client;
                $liste_dossier_N1[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_N1[$lot->client])) {
                    $liste_dossier_N1[$lot->client][] = $lot->dossier;
                }
            }
        }

        ksort($liste_dossier_N1);

        foreach ($liste_dossier_N1 as $key => &$value) {
            sort($value);
        }


        $lotTelecharger = $this->getDoctrine()
            ->getRepository('AppBundle:Lot')
            ->lotTelecharger(new \DateTime());


        $codeCouleur = $this->getDoctrine()
            ->getRepository('AppBundle:TachesPrioriteCouleur')
            ->findAll();

        if ($json == 0)
        {
            return $this->render('@Reception/Reception/tirage-priorite_tirage.html.twig', array(
                'nb_lot' => $nb_lot_niv1,
                'nb_image' => $nb_image_niv1,
                //'nb_lot_niv2' => $nb_lot_niv2,
                //'nb_image_niv2' => $nb_image_niv2,

                //'operateurs_niv1' => $operateurs_niv1,
                //'operateurs_niv2' => $operateurs_niv2,
                //'panier_niv1' => $panier_niv1,
                //'panier_niv2' => $panier_niv2,
                'lot_N1' => $lot_N1,

                'liste_client_N1' => $liste_client_N1,
                'liste_dossier_N1' => $liste_dossier_N1,
                'liste_client_N2' => $liste_client_N2,
                'liste_dossier_N2' => $liste_dossier_N2,
                'lot_telecharger' => $lotTelecharger,
                'code_couleur' => $codeCouleur,
            ));
        }
        else
        {
            return $this->render('@Reception/Reception/tirage-listeLotATelecharger.html.twig', array(
                'nb_lot' => $nb_lot_niv1,
                'nb_image' => $nb_image_niv1,
                //'nb_lot_niv2' => $nb_lot_niv2,
                //'nb_image_niv2' => $nb_image_niv2,

                //'operateurs_niv1' => $operateurs_niv1,
                //'operateurs_niv2' => $operateurs_niv2,
                //'panier_niv1' => $panier_niv1,
                //'panier_niv2' => $panier_niv2,
                'lot_N1' => $lot_N1,

                'liste_client_N1' => $liste_client_N1,
                'liste_dossier_N1' => $liste_dossier_N1,
                'liste_client_N2' => $liste_client_N2,
                'liste_dossier_N2' => $liste_dossier_N2,
                'lot_telecharger' => $lotTelecharger,
                'code_couleur' => $codeCouleur,
            ));

        }
    }



    /**
     * Affichage de la page d'affectation des lots
     *
     * @param Request $request
     * @param $json int retourner json ou html
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function affectationAction(Request $request, $json)
    {
        $user_app = $this->get('user_app_check')->check();

        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CGP_RECEPTION')) {
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

//        $image_repo = $this->getDoctrine()
//            ->getRepository('AppBundle:Image');
//        $nb_image_niv1 = $image_repo
//            ->imageTraitementN1($nb_lot_niv1);
//        $nb_image_niv2 = $image_repo
//            ->imageTraitementN2($nb_lot_niv2);

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');

        $lot_N1 = $lot_repo
            ->lotN1();

        $lot_N1_encours = $lot_repo
            ->lotN1(true);

        //return $lot_N1;
        $lot_N2 = $lot_repo
            ->lotN2();

        $lot_N2_encours = $lot_repo
            ->lotN2(true);

        $etape_niv1 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'DEC_NIV_1',
            ));
        $etape_niv2 = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'DEC_NIV_2',
            ));

        $operateurs_niv1 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_niv1);

        $operateurs_niv2 = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_niv2);


        $panier_reception_repo = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception');

        $panier_niv1 = $panier_reception_repo
            ->getPanierNiv1();
        $panier_niv2 = $panier_reception_repo
            ->getPanierNiv2();

        $liste_client_N1 = array();
        $liste_client_N1_encours = array();
        $liste_dossier_N1 = array();
        $liste_dossier_N1_encours = array();
        $liste_client_N2 = array();
        $liste_client_N2_encours = array();
        $liste_dossier_N2 = array();
        $liste_dossier_N2_encours = array();

        foreach ($lot_N1 as $lot) {
            if (!in_array($lot->client, $liste_client_N1)) {
                $liste_client_N1[] = $lot->client;
                $liste_dossier_N1[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_N1[$lot->client])) {
                    $liste_dossier_N1[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_N1_encours as $lot) {
            if (!in_array($lot->client, $liste_client_N1_encours)) {
                $liste_client_N1_encours[] = $lot->client;
                $liste_dossier_N1_encours[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_N1_encours[$lot->client])) {
                    $liste_dossier_N1_encours[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_N2 as $lot) {
            if (!in_array($lot->client, $liste_client_N2)) {
                $liste_client_N2[] = $lot->client;
                $liste_dossier_N2[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_N2[$lot->client])) {
                    $liste_dossier_N2[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_N2_encours as $lot) {
            if (!in_array($lot->client, $liste_client_N2_encours)) {
                $liste_client_N2_encours[] = $lot->client;
                $liste_dossier_N2_encours[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_N2_encours[$lot->client])) {
                    $liste_dossier_N2_encours[$lot->client][] = $lot->dossier;
                }
            }
        }

        ksort($liste_dossier_N1);
        ksort($liste_dossier_N2);

        foreach ($liste_dossier_N1 as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_N2 as $key => &$value) {
            sort($value);
        }

        if ($json == 0) {
            $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            return $this->render('@Reception/Reception/affectation.html.twig', array(
//                'nb_lot_niv1' => $nb_lot_niv1,
//                'nb_image_niv1' => $nb_image_niv1,
//                'nb_lot_niv2' => $nb_lot_niv2,
//                'nb_image_niv2' => $nb_image_niv2,
                'liste_jour' => $liste_jour,
                'operateurs_niv1' => $operateurs_niv1,
                'operateurs_niv2' => $operateurs_niv2,
                'panier_niv1' => $panier_niv1,
                'panier_niv2' => $panier_niv2,
                'lot_N1' => $lot_N1,
                'lot_N1_encours' => $lot_N1_encours,
                'lot_N2' => $lot_N2,
                'lot_N2_encours' => $lot_N2_encours,
                'liste_client_N1' => $liste_client_N1,
                'liste_client_N1_encours' => $liste_client_N1_encours,
                'liste_dossier_N1' => $liste_dossier_N1,
                'liste_dossier_N1_encours' => $liste_dossier_N1_encours,
                'liste_client_N2' => $liste_client_N2,
                'liste_client_N2_encours' => $liste_client_N2_encours,
                'liste_dossier_N2' => $liste_dossier_N2,
                'liste_dossier_N2_encours' => $liste_dossier_N2_encours,
                'mois' => $mois,
            ));
        } else {
            $operateurs = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->getOperateurCellule('image', true);

            $panier_niv1 = $panier_reception_repo
                ->getPanierNiv1();
            $panier_niv2 = $panier_reception_repo
                ->getPanierNiv2();
            $data = array(
                'erreur' => false,
//                'nb_lot_niv1' => $nb_lot_niv1,
//                'nb_image_niv1' => $nb_image_niv1,
//                'nb_lot_niv2' => $nb_lot_niv2,
//                'nb_image_niv2' => $nb_image_niv2,
                'liste_jour' => $liste_jour,
                'operateurs' => json_decode(json_encode($operateurs), TRUE),
                'panier_niv1' => $panier_niv1,
                'panier_niv2' => $panier_niv2,
                'lot_N1' => json_decode(json_encode($lot_N1), TRUE),
                'lot_N1_encours' => json_decode(json_encode($lot_N1_encours), TRUE),
                'lot_N2' => json_decode(json_encode($lot_N2), TRUE),
                'lot_N2_encours' => json_decode(json_encode($lot_N2_encours), TRUE),
                'liste_client_N1' => $liste_client_N1,
                'liste_client_N1_encours' => $liste_client_N1_encours,
                'liste_dossier_N1' => $liste_dossier_N1,
                'liste_dossier_N1_encours' => $liste_dossier_N1_encours,
                'liste_client_N2' => $liste_client_N2,
                'liste_client_N2_encours' => $liste_client_N2_encours,
                'liste_dossier_N2' => $liste_dossier_N2,
                'liste_dossier_N2_encours' => $liste_dossier_N2_encours,
            );

            return new JsonResponse($data);

        }
    }

    /**
     * Ajouter un lot dans un panier d'un utilisateur
     *
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     *
     * @param Request $request
     * @param Operateur $operateur
     * @param Lot $lot
     * @param int $status
     * @return JsonResponse
     */
    public function addToPanierAction(Request $request, Operateur $operateur, Lot $lot, $status)
    {
        try {
            if ($request->isXmlHttpRequest()) {
                $em = $this->getDoctrine()
                    ->getManager();

                $niveau = $status == 1 ? '1' : '2';
                $code_etape_partage = 'P_R_NIV_1';
                $code_etape = 'DEC_NIV_1';
                if ($status != 1) {
                    $code_etape_partage = 'P_R_NIV_2';
                    $code_etape = 'DEC_NIV_2';
                }
                $etape_traitement_partage = $this->getDoctrine()
                    ->getRepository('AppBundle:EtapeTraitement')
                    ->findOneBy(array('code' => $code_etape_partage));
                $etape_traitement = $this->getDoctrine()
                    ->getRepository('AppBundle:EtapeTraitement')
                    ->findOneBy(array('code' => $code_etape));

                $date_panier = $request->request->get('date_panier');
                $ip = $_SERVER['REMOTE_ADDR'];
                $panier_reception = new PanierReception();
                $panier_reception
                    ->setLot($lot)
                    ->setOperateur($operateur)
                    ->setDatePanier(new \DateTime($date_panier))
                    ->setEtapeTraitement($etape_traitement);

                $lot->setStatus(intval($status));

                $a_traiter = $this->getDoctrine()
                    ->getRepository('AppBundle:ImageATraiter')
                    ->getImageByLot($lot->getId());
                $traitement_status = $status == 1 ? 1 : 3;

                if ($a_traiter && count($a_traiter) > 0) {
                    /** @var \AppBundle\Entity\ImageATraiter $item */
                    foreach ($a_traiter as $item) {
                        $item->setStatus($traitement_status);
                    }
                }



                $logs = new Logs();
                $logs->setOperateur($this->getUser());
                $logs->setDateDebut(new \DateTime());
                $logs->setDateFin(new \DateTime());
                $logs->setIp($ip);
                $logs->setRemarque('PARTAGE REC. NIV. ' . $niveau);
                $logs->setLot($lot);
                $logs->setEtapeTraitement($etape_traitement_partage);

                $em->persist($panier_reception);
                $em->persist($logs);


                $em->flush();

                $data = array(
                    'erreur' => false,
                    'panier_id' => $panier_reception->getId(),
                );
                return new JsonResponse(json_encode($data));
            } else {
                throw new AccessDeniedHttpException("Accus refusé.");
            }
        } catch (\Exception $ex) {
            $data = array(
                'erreur' => true,
                'erreur_text' => $ex->getMessage(),
            );
            return new JsonResponse(json_encode($data));
        }
    }

    /**
     * Déplacer un lot d'un panier vers un autre panier
     *
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("operateur", class="AppBundle:Operateur")
     * @ParamConverter("panier", class="AppBundle:PanierReception")
     *
     * @param Request $request
     * @param Operateur $operateur
     * @param Lot $lot
     * @param int $status
     * @param PanierReception $panier
     * @return JsonResponse
     */
    public function moveToPanierAction(Request $request, Operateur $operateur, Lot $lot, $status, PanierReception $panier)
    {
        try {
            if ($request->isXmlHttpRequest()) {
                $em = $this->getDoctrine()
                    ->getManager();
                $date_panier = $request->request->get('date_panier');
                $ip = $_SERVER['REMOTE_ADDR'];
                $panier->setOperateur($operateur);
                $panier->setDatePanier(new \DateTime($date_panier));

                $niveau = $status == 1 ? '1' : '2';
                $code_etape = 'P_R_NIV_1';
                if ($status != 1) {
                    $code_etape = 'P_R_NIV_2';
                }
                $etape_traitement = $this->getDoctrine()
                    ->getRepository('AppBundle:EtapeTraitement')
                    ->findOneBy(array('code' => $code_etape));

                $logs = new Logs();
                $logs->setOperateur($this->getUser());
                $logs->setDateDebut(new \DateTime());
                $logs->setDateFin(new \DateTime());
                $logs->setIp($ip);
                $logs->setRemarque('DEPLACER LOT REC. NIV. ' . $niveau);
                $logs->setLot($lot);
                $logs->setEtapeTraitement($etape_traitement);

                $em->persist($logs);


                $em->flush();

                $data = array(
                    'erreur' => false,
                    'panier_id' => $panier->getId(),
                );
                return new JsonResponse(json_encode($data));
            }
        } catch (\Exception $ex) {
            $data = array(
                'erreur' => true,
                'erreur_text' => $ex->getMessage(),
            );
            return new JsonResponse(json_encode($data));
        }
    }

    /**
     * Retourner un lot déjà partagé vers la liste des lots à partager
     *
     * @ParamConverter("lot", class="AppBundle:Lot")
     * @ParamConverter("panier", class="AppBundle:PanierReception")
     *
     * @param Request $request
     * @param Lot $lot
     * @param $status
     * @param PanierReception $panier
     * @return JsonResponse
     */
    public function returnFromPanierAction(Request $request, Lot $lot, $status, PanierReception $panier)
    {
        try {
            $em = $this->getDoctrine()
                ->getManager();
            $ip = $_SERVER['REMOTE_ADDR'];

            $em->remove($panier);
            $lot->setStatus($status);

            $niveau = $status == 0 ? '1' : '2';
            $code_etape = 'P_R_NIV_1';
            if ($status != 0) {
                $code_etape = 'P_R_NIV_2';
            }
            $etape_traitement = $this->getDoctrine()
                ->getRepository('AppBundle:EtapeTraitement')
                ->findOneBy(array('code' => $code_etape));

            $logs = new Logs();
            $logs->setOperateur($this->getUser());
            $logs->setDateDebut(new \DateTime());
            $logs->setDateFin(new \DateTime());
            $logs->setIp($ip);
            $logs->setRemarque('RETOUR LOT REC. NIV. ' . $niveau);
            $logs->setLot($lot);
            $logs->setEtapeTraitement($etape_traitement);

            $em->persist($logs);

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

    public function panierAction($json)
    {
        $user_app = $this->get('user_app_check')->check();

        if ($user_app != "ok") {
            if ($user_app == "missing") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_missing');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface non connectée.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            } elseif ($user_app == "mismatch") {
                if ($json == 0) {
                    return $this->redirectToRoute('user_app_mismatch');
                } else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Interface connectée sur une autre machine.",
                    );

                    return new JsonResponse(json_encode($data));
                }
            }
        }

        $etape_traitement_repo = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement');
        $etapeN1 = $etape_traitement_repo
            ->getByCode('DEC_NIV_1');
        $etapeN2 = $etape_traitement_repo
            ->getByCode('DEC_NIV_2');

        $current_date = new \DateTime();

        $lotN1 = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->getPanierPerUser($this->getUser(), $etape_traitement_repo->findOneBy(['code' => 'DEC_NIV_1']), $imageN1);
        $lotN2 = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->getPanierPerUser2($this->getUser(), $etape_traitement_repo->findOneBy(['code' => 'DEC_NIV_2']), $imageN2);
        $lotFini1_today = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->getPanierFiniPerUserToday_1($this->getUser(), $etape_traitement_repo->findOneBy(['code' => 'DEC_NIV_1']), $image_finiN1_today);
        $lot_finiN1 = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->getPanierFiniPerUser($this->getUser(), $etape_traitement_repo->findOneBy(['code' => 'DEC_NIV_1']), $image_finiN1);
        $lot_finiN2 = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->getPanierFiniPerUser2($this->getUser(), $etape_traitement_repo->findOneBy(['code' => 'DEC_NIV_2']), $image_finiN2);
        $lotFini2_today = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->getPanierFiniPerUserToday_2($this->getUser(), $etape_traitement_repo->findOneBy(['code' => 'DEC_NIV_2']), $image_finiN2_today);
        $date_depuis = date('d-m-Y', strtotime('-3 day'));

        //controle si admin ou separation pour parametrage separation
        $showParametrage = false;
        $user = $this->getUser();
        $codePosteUser = $user->getOrganisation()->getCode();
        if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $codePosteUser == 'CODE_CHEF_SEPARATION') $showParametrage = true;

        if ($json == 0) {
            return $this->render('ReceptionBundle:Reception:panier.html.twig', array(
                'lotN1' => $lotN1,
                'imageN1' => $imageN1,
                'lotN2' => $lotN2,
                'imageN2' => $imageN2,
                'current_date' => $current_date,
                'lot_finiN1' => $lot_finiN1,
                'lot_fini1_today' => $lotFini1_today,
                'lot_fini2_today' => $lotFini2_today,
                'lot_finiN2' => $lot_finiN2,
                'image_finiN1' => $image_finiN1,
                'image_fini1_today' => $image_finiN1_today,
                'image_fini2_today' => $image_finiN2_today,
                'image_finiN2' => $image_finiN2,
                'etapeN1' => $etapeN1,
                'etapeN2' => $etapeN2,
                'date_depuis' => $date_depuis,
                'show_parametrage' => $showParametrage
            ));
        } elseif ($json == 1) {
            $data = array(
                'erreur' => false,
                'lotN1' => $lotN1,
                'imageN1' => $imageN1,
                'lotN2' => $lotN2,
                'imageN2' => $imageN2,
                'current_date' => $current_date,
                'lot_finiN1' => $lot_finiN1,
                'lot_finiN2' => $lot_finiN2,
                'lot_fini1_today' => $lotFini1_today,
                'lot_fini2_today' => $lotFini2_today,
                'image_finiN1' => $image_finiN1,
                'image_finiN2' => $image_finiN2,
                'image_fini1_today' => $image_finiN1_today,
                'image_fini2_today' => $image_finiN2_today,
                'etapeN1' => $etapeN1,
                'etapeN2' => $etapeN2,
                'date_depuis' => $date_depuis,
                'show_parametrage' => $showParametrage
            );
            return new JsonResponse($data);
        }
        throw new BadRequestHttpException("Requête malformée.");
    }


    public function affectationDossierAction()
    {
        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getAllResponsable();


        $responsables = $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->getAllResponsable();

        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();

        $clis = array();$cli = array();
        foreach($clients as $client){
            $cli['nom'] =$client->getNom();
            $cli['id'] =$client->getId();
            $cli['bold']= $this->getDoctrine()->getRepository('AppBundle:ResponsableClient')
                ->isAffecte($client->getId());
            if($cli['bold']){
                $clias[]= $cli;
            } else {
                $clis[]=$cli;
            }
        }

        $respas = array();$resps = array();
        foreach($operateurs as $respi){
            //print_r($respi);
            //die();
            $resp['nom'] =$respi->nom;
            $resp['prenom'] =$respi->prenom;
            $resp['id'] =$respi->id;
            $resp['bold']= $this->getDoctrine()->getRepository('AppBundle:ResponsableClient')
                ->isAffecteR($respi->id);
            if($resp['bold']){
                $respas[]= $resp;
            } else {
                $resps[]=$resp;
            }
        }

        return $this->render('@Reception/Reception/affectationDossierRec.html.twig', array(
            'operateurs' => $operateurs,
            'responsables' => $responsables,
            'clients' => $clis,
            'clientas' => $clias,
            'resps' => $resps,
            'respas' => $respas,
        ));

    }

    /**
     *
     *
     */

    public function getDownloadEnCourAction()
    {
        /** @var LotATelecharger[] $downloadEnCours */
        $downloadEnCours = $this->getDoctrine()
            ->getRepository('AppBundle:LotATelecharger')
            ->findAll();

        $liste_dossier_enCours = array();
        $liste_client_enCours = array();

        foreach ($downloadEnCours as $lot) {
            if (!in_array($lot->getCabinet(), $liste_client_enCours)) {
                $liste_client_enCours[] = $lot->getCabinet();
                $liste_dossier_enCours[$lot->getCabinet()][] = $lot->getDossier();
            } else {
                if (!in_array($lot->getDossier(), $liste_dossier_enCours[$lot->getCabinet()])) {
                    $liste_dossier_enCours[$lot->getCabinet()][] = $lot->getDossier();
                }
            }
        }
        return $this->render('@Reception/Reception/tirage-downloadEncours.html.twig', array('downloadEnCours' => $downloadEnCours,
            'liste_client_enCours' => $liste_client_enCours,
            'liste_dossier_enCours' => $liste_dossier_enCours ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveLotATelechargerAction(Request $request)
    {
        $m = $this->getDoctrine()->getManager();
        $datas = json_decode($request->request->get('variable'));
        $niveau = json_decode($request->request->get('niveau'));
        if ($niveau == 0)
        {
            foreach ($datas as $data) {
                $newLAT = new LotATelecharger();
                $newLAT->setCabinet($data->cabinet);
                $newLAT->setDossier($data->dossier);
                $newLAT->setExercice($data->exercice);
                $dateScan = \DateTime::createFromFormat('Y-m-d', $data->dateScan);
                $newLAT->setDateScan($dateScan);
                $newLAT->setLot($data->lot);
                $lot2 = $this->getDoctrine()->getRepository('AppBundle:Lot')->find($data->lotId);
                $newLAT->setLot2($lot2);
                $newLAT->setNbImage($data->nbImage);
                $newLAT->setStatus($data->status);

                $m->persist($newLAT);
            }
            $m->flush();
        }
        else  {
            foreach ($datas as $data) {
                $lot = $this->getDoctrine()
                    ->getRepository('AppBundle:Lot')
                    ->find($data->lotId);

                $lotA = $this->getDoctrine()
                    ->getRepository('AppBundle:LotATelecharger')
                    ->findBy(array('lot2'=>$lot));

                if (count($lotA) > 0)
                    $m->remove($lotA[0]);
            }
            $m->flush();

            foreach ($datas as $data) {
                $newLAT = new LotATelecharger();
                $newLAT->setCabinet($data->cabinet);
                $newLAT->setDossier($data->dossier);
                $newLAT->setExercice($data->exercice);
                $dateScan = \DateTime::createFromFormat('d/m/Y', $data->dateScan);
                $newLAT->setDateScan($dateScan);
                $newLAT->setLot($data->lot);
                $lot2 = $this->getDoctrine()->getRepository('AppBundle:Lot')->find($data->lotId);
                $newLAT->setLot2($lot2);
                $newLAT->setNbImage($data->nbImage);
                $newLAT->setStatus($data->status);

                $m->persist($newLAT);
            }
            $m->flush();
        }

        return new Response();
    }

    public function removeDownloadEnCoursAction()
    {
        $down = $this->getDoctrine()
            ->getRepository('AppBundle:LotATelecharger')
            ->viderLotATelecharger();
        return new JsonResponse(true);
    }

    public function tirageRechercheCabDos()
    {

    }

    public function tirageGetListeClientDossierAction(Request $request)
    {
        $listeDossier = $request->query->get('dossier-list');
        return $this->render('@Reception/Reception/tirage-listeClientDossier.html.twig',
            array('liste_dossier_N1'=>$listeDossier)
        );
    }

    public function fermerDownloadAction()
    {
        $download = $this->getDoctrine()
            ->getRepository('AppBundle:DownloadGestionAppli')
            ->fermerDownload();
        return new JsonResponse(true);
    }

    public function updateFileExtensionAction()
    {
        return $this->render('@Reception/Reception/update-file-extension.html.twig');
    }

    public function saveFileExtensionAction(Request $request)
    {
        $nom_fichier = $request->query->get('nomFichier');
        $nbPage = $request->query->get('nbpage');
        /** @var Image $image */
        $image = $this->getDoctrine()
            ->getRepository('AppBundle:Image')
            ->findOneBy(array('nom' => $nom_fichier));
        $retour = 0;
        if ($image) {

            $this->getDoctrine()
                ->getRepository('AppBundle:Image')
                ->saveFileExtension($image->getId(), 'pdf', $nbPage);
            $retour = 1;
        }
        return new JsonResponse($retour);
    }

    public function lancerDownloadAction()
    {
        $down = $this->getDoctrine()
            ->getRepository('AppBundle:DownloadGestionAppli')
            ->lancerDownload();
        return new JsonResponse(true);
    }

    public function correctionSeparationAction(){
        $em = $this->getDoctrine()
                   ->getManager();
        $etape_traitement = $this->getDoctrine()
                                 ->getRepository('AppBundle:EtapeTraitement')
                                 ->find(37);
        if (!empty($etape_traitement)) {
            $user_app = $this->getDoctrine()
                             ->getRepository('AppBundle:UserApplication')
                             ->getUserApp($this->getUser());

            if (!empty($user_app)) {
                if(($user_app->getIp() == $_SERVER['REMOTE_ADDR']) || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                    $new_user_app = $this->getDoctrine()
                                         ->getRepository('AppBundle:UserApplication')
                                         ->find($user_app->getId());
                    $new_user_app->setEtapeTraitement($etape_traitement)
                                 ->setParametre($this->getUser()->getId());

                    $em->flush();

                    $data = array(
                        'erreur' => false
                    );
                    return new JsonResponse($data);
                }
                $data = array(
                    'erreur' => true,
                    'erreur_text' => "Interface non connectée ou machine de  connexion différente",
                );
                return new JsonResponse($data);
            }else {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => "Interface non connectée ou machine de  connexion différente",
                );
                return new JsonResponse($data);
            }
        }else {
            $data = array(
                'erreur' => true,
                'erreur_text' => 'Application introuvable',
            );
            return new JsonResponse($data);
        }
    }

    public function controleSeparationAction(){
        $em = $this->getDoctrine()
            ->getManager();
        $etape_traitement = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->find(33);
        if (!empty($etape_traitement)) {

            $user_app = $this->getDoctrine()
                ->getRepository('AppBundle:UserApplication')
                ->getUserApp($this->getUser());

            if (!empty($user_app->getIp())) {

                if(($user_app->getIp() == $_SERVER['REMOTE_ADDR']) || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
                    || $this->get('security.authorization_checker')->isGranted('ROLE_CGP_RECEPTION'))
                {

                        $new_user_app = $this->getDoctrine()
                            ->getRepository('AppBundle:UserApplication')
                            ->find($user_app->getId());
                        $new_user_app->setEtapeTraitement($etape_traitement)
                            ->setParametre($this->getUser()->getId());

                        $em->flush();

                        $data = array(
                            'erreur' => false
                        );
                        return new JsonResponse($data);

                }
                else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Vous n'êtes pas autorisé à faire le contrôle",
                    );
                    return new JsonResponse($data);
                }
            }else {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $user_app."aaa Interface non connectée ou machine de  connexion différente",
                );
                return new JsonResponse($data);
            }
        }else {
            $data = array(
                'erreur' => true,
                'erreur_text' => 'Application introuvable',
            );
            return new JsonResponse($data);
        }
    }

    public function controleDecoupageAction(){
        $em = $this->getDoctrine()
            ->getManager();
        $etape_traitement = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->find(35);
        if (!empty($etape_traitement)) {
            $user_app = $this->getDoctrine()
                ->getRepository('AppBundle:UserApplication')
                ->getUserApp($this->getUser());
            if (!empty($user_app)) {
                if(($user_app->getIp() == $_SERVER['REMOTE_ADDR']) || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
                    || $this->get('security.authorization_checker')->isGranted('ROLE_CGP_RECEPTION')) {
                    $new_user_app = $this->getDoctrine()
                        ->getRepository('AppBundle:UserApplication')
                        ->find($user_app->getId());
                    $new_user_app->setEtapeTraitement($etape_traitement)
                        ->setParametre($this->getUser()->getId());

                    $em->flush();

                    $data = array(
                        'erreur' => false
                    );
                    return new JsonResponse($data);
                }
                else {
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => "Vous n'êtes pas autorisé à faire le contrôle",
                    );
                    return new JsonResponse($data);
                }
            }else {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => "Interface non connectée ou machine de  connexion différente",
                );
                return new JsonResponse($data);
            }
        }else {
            $data = array(
                'erreur' => true,
                'erreur_text' => 'Application introuvable',
            );
            return new JsonResponse($data);
        }
    }

    public function parametrageSeparationAction(){
        $em = $this->getDoctrine()
                   ->getManager();

        $res = $this->getDoctrine()
                    ->getRepository('AppBundle:UserAControler')
                    ->getAllResponsableSeparation();

        foreach ($res as $v) {
            $userAcontroler = $this->getDoctrine()
                                ->getRepository('AppBundle:UserAControler')
                                ->findBy(array(
                                    'operateur' => $v,
                                    'organisation' => $v->getOrganisation()
                                ));

            if(count($userAcontroler) == 0){
                $userAcontrolerEntity = new UserAControler();
                $userAcontrolerEntity->setOperateur($v)
                               ->setOrganisation($v->getOrganisation())
                               ->setAControler(0);
                $em->persist($userAcontrolerEntity);
            }
            $em->flush();
        }
        $userDelete = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception')
            ->nettoyerUserAControler();
        $datas = $this->getDoctrine()
                       ->getRepository('AppBundle:UserAControler')
                       ->findAll();

        return $this->render('ReceptionBundle:Reception:parametrage-liste-separation.html.twig', array(
            'datas' => $datas
        ));
    }
    public function saveParametrageSeparationAction(Request $request)
    {
        $datas = $request->request->get('datas');
        $em = $this->getDoctrine()
                   ->getManager();
        $user = $this->getUser();
        foreach ($datas as $d) {
            $uac = $this->getDoctrine()
                        ->getRepository('AppBundle:UserAControler')
                        ->find($d['id']);
            $state = ($d['state'] == 'false') ? 0 : 1;
            $uac->setAControler($state);
            $uac->setDate(new \DateTime());
            $uac->setOperateurCocher($user);
        }
        $em->flush();
        return new JsonResponse('ok');
    }
}
