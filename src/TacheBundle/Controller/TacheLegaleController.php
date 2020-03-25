<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\TacheLegale;
use AppBundle\Entity\TacheListeAction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TacheLegaleController extends Controller
{

    public function listeAction()
    {
        $taches = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:TacheLegale')
            ->getAllTache();

        /** @var \AppBundle\Entity\FormeJuridique[] $forme_juridiques */
        $forme_juridiques = [];
        $juridiques = $this->getDoctrine()
            ->getRepository('AppBundle:FormeJuridique')
            ->getAll();
        /** @var \AppBundle\Entity\FormeJuridique $juridique */
        foreach ($juridiques as $juridique) {
            $forme_juridiques[$juridique->getId()] = $juridique;
        }

        /** @var \AppBundle\Entity\FormeActivite[] $forme_activites */
        $forme_activites = [];
        $activites = $this->getDoctrine()
            ->getRepository('AppBundle:FormeActivite')
            ->getAll();
        /** @var \AppBundle\Entity\FormeActivite $activite */
        foreach ($activites as $activite) {
            $forme_activites[$activite->getId()] = $activite;
        }

        /** @var \AppBundle\Entity\RegimeFiscal[] $regime_fiscaux */
        $regime_fiscaux = [];
        $regimes = $this->getDoctrine()
            ->getRepository('AppBundle:RegimeFiscal')
            ->getAll();
        /** @var \AppBundle\Entity\RegimeFiscal $regime */
        foreach ($regimes as $regime) {
            $regime_fiscaux[$regime->getId()] = $regime;
        }

        $periodes = [
            12 => 'mois',
            4 => 'trimestre',
            3 => 'quadrimestre',
            2 => 'semestre',
            1 => 'année',
        ];
        $rows = [];
        /** @var TacheLegale $tache */
        foreach ($taches as $tache) {
            $regime = [];
            if ($tache->getRegimeFiscal()) {
                foreach ($tache->getRegimeFiscal() as $rf) {
                    if (isset($regime_fiscaux[$rf])) {
                        $regime[] = $regime_fiscaux[$rf]->getLibelle();
                    }
                }
            }
            $activite = [];
            if ($tache->getFormeActivite()) {
                foreach ($tache->getFormeActivite() as $fa) {
                    if (isset($forme_activites[$fa])) {
                        $activite[] = $forme_activites[$fa]->getLibelle();
                    }
                }
            }
            $juridique = [];
            if ($tache->getFormeJuridique()) {
                foreach ($tache->getFormeJuridique() as $fj) {
                    if (isset($forme_juridiques[$fj])) {
                        $juridique[] = $forme_juridiques[$fj]->getLibelle();
                    }
                }
            }
            $periode = "";
            if ($tache->getPeriodeDeclaration() && $tache->getPeriodeDeclaration() != '') {
                if (isset($periodes[$tache->getPeriodeDeclaration()])) {
                    $periode = $periodes[$tache->getPeriodeDeclaration()];
                }
            }
            $rows[] = [
                'id' => $tache->getId(),
                'cell' => [
                    $tache->getNom(),
                    implode(",", $regime),
                    implode(",", $activite),
                    implode(",", $juridique),
                    $tache->getDateCloture(),
                    $tache->getEvenementDeclencheur(),
                    $periode,
                    '<i class="fa fa-navicon js-action-a-faire" title="Modifier les actions à faire"></i>',
                    '<i class="fa fa-edit icon-action js-edit-tache-legale" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-tache-legale" title="Supprimer"></i>',
                ],
            ];
        }
        $liste = [
            'rows' => $rows,
        ];
        return new JsonResponse($liste);

    }

    public function listeActionsAction()
    {
        $actions = $this->getDoctrine()
            ->getRepository('AppBundle:TacheListeAction')
            ->getAllAction();
        return $this->render('@Tache/Tache/liste-action.html.twig', array(
            'actions' => $actions
        ));
        /*return new Response(
            '<select>' .
                '<option value="Déclaration">Déclaration</option>'.
                '<option value="Paiement Accompte 1">Paiement Accompte 1</option>'.
                '<option value="Paiement Accompte 2">Paiement Accompte 2</option>'.
                '<option value="Paiement Accompte 3">Paiement Accompte 3</option>'.
                '<option value="Paiement Accompte 4">Paiement Accompte 4</option>'.
                '<option value="Solde à payer">Solde à payer</option>'.
            '</select>'
        );*/
    }

    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()
            ->getManager();
        $nom = trim($request->request->get('nom', ''));
        $regime = $request->request->get('regime', '') != '' ? $request->request->get('regime') : NULL;
        $activite = $request->request->get('activite', '') != '' ? $request->request->get('activite') : NULL;
        $entreprise = $request->request->get('entreprise', '') != '' ? $request->request->get('entreprise') : NULL;
        $cloture = $request->request->get('cloture', '') != '' ? $request->request->get('cloture') : NULL;
        $evenement = $request->request->get('evenement');
        $periode = $request->request->get('periode');

        try {
            if ($nom != '') {
                $tache = new TacheLegale();
                if ($regime) sort($regime);
                if ($activite) sort($activite);
                if ($entreprise) sort($entreprise);
                if ($cloture) sort($cloture);

                $tache
                    ->setNom($nom)
                    ->setRegimeFiscal($regime)
                    ->setFormeActivite($activite)
                    ->setFormeJuridique($entreprise)
                    ->setDateCloture($cloture)
                    ->setEvenementDeclencheur($evenement)
                    ->setPeriodeDeclaration($periode);
                $em->persist($tache);
                $em->flush();

                $data = [
                    'erreur' => FALSE,
                ];
                return new JsonResponse(json_encode($data));
            } else {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => "Le nom de la tâche ne doit pas être vide.",
                ];
                return new JsonResponse(json_encode($data));
            }
        } catch (\Exception $ex) {
            $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
            if ($pos == FALSE) {
                $erreur_text = "Une erreur est survenue !";
            } else {
                $erreur_text = "Le nom de la tache existe déjà !";
            }

            $data = [
                'erreur' => TRUE,
                'erreur_text' => $erreur_text,
            ];
            return new JsonResponse(json_encode($data));
        }
    }

    public function removeAction(TacheLegale $tache)
    {
        try {
            $em = $this->getDoctrine()
                ->getManager();
            $em->remove($tache);
            $em->flush();
            $data = [
                'erreur' => FALSE,
            ];
            return new JsonResponse(json_encode($data));
        } catch (\Exception $ex) {
            $data = [
                'erreur' => TRUE,
                'erreur_text' => "Une erreur est survenue !",
            ];
            return new JsonResponse(json_encode($data));
        }
    }

    public function editAction(Request $request, TacheLegale $tache)
    {
        $em = $this->getDoctrine()
            ->getManager();
        $nom = trim($request->request->get('nom', ''));
        $regime = $request->request->get('regime', '') != '' ? $request->request->get('regime') : NULL;
        $activite = $request->request->get('activite', '') != '' ? $request->request->get('activite') : NULL;
        $entreprise = $request->request->get('entreprise', '') != '' ? $request->request->get('entreprise') : NULL;
        $cloture = $request->request->get('cloture', '') != '' ? $request->request->get('cloture') : NULL;
        $evenement = $request->request->get('evenement');
        $periode = $request->request->get('periode');

        try {
            if ($nom != '') {
                if ($regime) sort($regime);
                if ($activite) sort($activite);
                if ($entreprise) sort($entreprise);
                if ($cloture) sort($cloture);

                $tache
                    ->setNom($nom)
                    ->setRegimeFiscal($regime)
                    ->setFormeActivite($activite)
                    ->setFormeJuridique($entreprise)
                    ->setDateCloture($cloture)
                    ->setEvenementDeclencheur($evenement)
                    ->setPeriodeDeclaration($periode);
                $em->persist($tache);
                $em->flush();

                $data = [
                    'erreur' => FALSE,
                ];
                return new JsonResponse(json_encode($data));
            } else {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => "Le nom de la tâche ne doit pas être vide.",
                ];
                return new JsonResponse(json_encode($data));
            }
        } catch (\Exception $ex) {
            $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
            if ($pos == FALSE) {
                $erreur_text = "Une erreur est survenue !";
            } else {
                $erreur_text = "Le nom de la tache existe déjà !";
            }

            $data = [
                'erreur' => TRUE,
                'erreur_text' => $erreur_text,
            ];
            return new JsonResponse(json_encode($data));
        }
    }

    public function oneAction(TacheLegale $tache)
    {
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer([$normalizer], [$encoder]);
        return new JsonResponse($serializer->serialize($tache, 'json'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function TacheListesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $tacheListeActions = $this->getDoctrine()
                ->getRepository('AppBundle:TacheListeAction')
                ->getAllAction();

            $rows = [];
            /** @var TacheListeAction $tacheListeAction */
            foreach ($tacheListeActions as $tacheListeAction) {
                $rows[] = [
                    'id' => $tacheListeAction->getId(),
                    'cell' => [
                        $tacheListeAction->getNom(),
                        $tacheListeAction->getCode(),
                        '<i class="fa fa-save icon-action js-save-button js-save-liste-action" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-liste-action" title="Supprimer"></i>',
                    ],
                ];
            }
            $liste = [
                'rows' => $rows,
            ];
            return new JsonResponse($liste);
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    /**
     * Modifier Tache Liste Action
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function tacheListeEditAction(Request $request) {
        if ($request->isXmlHttpRequest()) {

            $id = $request->request->get("id", "");
            $nom = $request->request->get("liste-action-nom");
            $code = trim(strtoupper($request->request->get('liste-action-code')));
            $em = $this->getDoctrine()
                ->getManager();

            if ($id != "") {
                try {
                    if ($id != "new_row") {
                        /** @var TacheListeAction $tacheListeAction */
                        $tacheListeAction = $this->getDoctrine()
                            ->getRepository('AppBundle:TacheListeAction')
                            ->find($id);
                        if ($tacheListeAction) {
                            $tacheListeAction
                                ->setNom($nom)
                                ->setCode($code);
                        }
                    } else {
                        $tacheListeAction = new TacheListeAction();
                        $tacheListeAction
                            ->setNom($nom)
                            ->setCode($code);
                        $em->persist($tacheListeAction);
                    }
                    $em->flush();
                    $data = [
                        'erreur' => FALSE,
                    ];
                    return new JsonResponse(json_encode($data));
                } catch (\Exception $ex) {
                    if (strpos($ex->getMessage(), "nom_UNIQUE")) {
                        return new Response("L' action '$nom' existe déjà", 500);
                    }
                }
            }
            throw new NotFoundHttpException("Action introuvable.");

        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    /**
     * Supprimer Tache Liste Action
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function tacheListeRemoveAction(Request $request) {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get("id", "");
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != "") {
                if ($id != "new_row") {
                    $tacheListeAction = $this->getDoctrine()
                        ->getRepository('AppBundle:TacheListeAction')
                        ->find($id);
                    if ($tacheListeAction) {
                        $em->remove($tacheListeAction);
                    }
                }
                $em->flush();
                $data = [
                    'erreur' => FALSE,
                ];
                return new JsonResponse(json_encode($data));
            }
            throw new NotFoundHttpException("Liste introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }
}
