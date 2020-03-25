<?php

namespace TenueBundle\Controller;

use AppBundle\Entity\Categorie;
use AppBundle\Entity\Logs;
use AppBundle\Entity\Lot;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TenueImputationController extends Controller
{
    /**
     * Pilotage imputation
     *
     * @return Response
     */
    public function pilotageAction()
    {
        return $this->render('TenueBundle:Tenue:index.html.twig');
    }

    /**
     * Affectation saisie Index
     *
     * @param $json
     * @return Response|JsonResponse
     * @throws \Exception
     */
    public function affectationAction($json)
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

//        $image_repo = $this->getDoctrine()
//            ->getRepository('AppBundle:Image');
//        $nb_image_IMP = $image_repo
//            ->imageImputation($nb_lot_IMP);
//        $nb_image_CTRL = $image_repo
//            ->imageCtrlImputation($nb_lot_CTRL);

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');

        $lot_IMP = $lot_repo
            ->lotImputation();

        $lot_IMP_encours = $lot_repo
            ->lotImputation(true);

        $lot_CTRL = $lot_repo
            ->lotCtrlImputation();

        $lot_CTRL_encours = $lot_repo
            ->lotCtrlImputation(true);

        $etape_IMP = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'IMP',
            ));
        $etape_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'CTRL_IMP',
            ));

        $operateurs_IMP = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_IMP);
        $operateurs_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurEtape($etape_CTRL);

        $panier_IMP = $panier_repo
            ->getPanierImputation();
        $panier_CTRL = $panier_repo
            ->getPanierCtrlImputation();

        $liste_client_IMP = array();
        $liste_client_IMP_encours = array();
        $liste_dossier_IMP = array();
        $liste_dossier_IMP_encours = array();
        $liste_client_CTRL = array();
        $liste_client_CTRL_encours = array();
        $liste_dossier_CTRL = array();
        $liste_dossier_CTRL_encours = array();

        foreach ($lot_IMP as $lot) {
            if (!in_array($lot->client, $liste_client_IMP)) {
                $liste_client_IMP[] = $lot->client;
                $liste_dossier_IMP[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_IMP[$lot->client])) {
                    $liste_dossier_IMP[$lot->client][] = $lot->dossier;
                }
            }
        }

        foreach ($lot_IMP_encours as $lot) {
            if (!in_array($lot->client, $liste_client_IMP_encours)) {
                $liste_client_IMP_encours[] = $lot->client;
                $liste_dossier_IMP_encours[$lot->client][] = $lot->dossier;
            } else {
                if (!in_array($lot->dossier, $liste_dossier_IMP_encours[$lot->client])) {
                    $liste_dossier_IMP_encours[$lot->client][] = $lot->dossier;
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

        ksort($liste_dossier_IMP);
        ksort($liste_dossier_IMP_encours);
        ksort($liste_dossier_CTRL);
        ksort($liste_dossier_CTRL_encours);

        foreach ($liste_dossier_IMP as $key => &$value) {
            sort($value);
        }
        foreach ($liste_dossier_IMP_encours as $key => &$value) {
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
            return $this->render('@Tenue/Tenue/affectation-imputation.html.twig', array(
//                'nb_lot_IMP' => $nb_lot_IMP,
//                'nb_image_IMP' => $nb_image_IMP,
//                'nb_lot_CTRL' => $nb_lot_CTRL,
//                'nb_image_CTRL' => $nb_image_CTRL,
                'liste_jour' => $liste_jour,
                'operateurs_IMP' => $operateurs_IMP,
                'operateurs_CTRL' => $operateurs_CTRL,
                'panier_IMP' => $panier_IMP,
                'panier_CTRL' => $panier_CTRL,
                'lot_IMP' => $lot_IMP,
                'lot_IMP_encours' => $lot_IMP_encours,
                'lot_CTRL' => $lot_CTRL,
                'lot_CTRL_encours' => $lot_CTRL_encours,
                'liste_client_IMP' => $liste_client_IMP,
                'liste_client_IMP_encours' => $liste_client_IMP_encours,
                'liste_dossier_IMP' => $liste_dossier_IMP,
                'liste_dossier_IMP_encours' => $liste_dossier_IMP_encours,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_client_CTRL_encours' => $liste_client_CTRL_encours,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'liste_dossier_CTRL_encours' => $liste_dossier_CTRL_encours,
                'mois' => $mois,
            ));
        } else {


            $data = array(
                'erreur' => false,
//                'nb_lot_IMP' => $nb_lot_IMP,
//                'nb_image_IMP' => $nb_image_IMP,
//                'nb_lot_CTRL' => $nb_lot_CTRL,
//                'nb_image_CTRL' => $nb_image_CTRL,
                'liste_jour' => $liste_jour,
                'operateurs_IMP' => $operateurs_IMP,
                'operateurs_CTRL' => $operateurs_CTRL,
                'panier_IMP' => $panier_IMP,
                'panier_CTRL' => $panier_CTRL,
                'lot_IMP' => $lot_IMP,
                'lot_IMP_encours' => $lot_IMP_encours,
                'lot_CTRL' => $lot_CTRL,
                'lot_CTRL_encours' => $lot_CTRL_encours,
                'liste_client_IMP' => $liste_client_IMP,
                'liste_client_IMP_encours' => $liste_client_IMP_encours,
                'liste_dossier_IMP' => $liste_dossier_IMP,
                'liste_dossier_IMP_encours' => $liste_dossier_IMP_encours,
                'liste_client_CTRL' => $liste_client_CTRL,
                'liste_client_CTRL_encours' => $liste_client_CTRL_encours,
                'liste_dossier_CTRL' => $liste_dossier_CTRL,
                'liste_dossier_CTRL_encours' => $liste_dossier_CTRL_encours
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
                ->setRemarque('PARTAGE LOT ' . $etape->getLibelle() . '(' . $operateur->getId() .')')
                ->setLot($lot)
                ->setImage($panier->getImage())
                ->setEtapeTraitement($etape);

            $image_a_traiter = $this->getDoctrine()
                ->getRepository('AppBundle:ImageATraiter')
                ->findBy(array(
                    'image' => $image
                ));
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

    /**
     * Panier Imputation
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

        //TESTER USER AUTHORIZATION
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_IMPUTATION')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette page");
        }

        $current_date = new \DateTime();
        $panier_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Panier');
        $etape_IMP = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'IMP'
            ));
        $etape_CTRL = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->findOneBy(array(
                'code' => 'CTRL_IMP'
            ));
        $nbimage_IMP = 0;
        $nbimage_CTRL = 0;
        if ($etape_IMP) {
            $nbimage_IMP = $panier_repo
                ->getNbImagePanier($this->getUser(), $etape_IMP);
        }
        if ($etape_CTRL) {
            $nbimage_CTRL = $panier_repo
                ->getNbImagePanier($this->getUser(), $etape_CTRL);
        }
        $panier_IMP = $panier_repo
            ->getPanierImputationPerUser($this->getUser());
        if ($json == 0) {
            return $this->render('TenueBundle:Tenue:panier-imputation.html.twig', [
                'current_date' => $current_date,
                'panier_IMP' => $panier_IMP,
                'nbimage_IMP' => $nbimage_IMP,
                'nbimage_CTRL' => $nbimage_CTRL,
            ]);
        } else {
            $data = [
                'current_date' => $current_date,
                'panier_IMP' => $panier_IMP,
                'nbimage_IMP' => $nbimage_IMP,
                'nbimage_CTRL' => $nbimage_CTRL,
            ];

            return new JsonResponse($data);
        }
    }
}
