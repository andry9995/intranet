<?php

namespace ParametreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Poste;

class PosteController extends Controller
{
    /**
     * Index Poste + ADD NEW + EDIT + DELETE
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
            return $this->render('ParametreBundle:Organisation/Poste:poste.html.twig', array(
                'entites' => $entites,
            ));
        } elseif ($method == 'POST') {
            $id = $request->request->get('id', '');
            $id_cellule = $request->request->get('cellule');
            $nom = $request->request->get('nom');
            $description = $request->request->get('description');
            $capacite = intval($request->request->get('capacite'));
            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $cellule = $this->getDoctrine()
                    ->getRepository('AppBundle:Cellule')
                    ->find($id_cellule);
                if ($cellule) {
                    if ($id == '') {
                        // Ajout nouveau poste
                        $poste = new Poste();
                        $poste
                            ->setNom($nom)
                            ->setDescription($description)
                            ->setCapacite($capacite)
                            ->setCellule($cellule)
                            ->setDateCreation(new \DateTime())
                            ->setOperateur($this->getUser());
                        $em->persist($poste);
                        $em->flush();

                    } else {
                        // Modification d'un poste existant
                        /** @var Poste $poste */
                        $poste = $this->getDoctrine()->getManager()
                            ->getRepository('AppBundle:Poste')
                            ->find($id);
                        if ($poste) {
                            $poste
                                ->setNom($nom)
                                ->setDescription($description)
                                ->setCapacite($capacite)
                                ->setCellule($cellule)
                                ->setDateCreation(new \DateTime())
                                ->setOperateur($this->getUser());
                            $em->flush();
                        }
                    }

                    $data = array('erreur' => false);
                    return new JsonResponse(json_encode($data));
                } else {
                    $erreur_text = "Sélectionner une cellule dans la liste !";
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
                    $erreur_text = "Le nom du poste existe déjà !";
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

                $poste = $em->getRepository('AppBundle:Poste')
                    ->find($id);
                if ($poste) {
                    $em->remove($poste);
                    $em->flush();
                }

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
     * Liste des postes
     *
     * @param $json
     * @return JsonResponse|Response
     */
    public function listeForSelectAction($json)
    {
        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getAllPoste();

        if ($json == 1) {
            return new JsonResponse($postes);
        } else {
            return $this->render('ParametreBundle:Organisation/Poste:poste-for-select.html.twig', array(
                'postes' => $postes,
            ));
        }
    }


    /**
     * Liste des postes
     *
     * @param $json
     * @return JsonResponse|Response
     */
    public function listePosteAction(Request $request)
    {
        $processId = $request->request->get('processId');
        $postesChoisis = $request->request->get('postesChoisis');
        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getAllPoste();

        return $this->render('ParametreBundle:Organisation:liste_poste_processus.html.twig', array(
            'postes' => $postes,
            'processId' => $processId,
            'postesChoisis' => $postesChoisis,
        ));
    }


    public function listePosteChoisiAction(Request $request)
    {
        $postes = $request->request->get('postes');
        $processId = $request->request->get('processId');
        return $this->render('ParametreBundle:Organisation:postes_choisis_processus.html.twig', array(
            'postesChoisis' => $postes,
            'processId' => $processId,
        ));
    }

    /**
     * Liste des Postes pour jqGrid
     * 
     * @return JsonResponse
     */
    public function listeAction()
    {
        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Poste')
            ->getAllPoste();
        $rows = array();
        /** @var Poste $poste */
        foreach ($postes as $poste) {
            $rows[] = array(
                'id' => $poste->getId(),
                'cell' => array(
                    $poste->getCellule()->getService()->getDepartement()->getEntite()->getId(),
                    $poste->getCellule()->getService()->getDepartement()->getEntite()->getNom(),
                    $poste->getCellule()->getService()->getDepartement()->getId(),
                    $poste->getCellule()->getService()->getDepartement()->getNom(),
                    $poste->getCellule()->getService()->getId(),
                    $poste->getCellule()->getService()->getNom(),
                    $poste->getCellule()->getId(),
                    $poste->getCellule()->getNom(),
                    $poste->getNom(),
                    $poste->getCapacite(),
                    $poste->getDescription(),
                    $poste->getOperateur()->getPrenom() . ' ' . $poste->getOperateur()->getNom(),
                    $poste->getDateCreation()->format('Y-m-d'),
                    '<i class="fa fa-edit icon-action js-edit-poste" title="Modifier"></i><i class="fa fa-trash icon-action js-delete-poste" title="Supprimer"></i>',
                ),
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }
}
