<?php

namespace ProcedureBundle\Controller;

use AppBundle\Entity\ProcedureIntranet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProcedureController extends Controller
{
    /**
     * Index procédure
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $method = $request->getMethod();

        if ($method == 'GET') {
            $postes = $this->getDoctrine()
                ->getRepository('AppBundle:Organisation')
                ->getAllPoste();
            $procedures = $this->getDoctrine()
                ->getRepository('AppBundle:ProcedureIntranet')
                ->getAllProcedure();

            $unites = $this->getDoctrine()
                ->getRepository('AppBundle:UniteComptage')
                ->getAllUnite();

            return $this->render('ProcedureBundle:Procedure:index.html.twig', array(
                'postes' => $postes,
                'procedures' => $procedures,
                'unites' => $unites,
            ));
        }
    }

    /**
     * Listes des procédures à envoyer à jqGrid
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $procedures = $this->getDoctrine()
                ->getRepository('AppBundle:ProcedureIntranet')
                ->getAllProcedure();

            $rows = array();
            /** @var ProcedureIntranet $procedure */
            foreach ($procedures as $procedure) {
                $prec = [];
                $suiv = [];
                $poste_id = '0';
                $poste = '';
                $unite_compt_id = '0';
                $unite_compt = '';

                if ($procedure->getPrecedent()) {
                    foreach ($procedure->getPrecedent() as $id) {
                        $item = $this->getDoctrine()
                            ->getRepository('AppBundle:ProcedureIntranet')
                            ->find($id);
                        if ($item) {
                            $prec[] = $item->getNumero();
                        }
                    }
                }
                if ($procedure->getSuivant()) {
                    foreach ($procedure->getSuivant() as $id) {
                        $item = $this->getDoctrine()
                            ->getRepository('AppBundle:ProcedureIntranet')
                            ->find($id);
                        if ($item) {
                            $suiv[] = $item->getNumero();
                        }
                    }
                }
                if ($procedure->getOrganisation()) {
                    $poste_id = $procedure->getOrganisation()->getId();
                    $poste = $procedure->getOrganisation()->getNom();
                }
                if ($procedure->getUniteComptage()) {
                    $unite_compt_id = $procedure->getUniteComptage()->getId();
                    $unite_compt = $procedure->getUniteComptage()->getUnite();
                }

                $rows[] = array(
                    'id' => $procedure->getId(),
                    'cell' => array(
                        $procedure->getNumero(),
                        $procedure->getNom(),
                        $procedure->getDescription(),
                        implode(", ", $prec),
                        implode(", ", $suiv),
                        $poste_id,
                        $poste,
                        $unite_compt_id,
                        $unite_compt,
                        $procedure->getDuree(),
                        '<i class="fa fa-edit icon-action js-edit-procedure" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-procedure" title="Supprimer"></i>',
                    )
                );
            }
            $liste = array(
                'rows' => $rows,
            );
            return new JsonResponse($liste);
        } else {
            throw $this->createAccessDeniedException('Opération non autorisée');
        }
    }

    /**
     *  Listes des procédures JSON ou HTML
     *
     * @param $json
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listeSimpleAction($json)
    {
        $procedures = $this->getDoctrine()
            ->getRepository('AppBundle:ProcedureIntranet')
            ->getAllProcedure();

        if ($json == 1) {
            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });

            $serializer = new Serializer(array($normalizer), array($encoder));
            return JsonResponse::fromJsonString($serializer->serialize($procedures, 'json'));
        } else {
            return $this->render('@Procedure/Procedure/procedure-liste.html.twig', array(
                'procedures' => $procedures,
            ));
        }
    }

    public function oneAction(ProcedureIntranet $procedure)
    {
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        return JsonResponse::fromJsonString($serializer->serialize($procedure, 'json'));
    }

    /**
     * Mise à jour procédure via jqGrid
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        try {
            $em = $this->getDoctrine()
                ->getManager();
            $id = $request->request->get('id');
            $procedure = false;

            if ($id != '') {
                $procedure = $this->getDoctrine()
                    ->getRepository('AppBundle:ProcedureIntranet')
                    ->find($id);
            }
            if ($procedure) {
                $numero = $request->request->get('numero');
                $nom = $request->request->get('procedure');
                $description = $request->request->get('description');
                $id_prec = $request->request->get('precedent');
                $id_suiv = $request->request->get('suivant');
                $id_poste = $request->request->get('poste');
                $id_unite = $request->request->get('unite');
                $duree = $request->request->get('duree') != '' ? $request->request->get('duree') : 0;

                $precedent = false;
                $suivant = false;
                $poste = false;
                $unite = false;

                if ($id_prec != '') {
                    $precedent = $this->getDoctrine()
                        ->getRepository('AppBundle:ProcedureIntranet')
                        ->find($id_prec);
                }
                if ($id_suiv != '') {
                    $suivant = $this->getDoctrine()
                        ->getRepository('AppBundle:ProcedureIntranet')
                        ->find($id_suiv);
                }
                if ($id_poste != '') {
                    $poste = $this->getDoctrine()
                        ->getRepository('AppBundle:Poste')
                        ->find($id_poste);
                }
                if ($id_unite != '') {
                    $unite = $this->getDoctrine()
                        ->getRepository('AppBundle:UniteComptage')
                        ->find($id_unite);
                }

                $procedure->setNumero($numero);
                $procedure->setNom($nom);
                $procedure->setDescription($description);
                if ($precedent) {
                    $procedure->setPrecedent($precedent);
                }
                if ($suivant) {
                    $procedure->setSuivant($suivant);
                }
                if ($poste) {
                    $procedure->setPoste($poste);
                }
                if ($unite) {
                    $procedure->setUniteComptage($unite);
                }
                $procedure->setDuree($duree);

                $em->persist($procedure);
                $em->flush();

                $data = array('erreur' => false);
                return new JsonResponse(json_encode($data));
            } else {
                $erreur_text = "Procédure introuvable";
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $erreur_text,
                );
                return new JsonResponse(json_encode($data));
            }
        } catch (\Exception $ex) {
            $erreur_text = "Il y a une erreur !";
            $data = array(
                'erreur' => true,
                'erreur_text' => $erreur_text,
            );
            return new JsonResponse(json_encode($data));
        }
    }

    /**
     * Ajout nouvelle procédure via AJAX
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function addAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $id = $request->request->get('id');
                $numero = $request->request->get('numero');
                $nom = $request->request->get('procedure');
                $description = $request->request->get('description');
                $prec = $request->request->get('precedent');
                $suiv = $request->request->get('suivant');
                $id_poste = $request->request->get('poste');
                $id_unite = $request->request->get('unite');
                $duree = $request->request->get('duree') != '' ? $request->request->get('duree') : 0;

                $poste = false;
                $unite = false;

                if ($id_poste != '') {
                    $poste = $this->getDoctrine()
                        ->getRepository('AppBundle:Organisation')
                        ->find($id_poste);
                }
                if ($id_unite != '') {
                    $unite = $this->getDoctrine()
                        ->getRepository('AppBundle:UniteComptage')
                        ->find($id_unite);
                }
                if ($id == '') {
                    $procedure = new ProcedureIntranet();
                } else {
                    $procedure = $this->getDoctrine()
                        ->getRepository('AppBundle:ProcedureIntranet')
                        ->find($id);
                    if (!$procedure) {
                        $procedure = new ProcedureIntranet();
                    }
                }
                $procedure->setNumero($numero);
                $procedure->setNom($nom);
                $procedure->setDescription($description);
                if (is_array($prec)) {
                    $procedure->setPrecedent($prec);
                }
                if (is_array($suiv)) {
                    $procedure->setSuivant($suiv);
                }
                if ($poste) {
                    $procedure->setOrganisation($poste);
                }
                if ($unite) {
                    $procedure->setUniteComptage($unite);
                }
                $procedure->setDuree($duree);

                $em->persist($procedure);
                $em->flush();

                $data = array('erreur' => false);
                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                $erreur_text = "Il y a une erreur !";
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $erreur_text,
                );
                return new JsonResponse(json_encode($data));
            }
        } else {
            return $this->createAccessDeniedException('Accès refusé !');
        }
    }

    /**
     * Supprimer une procédure
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function deleteAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id', '');
            $em = $this->getDoctrine()
                ->getManager();

            if ($id != '') {
                $procedure = $this->getDoctrine()
                    ->getRepository('AppBundle:ProcedureIntranet')
                    ->find($id);
                if ($procedure) {
                    $em->remove($procedure);
                    $em->flush();
                } else {
                    return $this->createNotFoundException('Procédure introuvable');
                }
            } else {
                return $this->createNotFoundException('Procédure introuvable');
            }

            $data = array('erreur' => false);
            return new JsonResponse(json_encode($data));
        } else {
            return $this->createAccessDeniedException('Operation refusée!');
        }
    }
}
