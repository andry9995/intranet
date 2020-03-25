<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\TacheClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TacheClientController extends Controller
{
    public function tacheParClientAction()
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Tache')
            ->listeClient();
        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getAllOperateurByPrenom();
        return $this->render('@Tache/Tache/par-client.html.twig', [
            'clients' => $clients,
            'operateurs' => $operateurs,
        ]);
    }

    public function listeTacheClientAction(Request $request, Client $client)
    {
        if ($request->isXmlHttpRequest()) {
            $taches = $this->getDoctrine()
                ->getRepository('AppBundle:TacheClient')
                ->listeTacheClient($client);

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

            /** @var TacheClient $tache */
            foreach ($taches as $tache) {
                $responsable = "";
                if ($tache->getResponsableScriptura() != NULL) {
                    $responsable = strtoupper($tache->getResponsableScriptura()
                        ->getPrenom());
                }
                if ($tache->getResponsableClient() != NULL) {
                    $responsable = strtoupper($tache->getResponsableClient()
                        ->getLogin());
                }
                $responsable_client = "";
                if ($tache->getResponsableClient()) {
                    $responsable_client = $tache->getResponsableClient()
                        ->getId();
                }
                $responsable_scriptura = "";
                if ($tache->getResponsableScriptura()) {
                    $responsable_scriptura = $tache->getResponsableScriptura()
                        ->getId();
                }
                $entite = "";
                if ($tache->getEntite() != NULL) {
                    if ($tache->getEntite() == 1) {
                        $entite = "Scriptura";
                    } elseif ($tache->getEntite() == 2) {
                        $entite = "Client";
                    }
                }
                $periode = "";
                if ($tache->getPeriode() != "" && $tache->getPeriode() != NULL) {
                    $periode = $periodes[$tache->getPeriode()];
                }
                $tache_domaine = "";
                if ($tache->getTache()->getTacheDomaine()) {
                    $tache_domaine = $tache->getTache()
                        ->getTacheDomaine()
                        ->getDomaine();
                }
                $rows[] = [
                    'id' => $tache->getId(),
                    'cell' => [
                        $tache_domaine,
                        $tache->getTache()->getId(),
                        $tache->getId(),
                        $tache->getTache()->getNom(),
                        $tache->getDemarrage()->format('d-m-Y'),
                        $tache->getPeriode(),
                        $periode,
                        $tache->getJalon(),
                        $tache->getLegale(),
                        $tache->getRealiserAvant(),
                        $tache->getPlusTard(),
                        $tache->getEntite(),
                        $entite,
                        $responsable_scriptura,
                        $responsable_client,
                        $responsable,
                        implode(',', $tache->getDateList()),
                        $tache->getMoisPlus(),
                        '<i class="fa fa-edit icon-action js-edit-tache-client" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-tache-client" title="Supprimer"></i>',
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

    public function saveTacheClientAction(Request $request)
    {
        try {
            $em = $this->getDoctrine()
                ->getManager();

            $id = $request->request->get('id');
            if (trim($id) != "") {
                $tache = $this->getDoctrine()
                    ->getRepository('AppBundle:TacheClient')
                    ->find($id);
            } else {
                $tache = new TacheClient();
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
            $demarrage = $request->request->get('demarrage');
            $client_id = $request->request->get('client_id');

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
            $client = $this->getDoctrine()
                ->getRepository('AppBundle:Client')
                ->find($client_id);

            $tache
                ->setClient($client)
                ->setTache($tache_parent)
                ->setDemarrage(\DateTime::createFromFormat('d/m/Y', $demarrage))
                ->setPeriode($periode)
                ->setDateList($date_list)
                ->setMoisPlus(intval($mois_plus))
                ->setPlusTard(intval($plus_tard))
                ->setRealiserAvant(intval($realiser_avant))
                ->setEntite(intval($entite))
                ->setJalon(boolval($jalon))
                ->setLegale(boolval($legale));
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
        } catch (\Exception $ex) {
            $data = [
                'erreur' => TRUE,
                'erreur_text' => $ex->getMessage(),
            ];
            return new JsonResponse(json_encode($data));
        }
    }

    /**
     * Supprimer une tache client
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteTacheClientAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            if ($id) {
                $em = $this->getDoctrine()
                    ->getManager();
                $tache_client = $this->getDoctrine()
                    ->getRepository('AppBundle:TacheClient')
                    ->find($id);
                if ($tache_client) {
                    $em->remove($tache_client);
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
}