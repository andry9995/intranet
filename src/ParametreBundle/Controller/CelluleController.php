<?php

namespace ParametreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Cellule;
use AppBundle\Entity\CodeCellule;

class CelluleController extends Controller
{
    /**
     * Index cellule + ADD NEW + EDIT + DELETE
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        $method = $request->getMethod();

        $entites = $this->getDoctrine()
            ->getRepository('AppBundle:Entite')
            ->getAllEntite();

        if ($method == 'GET') {
            return $this->render('ParametreBundle:Organisation/Cellule:cellule.html.twig', array(
                'entites' => $entites,
            ));
        } elseif ($method == 'POST') {
            $id = $request->request->get('id', '');
            $id_service = $request->request->get('service');
            $code = $request->request->get('code');
            $nom = $request->request->get('nom');
            $description = $request->request->get('description');
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $service = $this->getDoctrine()
                    ->getRepository('AppBundle:Service')
                    ->find($id_service);
                if ($service) {
                    if ($id == '') {
                        // Ajout nouvelle cellule
                        $cellule = new Cellule();
                        $cellule->setNom($nom);
                        $cellule->setCode($code);
                        $cellule->setDescription($description);
                        $cellule->setService($service);
                        $cellule->setDateCreation(new \DateTime());
                        $cellule->setOperateur($this->getUser());
                        $em->persist($cellule);
                        $em->flush();

                    } else {
                        // Modification d'une cellule existante
                        $cellule = $this->getDoctrine()->getManager()
                            ->getRepository('AppBundle:Cellule')
                            ->find($id);
                        if ($cellule) {
                            $cellule->setNom($nom);
                            $cellule->setCode($code);
                            $cellule->setDescription($description);
                            $cellule->setService($service);
                            $cellule->setDateCreation(new \DateTime());
                            $cellule->setOperateur($this->getUser());
                            $em->flush();
                        }
                    }

                    $data = array('erreur' => false);
                    return new JsonResponse(json_encode($data));
                } elseif (!$service) {
                    $erreur_text = "Sélectionner un service dans la liste !";
                    $data = array(
                        'erreur' => true,
                        'erreur_text' => $erreur_text,
                    );
                    return new JsonResponse(json_encode($data));
                }
            } catch (\Exception $ex) {
                $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
                if ($pos == false) {
                    $erreur_text = "Il y a une erreur !";
                } else {
                    $erreur_text = "Le nom de la cellule existe déjà !";
                }

                $data = array(
                    'erreur' => true,
                    'erreur_text' => $erreur_text,
                );
                return new JsonResponse(json_encode($data));
            }
        } elseif ($method == 'DELETE') {
            try {
                $id = $request->request->get('id', '');
                $em = $entite = $this->getDoctrine()
                    ->getManager();

                $cellule = $em->getRepository('AppBundle:Cellule')
                    ->find($id);
                if ($cellule) {
                    $em->remove($cellule);
                    $em->flush();
                }

                $data = array(
                    'erreur' => false,
                );
                return new JsonResponse(json_encode($data));
            } catch (Exception $ex) {
                $data = array(
                    'erreur' => true,
                    'erreur_text' => $ex->getMessage(),
                );
                return new JsonResponse(json_encode($data));
            }
        }
    }

    /**
     * Liste des cellules
     * 
     * @return JsonResponse
     */
    public function listeAction()
    {
        $cellules = $this->getDoctrine()
            ->getRepository('AppBundle:Cellule')
            ->getAllCellule();
        $rows = array();
        foreach ($cellules as $cellule) {
            $rows[] = array(
                'id' => $cellule->getId(),
                'cell' => array(
                    $cellule->getService()->getDepartement()->getEntite()->getId(),
                    $cellule->getService()->getDepartement()->getEntite()->getNom(),
                    $cellule->getService()->getDepartement()->getId(),
                    $cellule->getService()->getDepartement()->getNom(),
                    $cellule->getService()->getId(),
                    $cellule->getService()->getNom(),
                    $cellule->getCode(),
                    $cellule->getNom(),
                    $cellule->getDescription(),
                    $cellule->getOperateur()->getPrenom() . ' ' . $cellule->getOperateur()->getNom(),
                    $cellule->getDateCreation()->format('Y-m-d'),
                    '<i class="fa fa-edit icon-action js-edit-cellule" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-cellule" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }

    /**
     * Listes des cellules d'un service
     * @param $id
     * @return JsonResponse
     */
    public function listeCelluleServiceAction($id)
    {
        $cellule = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Cellule')
            ->getCelluleService($id);
        $data = array();
        if (count($cellule) > 0) {
            foreach ($cellule as $cellule) {
                $data[] = array(
                    'id' => $cellule->getId(),
                    'nom' => $cellule->getNom(),
                );
            }
        }
        Return new JsonResponse(json_encode($data));
    }
}
