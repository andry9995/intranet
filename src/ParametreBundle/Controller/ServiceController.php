<?php

namespace ParametreBundle\Controller;

use AppBundle\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Departement;
use AppBundle\Entity\Service;

class ServiceController extends Controller
{
    /**
     * Index Service + ADD NEW + EDIT + DELETE
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
            return $this->render('ParametreBundle:Organisation/Service:service.html.twig', array(
                'entites' => $entites,
            ));
        } elseif ($method == 'POST') {
            $id = $request->request->get('id', '');
            $id_departement = $request->request->get('departement');
            $nom = $request->request->get('nom');
            $description = $request->request->get('description');
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $departement = $this->getDoctrine()
                    ->getRepository('AppBundle:Departement')
                    ->find($id_departement);
                if ($departement) {
                    if ($id == '') {
                        // Ajout nouveau service
                        $service = new Service();
                        $service->setNom($nom);
                        $service->setDescription($description);
                        $service->setDepartement($departement);
                        $service->setDateCreation(new \DateTime());
                        $service->setOperateur($this->getUser());
                        $em->persist($service);
                        $em->flush();

                    } else {
                        // Modification d'un service existant
                        $service = $this->getDoctrine()->getManager()
                            ->getRepository('AppBundle:Service')
                            ->find($id);
                        if ($service) {
                            $service->setNom($nom);
                            $service->setDescription($description);
                            $service->setDepartement($departement);
                            $service->setDateCreation(new \DateTime());
                            $service->setOperateur($this->getUser());
                            $em->flush();
                        }
                    }

                    $data = array('erreur' => false);
                    return new JsonResponse(json_encode($data));
                } else {
                    $erreur_text = "Sélectionner un département dans la liste !";
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
                    $erreur_text = "Le nom du service existe déjà !";
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

                $service = $em->getRepository('AppBundle:Service')
                    ->find($id);
                if ($service) {
                    $em->remove($service);
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
     * Liste services pour jqGrid
     *
     * @return JsonResponse
     */
    public function listeAction()
    {
        $services = $this->getDoctrine()
            ->getRepository('AppBundle:Service')
            ->getAllService();
        $rows = array();
        foreach ($services as $service) {
            $rows[] = array(
                'id' => $service->getId(),
                'cell' => array(
                    $service->getDepartement()->getEntite()->getId(),
                    $service->getDepartement()->getEntite()->getNom(),
                    $service->getDepartement()->getId(),
                    $service->getDepartement()->getNom(),
                    $service->getNom(),
                    $service->getDescription(),
                    $service->getOperateur()->getPrenom() . ' ' . $service->getOperateur()->getNom(),
                    $service->getDateCreation()->format('Y-m-d'),
                    '<i class="fa fa-edit icon-action js-edit-service" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-service" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }

    /**
     * Liste des services d'un département
     *
     * @param $id
     * @return JsonResponse
     */
    public function listeServiceDepartementAction($id)
    {
        $services = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Service')
            ->getServiceDepartement($id);
        $data = array();
        if (count($services) > 0) {
            foreach ($services as $service) {
                $data[] = array(
                    'id' => $service->getId(),
                    'nom' => $service->getNom(),
                );
            }
        }
        Return new JsonResponse(json_encode($data));
    }
}
