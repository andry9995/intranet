<?php

namespace RevisionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RevisionController extends Controller
{
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
        $image_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Image');
        $nb_image_niv1 = $image_repo
            ->imageTraitementN1($nb_lot_niv1);
        $nb_image_niv2 = $image_repo
            ->imageTraitementN2($nb_lot_niv2);

        $lot_repo = $this->getDoctrine()
            ->getRepository('AppBundle:Lot');

        $lot_N1 = $lot_repo
            ->lotN1();
        $lot_N2 = $lot_repo
            ->lotN2();

        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getOperateurCellule('image');

        $panier_reception_repo = $this->getDoctrine()
            ->getRepository('AppBundle:PanierReception');

        $panier_niv1 = $panier_reception_repo
            ->getPanierNiv1();
        $panier_niv2 = $panier_reception_repo
            ->getPanierNiv2();

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
            return $this->render('@Revision/Revision/affectation.html.twig', array(
                'nb_lot_niv1' => $nb_lot_niv1,
                'nb_image_niv1' => $nb_image_niv1,
                'nb_lot_niv2' => $nb_lot_niv2,
                'nb_image_niv2' => $nb_image_niv2,
                'liste_jour' => $liste_jour,
                'operateurs' => $operateurs,
                'panier_niv1' => $panier_niv1,
                'panier_niv2' => $panier_niv2,
                'lot_N1' => $lot_N1,
                'lot_N2' => $lot_N2,
                'liste_client_N1' => $liste_client_N1,
                'liste_dossier_N1' => $liste_dossier_N1,
                'liste_client_N2' => $liste_client_N2,
                'liste_dossier_N2' => $liste_dossier_N2,
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
                'nb_lot_niv1' => $nb_lot_niv1,
                'nb_image_niv1' => $nb_image_niv1,
                'nb_lot_niv2' => $nb_lot_niv2,
                'nb_image_niv2' => $nb_image_niv2,
                'liste_jour' => $liste_jour,
                'operateurs' => json_decode(json_encode($operateurs), TRUE),
                'panier_niv1' => $panier_niv1,
                'panier_niv2' => $panier_niv2,
                'lot_N1' => json_decode(json_encode($lot_N1), TRUE),
                'lot_N2' => json_decode(json_encode($lot_N2), TRUE),
                'liste_client_N1' => $liste_client_N1,
                'liste_dossier_N1' => $liste_dossier_N1,
                'liste_client_N2' => $liste_client_N2,
                'liste_dossier_N2' => $liste_dossier_N2,
            );

            return new JsonResponse($data);

        }
    }
}
