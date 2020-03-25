<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheClient;
use AppBundle\Entity\TacheDossier;
use AppBundle\Entity\TacheLegaleParam;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TacheDossierController extends Controller
{

    public function tacheParDossierAction()
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();
        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getAllOperateurByPrenom();
        return $this->render('@Tache/Tache/par-dossier.html.twig', [
            'clients' => $clients,
            'operateurs' => $operateurs,
        ]);
    }

    public function utilisateurParClientAction(Request $request, Client $client)
    {
        try {
            if ($request->isXmlHttpRequest()) {
                $utilisateurs = $this->getDoctrine()
                    ->getRepository('AppBundle:Tache')
                    ->getUtilisateurParClient($client, TRUE);
                $data = [
                    'erreur' => FALSE,
                    'utilisateurs' => $utilisateurs,
                ];
                return new JsonResponse(json_encode($data));
            } else {
                throw new AccessDeniedHttpException("Vous n'avez pas accès à cettre ressource.");
            }
        } catch (\Exception $ex) {
            $data = [
                'erreur' => TRUE,
                'erreur_text' => $ex->getMessage(),
            ];
            return new JsonResponse(json_encode($data));
        }
    }


    /**
     * @param Request $request
     * @param Dossier $dossier
     * @return JsonResponse
     * @throws \Exception
     */
    public function listeTacheDossierAction(Request $request, Dossier $dossier)
    {
        if ($request->isXmlHttpRequest()) {
            $taches = $this->getDoctrine()
                ->getRepository('AppBundle:TacheDossier')
                ->listeTacheDossier($dossier);
            $tache_legales = $this->getDoctrine()
                ->getRepository('AppBundle:TacheLegale')
                ->getActions($dossier);

            $periodes = [
                1 => 'Annuel',
                2 => 'Semestriel',
                3 => 'Quadrimestriel',
                4 => 'Trimestriel',
                6 => 'Bimensuel',
                12 => 'Mensuel',
                0 => 'Ponctuel',
            ];

            $rows = [];

            /** @var TacheDossier $tache */
            foreach ($taches as $tache) {
                $responsable = "";
                $responsable_client = "";
                $responsable_scriptura = "";
                $entite = "";
                $periode = "";
                $demarrage = "";

                if ($tache->getResponsableScriptura() != NULL) {
                    $responsable = strtoupper($tache->getResponsableScriptura()
                        ->getPrenom());
                }
                if ($tache->getResponsableClient() != NULL) {
                    $responsable = strtoupper($tache->getResponsableClient()
                        ->getEmail());
                }
                if ($tache->getResponsableClient()) {
                    $responsable_client = $tache->getResponsableClient()
                        ->getId();
                }
                if ($tache->getResponsableScriptura()) {
                    $responsable_scriptura = $tache->getResponsableScriptura()
                        ->getId();
                }
                if ($tache->getEntite() != NULL) {
                    if ($tache->getEntite() == 1) {
                        $entite = "Scriptura";
                    } elseif ($tache->getEntite() == 2) {
                        $entite = "Client";
                    }
                }
                if ($tache->getPeriode() != "" && $tache->getPeriode() != NULL) {
                    $periode = $periodes[$tache->getPeriode()];
                }
                if ($tache->getDemarrage()) {
                    $demarrage = $tache->getDemarrage()->format('d/m/Y');
                }

                $rows[] = [
                    'id' => $tache->getId(),
                    'cell' => [
                        $tache->getTache()->getTacheDomaine()->getDomaine(),
                        $tache->getTache()->getId(),
                        $tache->getId(),
                        $tache->getTache()->getNom(),
                        $demarrage,
                        $tache->getPeriode(),
                        $periode,
                        $tache->getJalon(),
                        $tache->getLegale(),
                        $tache->getRealiserAvant(),
                        $tache->getPlusTard(),
                        '',
                        $tache->getEntite(),
                        $entite,
                        $responsable_scriptura,
                        $responsable_client,
                        $responsable,
                        implode(',', $tache->getDateList()),
                        $tache->getMoisPlus(),
                        '<i class="fa fa-edit icon-action js-edit-tache-dossier" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-tache-dossier" title="Supprimer"></i>',
                    ],
                ];
            }

            foreach ($tache_legales as $tache_legale) {
                $periode = "";
                $periode_number = "";
                if ($tache_legale['tache']->getPeriodeDeclaration() && isset($periodes[$tache_legale['tache']->getPeriodeDeclaration()])) {
                    $periode = $periodes[$tache_legale['tache']->getPeriodeDeclaration()];
                    $periode_number = $tache_legale['tache']->getPeriodeDeclaration();
                }

                $responsable = "";
                $responsable_client = "";
                $responsable_scriptura = "";
                $entite = "";
                $entite_id = "";
                $plus_tard = 0;
                $realiser_avant = 0;
                $demarrage = "";

                /** @var \AppBundle\Entity\TacheLegaleAction $action */
                $action = $tache_legale['action'];

                /** @var TacheLegaleParam[] $param */
                $params = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('AppBundle:TacheLegaleParam')
                    ->getByDossierAndAction($action, $dossier);
                if (count($params) > 0) {
                    /** @var TacheLegaleParam $param */
                    $param = $params[0];
                    if ($param->getOperateur() != NULL) {
                        $responsable = strtoupper($param->getOperateur()
                            ->getPrenom());
                    }
                    if ($param->getUtilisateur() != NULL) {
                        $responsable = strtoupper($param->getUtilisateur()
                            ->getEmail());
                    }

                    if ($param->getUtilisateur()) {
                        $responsable_client = $param->getUtilisateur()
                            ->getId();
                    }

                    if ($param->getOperateur()) {
                        $responsable_scriptura = $param->getOperateur()
                            ->getId();
                    }

                    if ($param->getEntite() != NULL) {
                        $entite_id = $param->getEntite();
                        if ($param->getEntite() == 1) {
                            $entite = "Scriptura";
                        } elseif ($param->getEntite() == 2) {
                            $entite = "Client";
                        }
                    }

                    if ($param->getPlusTard()) {
                        $plus_tard = $param->getPlusTard();
                    }
                    if ($param->getRealiserAvant()) {
                        $realiser_avant = $param->getRealiserAvant();
                    }

                    if ($param->getDemarrage()) {
                        $demarrage = $param->getDemarrage()->format('d-m-Y');
                    }
                }

                $date_prevue = "";
                if (isset($tache_legale['date'])) {
                    $date_prevue = $tache_legale['date']->format('d/m/Y');
                }

                $rows[] = [
                    'id' => "legale_" . $tache_legale['action']->getId(),
                    'cell' => [
                        'Légale',
                        '',
                        $tache_legale['action']->getId(),
                        $tache_legale['tache']->getNom() . ' - ' . $tache_legale['action']->getNom(),
                        $demarrage,
                        $periode_number,
                        $periode,
                        FALSE,
                        TRUE,
                        $realiser_avant,
                        $plus_tard,
                        $date_prevue,
                        $entite_id,
                        $entite,
                        $responsable_scriptura,
                        $responsable_client,
                        $responsable,
                        '',
                        '',
                        '<i class="fa fa-edit icon-action js-edit-tache-dossier" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-tache-dossier" title="Supprimer"></i>',
                    ],
                ];
            }
            $liste = [
                'rows' => $rows,
            ];
            return new JsonResponse($liste);
        }

        throw new AccessDeniedHttpException('Accès refusé');
    }

    public function saveTacheDossierAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $em = $this->getDoctrine()
                    ->getManager();

                $id = $request->request->get('id');
                $is_legale = $request->request->get('is_tache_legale', '') == 1;

                if ($is_legale) {
                    if (trim($id) != "") {
                        $action = $this->getDoctrine()
                            ->getRepository('AppBundle:TacheLegaleAction')
                            ->find($id);
                        if ($action) {
                            $plus_tard = $request->request->get('plus_tard');
                            $realiser_avant = $request->request->get('realiser_avant');
                            $entite = $request->request->get('entite');
                            $resp_scriptura_id = $request->request->get('resp_scriptura_id');
                            $resp_client_id = $request->request->get('resp_client_id');
                            $dossier_id = $request->request->get('dossier_id');
                            $demarrage = $request->request->get('demarrage', '');
                            $dossier = $this->getDoctrine()
                                ->getRepository('AppBundle:Dossier')
                                ->find($dossier_id);
                            if ($dossier) {
                                if ($resp_scriptura_id != "") {
                                    $resp_scriptura = $this->getDoctrine()
                                        ->getRepository('AppBundle:Operateur')
                                        ->find($resp_scriptura_id);
                                } else {
                                    $resp_scriptura = FALSE;
                                }
                                if ($resp_client_id != "") {
                                    $resp_client = $this->getDoctrine()
                                        ->getRepository('AppBundle:Utilisateur')
                                        ->find($resp_client_id);
                                } else {
                                    $resp_client = FALSE;
                                }
                                /** @var TacheLegaleParam[] $params */
                                $params = $this->getDoctrine()
                                    ->getRepository('AppBundle:TacheLegaleParam')
                                    ->getByDossierAndAction($action, $dossier);
                                if (count($params) > 0) {
                                    /** @var TacheLegaleParam $param */
                                    $param = $params[0];
                                } else {
                                    /** @var TacheLegaleParam $param */
                                    $param = new TacheLegaleParam();
                                }
                                $param
                                    ->setDossier($dossier)
                                    ->setTacheLegaleAction($action)
                                    ->setEntite(intval($entite))
                                    ->setPlusTard(intval($plus_tard))
                                    ->setRealiserAvant(intval($realiser_avant));
                                if ($demarrage && $demarrage != '') {
                                    $param->setDemarrage(\DateTime::createFromFormat('d/m/Y', $demarrage));
                                } else {
                                    $param->setDemarrage(NULL);
                                }
                                if ($resp_client) {
                                    $param->setUtilisateur($resp_client);
                                } else {
                                    $param->setUtilisateur(NULL);
                                }

                                if ($resp_scriptura) {
                                    $param->setOperateur($resp_scriptura);
                                } else {
                                    $param->setOperateur(NULL);
                                }
                                $em->persist($param);
                            }
                            $em->persist($action);
                            $em->flush();
                        }
                    }
                    $data = ['erreur' => FALSE];
                    return new JsonResponse(json_encode($data));
                } else {
                    if (trim($id) != "") {
                        $tache = $this->getDoctrine()
                            ->getRepository('AppBundle:TacheDossier')
                            ->find($id);
                    } else {
                        $tache = new TacheDossier();
                    }

                    $tache_id = $request->request->get('tache_id');
                    $periode = $request->request->get('periode');
                    $date_list = $request->request->get('date_list');
                    $mois_plus = $request->request->get('mois_plus');
                    $plus_tard = $request->request->get('plus_tard');
                    $realiser_avant = $request->request->get('realiser_avant');
                    $entite = $request->request->get('entite');
                    $resp_scriptura_id = $request->request->get('resp_scriptura_id');
                    $resp_client_id = $request->request->get('resp_client_id');
                    $jalon = $request->request->get('jalon');
                    $legale = $request->request->get('legale');
                    $demarrage = $request->request->get('demarrage', '');
                    $dossier_id = $request->request->get('dossier_id');

                    $tache_parent = $this->getDoctrine()
                        ->getRepository('AppBundle:Tache')
                        ->find($tache_id);
                    if ($resp_scriptura_id != "") {
                        $resp_scriptura = $this->getDoctrine()
                            ->getRepository('AppBundle:Operateur')
                            ->find($resp_scriptura_id);
                    } else {
                        $resp_scriptura = FALSE;
                    }
                    if ($resp_client_id != "") {
                        $resp_client = $this->getDoctrine()
                            ->getRepository('AppBundle:Utilisateur')
                            ->find($resp_client_id);
                    } else {
                        $resp_client = FALSE;
                    }
                    $dossier = $this->getDoctrine()
                        ->getRepository('AppBundle:Dossier')
                        ->find($dossier_id);

                    $tache
                        ->setDossier($dossier)
                        ->setTache($tache_parent)
                        ->setPeriode($periode)
                        ->setDateList($date_list)
                        ->setMoisPlus(intval($mois_plus))
                        ->setPlusTard(intval($plus_tard))
                        ->setRealiserAvant(intval($realiser_avant))
                        ->setEntite(intval($entite))
                        ->setJalon(boolval($jalon))
                        ->setLegale(boolval($legale));

                    if ($demarrage && $demarrage != '') {
                        $tache->setDemarrage(\DateTime::createFromFormat('d/m/Y', $demarrage));
                    } else {
                        $tache->setDemarrage(NULL);
                    }

                    if ($resp_scriptura) {
                        $tache->setResponsableScriptura($resp_scriptura);
                    } else {
                        $tache->setResponsableScriptura(NULL);
                    }
                    if ($resp_client) {
                        $tache->setResponsableClient($resp_client);
                    } else {
                        $tache->setResponsableClient(NULL);
                    }

                    $em->persist($tache);
                    $em->flush();

                    $data = ['erreur' => FALSE];
                    return new JsonResponse(json_encode($data));
                }
            } catch (\Exception $ex) {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => $ex->getMessage(),
                ];
                return new JsonResponse(json_encode($data));
            }
        } else {
            throw new AccessDeniedHttpException('Accès refusé!');
        }
    }

    /**
     * Supprimer une tache dossier
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteTacheDossierAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            if ($id) {
                $em = $this->getDoctrine()
                    ->getManager();
                $tache_dossier = $this->getDoctrine()
                    ->getRepository('AppBundle:TacheDossier')
                    ->find($id);
                if ($tache_dossier) {
                    $em->remove($tache_dossier);
                    $em->flush();

                    $data = [
                        'erreur' => FALSE,
                    ];
                } else {
                    $data = [
                        'erreur' => TRUE,
                        'erreur_text' => "Tache introuvable",
                    ];
                    return new JsonResponse(json_encode($data), 404);
                }
            } else {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => "Tache introuvable",
                ];
                return new JsonResponse(json_encode($data), 404);
            }
            return new JsonResponse(json_encode($data));
        } else {
            throw new AccessDeniedHttpException('Accès refusé.');
        }
    }

    public function importTacheClientToDossierAction(Request $request, Dossier $dossier)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $taches_dossier = $this->getDoctrine()
                    ->getRepository('AppBundle:TacheDossier')
                    ->listeTacheDossier($dossier);
                $id_tache = [];

                /** @var TacheDossier $td */
                foreach ($taches_dossier as $td) {
                    $id_tache[] = $td->getTache()->getId();
                }

                $taches_client = $this->getDoctrine()
                    ->getRepository('AppBundle:TacheClient')
                    ->listeTacheClient($dossier->getSite()->getClient());

                /** @var TacheClient $tc */
                foreach ($taches_client as $tc) {
                    if (!in_array($tc->getTache()->getId(), $id_tache)) {
                        $tache_dossier = new TacheDossier();
                        $tache_dossier->setDossier($dossier)
                            ->setTache($tc->getTache())
                            ->setPeriode($tc->getPeriode())
                            ->setDateList($tc->getDateList())
                            ->setDemarrage($tc->getDemarrage())
                            ->setMoisPlus($tc->getMoisPlus())
                            ->setPlusTard($tc->getPlusTard())
                            ->setRealiserAvant($tc->getRealiserAvant())
                            ->setEntite($tc->getEntite())
                            ->setResponsableClient($tc->getResponsableClient())
                            ->setResponsableScriptura($tc->getResponsableScriptura())
                            ->setJalon($tc->getJalon())
                            ->setLegale($tc->getLegale());
                        $em->persist($tache_dossier);
                    }
                }
                $em->flush();

                $data = [
                    'erreur' => FALSE,
                    'dossier' => $dossier->getNom(),
                    'tache_dossier' => $id_tache,
                ];
                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                $data = [
                    'erreur' => TRUE,
                    'erreur_text' => $ex->getMessage(),
                ];
                return new JsonResponse(json_encode($data), 500);
            }


        } else {
            throw new AccessDeniedHttpException('Accès refusé');
        }
    }
}
