<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\TacheLegale;
use AppBundle\Entity\TacheLegaleAction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TacheLegaleActionController extends Controller
{
    public function listeAction(TacheLegale $tache)
    {
        $actions = $this->getDoctrine()
            ->getRepository('AppBundle:TacheLegaleAction')
            ->getByTache($tache);
        $rows = [];

        /** @var \AppBundle\Entity\TacheLegaleAction $action */
        foreach ($actions as $action) {
            $rows[] = [
                'id' => $action->getId(),
                'cell' => [
                    //$action->getNom(),
                    $action->getTacheListeAction()->getNom(),
                    $action->getDateAction(),
                    $action->getCerfa(),
                    $action->getCommentaire1(),
                    $action->getCommentaire2(),
                    $action->getCommentaire3(),
                    $action->getCommentaire4(),
                    '<i class="fa fa-save icon-action js-save-button js-save-tache-legale-action" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-tache-legale-action" title="Supprimer"></i>',
                ],
            ];
        }
        $liste = [
            'rows' => $rows,
        ];
        return new JsonResponse($liste);
    }

    public function editAction(Request $request, TacheLegale $tache)
    {
        $id = $request->request->get('id', '');
        $nom = $request->request->get('t-legale-action-nom', '');
        $date = $request->request->get('t-legale-action-date', '');
        $cerfa = $request->request->get('t-legale-action-cerfa', '');
        $commentaire1 = $request->request->get('t-legale-action-com1', '');
        $commentaire2 = $request->request->get('t-legale-action-com2', '');
        $commentaire3 = $request->request->get('t-legale-action-com3', '');
        $commentaire4 = $request->request->get('t-legale-action-com4', '');

        $tacheListeAction = $this->getDoctrine()->getRepository('AppBundle:TacheListeAction')
            ->find($nom);

        $em = $this->getDoctrine()
            ->getManager();
        if ($id == 'new_row') {
            $action = new TacheLegaleAction();
            $action
                ->setTacheLegale($tache)
                ->setNom($nom)
                ->setTacheListeAction($tacheListeAction)
                ->setDateAction($date)
                ->setCerfa($cerfa)
                ->setCommentaire1($commentaire1)
                ->setCommentaire2($commentaire2)
                ->setCommentaire3($commentaire3)
                ->setCommentaire4($commentaire4);
            $em->persist($action);
            $em->flush();
        } elseif (intval($id) != 0) {
            /** @var TacheLegaleAction $action */
            $action = $this->getDoctrine()
                ->getRepository('AppBundle:TacheLegaleAction')
                ->find($id);
            if ($action) {
                $action
                    ->setNom($nom)
                    ->setTacheListeAction($tacheListeAction)
                    ->setDateAction($date)
                    ->setCerfa($cerfa)
                    ->setCommentaire1($commentaire1)
                    ->setCommentaire2($commentaire2)
                    ->setCommentaire3($commentaire3)
                    ->setCommentaire4($commentaire4);
                $em->flush();
            }
        } else {
            throw new BadRequestHttpException("Action incorrecte.");
        }
        return new JsonResponse('ok');
    }

    public function removeAction($tache_id)
    {
        if ($tache_id != 'new_row' && $tache_id != '') {
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $tache = $this->getDoctrine()
                    ->getRepository('AppBundle:TacheLegaleAction')
                    ->find($tache_id);
                if ($tache) {
                    $em->remove($tache);
                    $em->flush();
                }
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
        } else {
            $data = [
                'erreur' => FALSE,
            ];
            return new JsonResponse(json_encode($data));
        }
    }

    public function testAction()
    {
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find(11344);
        $actions = $this->getDoctrine()
            ->getRepository('AppBundle:TacheLegale')
            ->getActions($dossier);
        return new Response("ok");
    }
}
