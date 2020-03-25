<?php

namespace ParametreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Departement;

class DepartementController extends Controller
{
    /**
     * Index departement + ADD NEW + EDIT + DELETE
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $method = $request->getMethod();

        $entites = $this->getDoctrine()
            ->getRepository('AppBundle:Entite')
            ->getAllEntite();

        if ($method == 'GET') {
            return $this->render('ParametreBundle:Organisation/Departement:departement.html.twig', array(
                'entites' => $entites,
            ));
        } elseif ($method == 'POST') {
            $id = $request->request->get('id', '');
            $id_entite = $request->request->get('entite');
            $nom = $request->request->get('nom');
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $entite = $this->getDoctrine()
                    ->getRepository('AppBundle:Entite')
                    ->find($id_entite);
                if ($entite) {
                    if ($id == '') {
                        // Ajout nouveau département
                        $departement = new Departement();
                        $departement->setNom($nom);
                        $departement->setEntite($entite);
                        $departement->setDateCreation(new \DateTime());
                        $departement->setOperateur($this->getUser());
                        $em->persist($departement);
                        $em->flush();

                    }

                    $data = array('erreur' => false);
                    return new JsonResponse(json_encode($data));
                } else {
                    $erreur_text = "Sélectionner une entité dans la liste !";
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
                    $erreur_text = "Le nom du département existe déjà !";
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

                $departement = $em->getRepository('AppBundle:Departement')
                    ->find($id);
                if ($departement) {
                    $em->remove($departement);
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
     * Modification d'un departement existant
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        $id = $request->request->get('id', '');
        $id_entite = $request->request->get('entite');
        $nom = $request->request->get('departement');
        try {
            $em = $this->getDoctrine()
                ->getManager();
            $entite = $this->getDoctrine()
                ->getRepository('AppBundle:Entite')
                ->find($id_entite);
            if ($entite) {
                // Modification d'un département existant
                $departement = $this->getDoctrine()->getManager()
                    ->getRepository('AppBundle:Departement')
                    ->find($id);
                if ($departement) {
                    $departement->setNom($nom);
                    $departement->setEntite($entite);
                    $em->flush();
                }

                $data = array('erreur' => false);
                return new JsonResponse(json_encode($data));
            } else {
                $erreur_text = "Sélectionner une entité dans la liste !";
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
                $erreur_text = "Le nom du département existe déjà !";
            }

            $data = array(
                'erreur' => true,
                'erreur_text' => $erreur_text,
            );
            return new JsonResponse(json_encode($data));
        }
    }

    /**
     * Liste des départements pour jqGrid
     *
     * @return JsonResponse
     */
    public function listeAction()
    {
        $deps = $this->getDoctrine()
            ->getRepository('AppBundle:Departement')
            ->getAllDepartement();
        $rows = array();
        foreach ($deps as $dep) {
            $rows[] = array(
                'id' => $dep->getId(),
                'cell' => array(
                    $dep->getEntite()->getId(),
                    $dep->getEntite()->getNom(),
                    $dep->getNom(),
                    $dep->getOperateur()->getPrenom() . ' ' . $dep->getOperateur()->getNom(),
                    $dep->getDateCreation()->format('Y-m-d'),
                    '<i class="fa fa-save icon-action js-save-button js-save-departement" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-departement" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }

    /**
     * Liste des département d'une entité
     * 
     * @param $id
     * @return JsonResponse
     */
    public function listeDepEntiteAction($id)
    {
        $departements = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Departement')
            ->getDepartementEntite($id);
        $data = array();
        if (count($departements) > 0) {
            foreach ($departements as $dep) {
                $data[] = array(
                    'id' => $dep->getId(),
                    'nom' => $dep->getNom(),
                );
            }
        }
        Return new JsonResponse(json_encode($data));
    }
}
