<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Tache;
use AppBundle\Entity\TacheClient;
use AppBundle\Entity\TacheDomaine;
use AppBundle\Entity\TacheDossier;
use AppBundle\Entity\TachePrecedente;
use AppBundle\Entity\TacheSuivante;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TacheLibreController extends Controller {

    /**
     * Index tache - Charement parametre et tache libre
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction() {
        $domaines = $this->getDoctrine()
            ->getRepository('AppBundle:TacheDomaine')
            ->getAllTacheDomaine();
        $taches = $this->getDoctrine()
            ->getRepository('AppBundle:Tache')
            ->getAllTache();
        $regimes_fiscaux = $this->getDoctrine()
            ->getRepository('AppBundle:RegimeFiscal')
            ->getAllForTacheLegale();
        $forme_activites = $this->getDoctrine()
            ->getRepository('AppBundle:FormeActivite')
            ->getAll();
        $forme_juridiques = $this->getDoctrine()
            ->getRepository('AppBundle:FormeJuridique')
            ->getAll();

        $regimesFiscals = $this->getDoctrine()
            ->getRepository('AppBundle:RegimeFiscal')
            ->getForAllTache();

        $tachesGroups = $this->getDoctrine()->getRepository('AppBundle:TachesGroup')
            ->getListe();

        return $this->render('TacheBundle:Tache:index.html.twig', [
            'domaines' => $domaines,
            'taches' => $taches,
            'regimes_fiscaux' => $regimes_fiscaux,
            'forme_activites' => $forme_activites,
            'forme_juridiques' => $forme_juridiques,
            'regimesFiscals' => $regimesFiscals,
            'tachesGroups' => $tachesGroups
        ]);
    }

    /**
     * Supprimer Tache Libre
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \AppBundle\Entity\Tache $tache
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeAction(Request $request, Tache $tache) {
        if ($request->isXmlHttpRequest()) {
            try {
                $em = $entite = $this->getDoctrine()
                    ->getManager();
                $em->remove($tache);
                $em->flush();

                $data = [
                    'erreur' => FALSE,
                ];
                return new JsonResponse(json_encode($data));
            } catch (Exception $ex) {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => $ex->getMessage(),
                ];
                return new JsonResponse(json_encode($data));
            }
        }
        throw new AccessDeniedHttpException("Accès refusé.");
    }

    /**
     * Ajout tache Libre
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addAction(Request $request) {
        $id_domaine = $request->request->get('domaine');
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');
        $jalon = ($request->request->get('jalon') == '1' ? TRUE : FALSE);

        try {
            $em = $this->getDoctrine()
                ->getManager();
            $domaine = $this->getDoctrine()
                ->getRepository('AppBundle:TacheDomaine')
                ->find($id_domaine);
            if ($domaine) {
                // Ajout nouvelle tâche
                $tache = new Tache();
                $tache->setTacheDomaine($domaine);
                $tache->setNom($nom);
                $tache->setDescription($description);
                $tache->setJalon($jalon);

                $em->persist($tache);
                $em->flush();

                $data = ['erreur' => FALSE];
                return new JsonResponse(json_encode($data));
            } else {
                $erreur_text = "Sélectionner un domaine dans la liste !";
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => $erreur_text,
                ];
                return new JsonResponse(json_encode($data));
            }
        } catch (\Exception $ex) {
            $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
            if ($pos == FALSE) {
                $erreur_text = "Il y a une erreur !";
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

    /**
     * Modification d'une tâche Libre
     *
     * @param Request $request
     *
     * @param \AppBundle\Entity\Tache $tache
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request, Tache $tache) {
        if ($request->isXmlHttpRequest()) {
            $id_domaine = $request->request->get('domaine');
            $nom = $request->request->get('nom');
            $description = $request->request->get('description');
            $jalon = $request->request->get('jalon') == 1 ? TRUE : FALSE;

            try {
                $em = $this->getDoctrine()
                    ->getManager();

                if ($id_domaine != '') {
                    $domaine = $em->getRepository('AppBundle:TacheDomaine')
                        ->find($id_domaine);
                    if ($domaine) {
                        $tache
                            ->setNom($nom)
                            ->setDescription($description)
                            ->setJalon($jalon)
                            ->setTacheDomaine($domaine);
                        $em->persist($tache);
                        $em->flush();

                        $data = ['erreur' => FALSE];
                        return new JsonResponse(json_encode($data));
                    } else {
                        $data = [
                            'erreur' => TRUE,
                            'erreur_text' => "Séléctionner un domaine dans la liste",
                        ];
                        return new JsonResponse(json_encode($data));
                    }
                } else {
                    $data = [
                        'erreur' => TRUE,
                        'erreur_text' => "Séléctionner un domaine dans la liste",
                    ];
                    return new JsonResponse(json_encode($data));
                }
            } catch (\Exception $ex) {
                $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
                if ($pos == FALSE) {
                    $erreur_text = $ex->getMessage();
                } else {
                    $erreur_text = "Le nom de la tache existe déjà !";
                }

                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => $erreur_text,
                    'erreur_details' => $ex->getMessage(),
                ];
                return new JsonResponse(json_encode($data));
            }
        }
        throw new AccessDeniedHttpException('Accès refusé');
    }

    /**
     * Listes tache pour JqGrid
     *
     * @return JsonResponse
     */
    public function listeAction() {
        $taches = $this->getDoctrine()
            ->getRepository('AppBundle:Tache')
            ->getAllTache();

        $rows = [];
        /** @var Tache $tache */
        foreach ($taches as $tache) {
            $domaine_id = "";
            $domaine = "";
            if ($tache->getTacheDomaine()) {
                $domaine_id = $tache->getTacheDomaine()->getId();
                $domaine = $tache->getTacheDomaine()->getDomaine();
            }
            $rows[] = [
                'id' => $tache->getId(),
                'cell' => [
                    $domaine_id,
                    $domaine,
                    $tache->getNom(),
                    $tache->getDescription(),
                    $tache->getJalon(),
                    $tache->getJalon(),
                    '<i class="fa fa-edit js-edit-tache-prec-suiv"></i>',
                    '<i class="fa fa-edit js-edit-tache-prec-suiv"></i>',
                    '<i class="fa fa-save icon-action js-save-button js-save-tache" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-tache" title="Supprimer"></i>',
                ],
            ];
        }
        $liste = [
            'rows' => $rows,
        ];
        return new JsonResponse($liste);
    }

    /**
     * Liste simple pour HTML select ou JSON
     *
     * @param $json
     *
     * @return JsonResponse|Response
     */
    public function listeSimpleAction($json) {
        $taches = $this->getDoctrine()
            ->getRepository('AppBundle:Tache')
            ->getAllTache();
        if ($json == 1) {
            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });

            $serializer = new Serializer(array($normalizer), array($encoder));
            return new JsonResponse($serializer->serialize($taches, 'json'));
        } else {
            return $this->render('@Tache/Tache/liste.html.twig', [
                'taches' => $taches,
            ]);
        }
    }

    /**
     * Liste domaine Tache
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function domaineAction(Request $request) {
        if ($request->isXmlHttpRequest()) {
            $domaines = $this->getDoctrine()
                ->getRepository('AppBundle:TacheDomaine')
                ->getAllTacheDomaine();

            $rows = [];
            /** @var TacheDomaine $domaine */
            foreach ($domaines as $domaine) {
                $rows[] = [
                    'id' => $domaine->getId(),
                    'cell' => [
                        $domaine->getDomaine(),
                        '<i class="fa fa-save icon-action js-save-button js-save-domaine" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-domaine" title="Supprimer"></i>',
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
     * Modifier Domaine Tache
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function domaineEditAction(Request $request) {
        if ($request->isXmlHttpRequest()) {

            $id = $request->request->get("id", "");
            $nom = $request->request->get("domaine-nom");
            $em = $this->getDoctrine()
                ->getManager();

            if ($id != "") {
                try {
                    if ($id != "new_row") {
                        $domaine = $this->getDoctrine()
                            ->getRepository('AppBundle:TacheDomaine')
                            ->find($id);
                        if ($domaine) {
                            $domaine->setDomaine($nom);
                        }
                    } else {
                        $domaine = new TacheDomaine();
                        $domaine->setDomaine($nom);
                        $em->persist($domaine);
                    }
                    $em->flush();
                    $data = [
                        'erreur' => FALSE,
                    ];
                    return new JsonResponse(json_encode($data));
                } catch (\Exception $ex) {
                    if (strpos($ex->getMessage(), "domaine_UNIQUE")) {
                        return new Response("Le domaine '$nom' existe déjà", 500);
                    }
                }
            }
            throw new NotFoundHttpException("Domaine introuvable.");

        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    /**
     * Supprimer Domaine Tache
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function domaineRemoveAction(Request $request) {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get("id", "");
            $em = $this->getDoctrine()
                ->getManager();
            if ($id != "") {
                if ($id != "new_row") {
                    $domaine = $this->getDoctrine()
                        ->getRepository('AppBundle:TacheDomaine')
                        ->find($id);
                    if ($domaine) {
                        $em->remove($domaine);
                    }
                }
                $em->flush();
                $data = [
                    'erreur' => FALSE,
                ];
                return new JsonResponse(json_encode($data));
            }
            throw new NotFoundHttpException("Domaine introuvable.");
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    /**
     * Liste des domaines des taches en HTML select ou JSON
     *
     * @param $json
     *
     * @return JsonResponse|Response
     */
    public function domaineListeAction($json) {
        $domaines = $this->getDoctrine()
            ->getRepository('AppBundle:TacheDomaine')
            ->getAllTacheDomaine();

        if ($json == 1) {
            return new JsonResponse($domaines);
        } else {
            return $this->render('@Tache/Tache/domaine.html.twig', [
                'domaines' => $domaines,
            ]);
        }
    }

    /**
     * Ajouter une tache précédente
     *
     *
     * @param Tache $principale
     * @param Tache $precedente
     *
     * @return JsonResponse
     */
    public function addTachePrecedenteAction(Tache $principale, Tache $precedente) {
        try {
            $em = $this->getDoctrine()
                ->getManager();

            $tache_precedente = new TachePrecedente();
            $tache_precedente->setTachePrincipale($principale);
            $tache_precedente->setTachePrecedente($precedente);

            $em->persist($tache_precedente);
            $em->flush();

            $data = [
                'erreur' => FALSE,
                'id' => $tache_precedente->getId(),
            ];

            return new JsonResponse(json_encode($data));

        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(), "unique_tache_principale_precedent")) {
                $erreur_text = "La tache précédente existe déjà !";
            } else {
                $erreur_text = "Une erreur est survenue !";
            }
            $data = [
                'erreur' => TRUE,
                'erreur_text' => $erreur_text,
            ];

            return new JsonResponse(json_encode($data));
        }

    }

    /**
     * Ajouter une tache suivante
     *
     *
     * @param Tache $principale
     * @param Tache $suivante
     *
     * @return JsonResponse
     */
    public function addTacheSuivanteAction(Tache $principale, Tache $suivante) {
        try {
            $em = $this->getDoctrine()
                ->getManager();

            $tache_suivante = new TacheSuivante();
            $tache_suivante->setTachePrincipale($principale);
            $tache_suivante->setTacheSuivante($suivante);

            $em->persist($tache_suivante);
            $em->flush();

            $data = [
                'erreur' => FALSE,
                'id' => $tache_suivante->getId(),
            ];

            return new JsonResponse(json_encode($data));

        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(), "unique_tache_principale_suivant")) {
                $erreur_text = "La tache suivante existe déjà !";
            } else {
                $erreur_text = "Une erreur est survenue !";
            }
            $data = [
                'erreur' => TRUE,
                'erreur_text' => $erreur_text,
            ];

            return new JsonResponse(json_encode($data));
        }

    }

    /**
     * Liste Tache Précédente/Tache Suivante
     *
     * @param \AppBundle\Entity\Tache $principale
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listePrecSuivAction(Tache $principale) {
        $tache_prec = $this->getDoctrine()
            ->getRepository('AppBundle:TachePrecedente')
            ->getAllTachePrecedente($principale, TRUE);
        $tache_suiv = $this->getDoctrine()
            ->getRepository('AppBundle:TacheSuivante')
            ->getAllTacheSuivante($principale, TRUE);

        $data = [
            'tache_prec' => $tache_prec,
            'tache_suiv' => $tache_suiv,
        ];

        return new JsonResponse(json_encode($data));
    }


    /**
     * Suppression tache précédente|Suivante
     *
     * @param Request $request
     * @param $id
     * @param $type
     *
     * @return JsonResponse
     */
    public function removePrecSuivAction(Request $request, $id, $type) {
        try {
            if ($request->isXmlHttpRequest()) {
                $em = $this->getDoctrine()
                    ->getManager();
                if ($type == 0) {
                    //Supprimer tache précédente
                    $tache_prec = $this->getDoctrine()
                        ->getRepository('AppBundle:TachePrecedente')
                        ->find($id);
                    if ($tache_prec) {
                        $em->remove($tache_prec);
                    }
                } elseif ($type == 1) {
                    //Supprimer tache suivante
                    $tache_suiv = $this->getDoctrine()
                        ->getRepository('AppBundle:TacheSuivante')
                        ->find($id);
                    if ($tache_suiv) {
                        $em->remove($tache_suiv);
                    }
                }
                $em->flush();
                $data = [
                    'erreur' => FALSE,
                ];

                return new JsonResponse(json_encode($data));
            } else {
                throw new AccessDeniedHttpException('Accès refusé !');
            }
        } catch (\Exception $ex) {
            $data = [
                'erreur' => TRUE,
                'erreur_text' => 'Erreur lors de la suppression',
            ];

            return new JsonResponse(json_encode($data));
        }
    }

    /**
     * Mettre en order Tache Précédente/Tache Suivante
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $type
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function reorderPrecSuivAction(Request $request, $type) {
        if ($request->isXmlHttpRequest()) {
            try {
                $taches = $request->request->get('taches');
                $em = $this->getDoctrine()->getManager();

                // Taches précédentes
                if ($type == 0) {
                    $order = 1;
                    foreach ($taches as $tache_id) {
                        $tache = $this->getDoctrine()
                            ->getRepository('AppBundle:TachePrecedente')
                            ->find($tache_id);
                        if ($tache) {
                            $tache->setOrdre($order);
                            $order++;
                        }
                    }
                    $em->flush();
                } else {
                    $order = 1;
                    foreach ($taches as $tache_id) {
                        $tache = $this->getDoctrine()
                            ->getRepository('AppBundle:TacheSuivante')
                            ->find($tache_id);
                        if ($tache) {
                            $tache->setOrdre($order);
                            $order++;
                        }
                    }
                    $em->flush();
                }
                $data = [
                    'erreur' => FALSE,
                ];

                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => $ex->getMessage(),
                ];

                return new JsonResponse(json_encode($data));
            }
        } else {
            throw new AccessDeniedHttpException('Accès refusé.');
        }
    }

    public function getOneAction(Tache $tache) {
        $serializer = $this->get('serializer');
        $data = $serializer->serialize($tache, 'json');
        return new JsonResponse($data);
    }
}
