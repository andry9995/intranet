<?php

namespace ProcedureBundle\Controller;

use AppBundle\Entity\UniteComptage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UniteController extends Controller
{
    /**
     *  Listes des unités de comptage JSON ou HTML
     *
     * @param Request $request
     * @param $json
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listeAction(Request $request, $json)
    {
        $unites = $this->getDoctrine()
            ->getRepository('AppBundle:UniteComptage')
            ->getAllUnite();

        if ($json == 1) {
            $rows = array();
            foreach ($unites as $unite) {
                $rows[] = array(
                    'id' => $unite->getId(),
                    'cell' => array(
                        $unite->getUnite(),
                        $unite->getCode(),
                        '<i class="fa fa-save icon-action js-save-button js-save-unite" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-unite" title="Supprimer"></i>',
                    )
                );
            }
            $liste = array(
                'rows' => $rows,
            );
            return new JsonResponse($liste);
        } else {
            return $this->render('@Procedure/Unite/liste.html.twig', array(
                'unites' => $unites,
            ));
        }
    }

    /**
     * Ajouter une nouvelle unité
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $code = $request->request->get('code');
                $code = str_replace(' ', '_', $code);
                $unite = $request->request->get('unite');

                $em = $this->getDoctrine()
                    ->getManager();

                $unite_comptage = new UniteComptage();
                $unite_comptage->setCode($code);
                $unite_comptage->setUnite($unite);

                $em->persist($unite_comptage);
                $em->flush();

                $data = array(
                    'erreur' => false,
                );

                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $ex->getMessage(),
                );

                return new JsonResponse(json_encode($data));
            }
        }
    }

    /**
     * Modifier une unité
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $id = $request->request->get('id');
                $code = $request->request->get('code');
                $code = str_replace(' ', '_', $code);
                $unite = $request->request->get('unite');

                $unite_comptage = $this->getDoctrine()
                    ->getRepository('AppBundle:UniteComptage')
                    ->find($id);

                if ($unite_comptage) {
                    $em = $this->getDoctrine()
                        ->getManager();

                    $unite_comptage->setCode($code);
                    $unite_comptage->setUnite($unite);
                    $em->flush();

                    $data = array(
                        'erreur' => false,
                    );

                    return new JsonResponse(json_encode($data));
                } else {
                    return $this->createNotFoundException('Unité de comptage introuvable');
                }
            } catch (\Exception $ex) {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $ex->getMessage(),
                );

                return new JsonResponse(json_encode($data));
            }
        }
    }

    /**
     * Supprimer une entité
     * 
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $id = $request->request->get('id');

                $unite_comptage = $this->getDoctrine()
                    ->getRepository('AppBundle:UniteComptage')
                    ->find($id);

                if ($unite_comptage) {
                    $em = $this->getDoctrine()
                        ->getManager();

                    $em->remove($unite_comptage);
                    $em->flush();

                    $data = array(
                        'erreur' => false,
                    );

                    return new JsonResponse(json_encode($data));
                } else {
                    return $this->createNotFoundException('Unité de comptage introuvable');
                }
            } catch (\Exception $ex) {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $ex->getMessage(),
                );

                return new JsonResponse(json_encode($data));
            }
        }
    }
}
