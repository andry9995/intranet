<?php

namespace ParametreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Entite;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EntiteController extends Controller
{
    /**
     * Index Entité + ADD NEW + EDIT + DELETE
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        $method = $request->getMethod();

        if ($method == 'GET') {
            return $this->render('ParametreBundle:Organisation/Entite:entite.html.twig');
        } elseif ($method == 'POST') {
            try {
                $id = $request->request->get('id', '');
                if ($id == '') {
                    // Ajout nouvelle entité
                    $entite = new Entite();
                    $nom = $request->request->get('nom');
                    $forme_juridique = $request->request->get('form_jur');
                    $adresse = $request->request->get('adresse');
                    $email = $request->request->get('email');
                    $tel = $request->request->get('telephone');

                    $entite->setNom($nom);
                    $entite->setFormeJur($forme_juridique);
                    $entite->setAdresse($adresse);
                    $entite->setEmail($email);
                    $entite->setTel($tel);
                    $entite->setOperateur($this->getUser());
                    $entite->setDateCreation(new \DateTime());

                    $em = $this->getDoctrine()
                        ->getManager();
                    $em->persist($entite);
                    $em->flush();

                    $data = array(
                        'erreur' => false,
                    );
                } else {
                    // Modification d'une entité existante
                    $em = $entite = $this->getDoctrine()
                        ->getManager();

                    $entite = $em->getRepository('AppBundle:Entite')
                        ->find($id);
                    if ($entite) {

                        $nom = $request->request->get('nom');
                        $forme_juridique = $request->request->get('form_jur');
                        $adresse = $request->request->get('adresse');
                        $email = $request->request->get('email');
                        $tel = $request->request->get('telephone');

                        $entite->setNom($nom);
                        $entite->setFormeJur($forme_juridique);
                        $entite->setAdresse($adresse);
                        $entite->setEmail($email);
                        $entite->setTel($tel);
                        $entite->setOperateur($this->getUser());
                        $entite->setDateCreation(new \DateTime());

                        $em->flush();
                    }
                    $data = array(
                        'erreur' => false,
                    );
                }
                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
                if ($pos == false) {
                    $erreur_text = "Il y a une erreur !";
                } else {
                    $erreur_text = "Le nom de l'entité existe déjà !";
                }

                $data = array(
                    'erreur' => true,
                    'erreur_text' => $erreur_text,
                );
                return new JsonResponse(json_encode($data));
            }
        } elseif ($method == 'DELETE') {
            try {
                $id = $request->request->get('id');
                $em = $entite = $this->getDoctrine()
                    ->getManager();

                $entite = $em->getRepository('AppBundle:Entite')
                    ->find($id);
                if ($entite) {
                    $em->remove($entite);
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
        throw new AccessDeniedHttpException("Accès refusé.");
    }

    /**
     * Modification d'une entité existante
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $id = $request->request->get('id', '');
                // Modification d'une entité existante
                $em = $entite = $this->getDoctrine()
                    ->getManager();

                $entite = $em->getRepository('AppBundle:Entite')
                    ->find($id);
                if ($entite) {

                    $nom = $request->request->get('entite');
                    $forme_juridique = $request->request->get('form_jur');
                    $adresse = $request->request->get('adresse');
                    $email = $request->request->get('email');
                    $telephone = $request->request->get('telephone');

                    $entite->setNom($nom);
                    $entite->setFormeJur($forme_juridique);
                    $entite->setAdresse($adresse);
                    $entite->setEmail($email);
                    $entite->setTel($telephone);
                    $entite->setOperateur($this->getUser());

                    $em->flush();
                }
                $data = array(
                    'erreur' => false,
                );
                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                $pos = strpos($ex->getMessage(), 'nom_UNIQUE');
                if ($pos == false) {
                    $erreur_text = "Il y a une erreur !";
                } else {
                    $erreur_text = "Le nom de l'entité existe déjà !";
                }

                $data = array(
                    'erreur' => true,
                    'erreur_text' => $erreur_text,
                );
                return new JsonResponse(json_encode($data));
            }
        }
    }

    /**
     * Liste des entité en HTML select ou JSON
     *
     * @param Request $request
     * @param $json
     * @return JsonResponse|Response
     */
    public function listeSimpleAction(Request $request, $json)
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $entites = $this->getDoctrine()
            ->getRepository('AppBundle:Entite')
            ->getAllEntite();

        if ($json == 1) {
            return new JsonResponse($serializer->serialize($entites, 'json'));
        } else {
            return $this->render('@Parametre/Organisation/Entite/entite-liste.html.twig', array(
                'entites' => $entites,
            ));
        }

    }

    /**
     * Liste entité pour jqGrid
     * 
     * @return JsonResponse
     */
    public function listeAction()
    {
        $entites = $this->getDoctrine()
            ->getRepository('AppBundle:Entite')
            ->getAllEntite();
        $rows = array();
        foreach ($entites as $entite) {
            $rows[] = array(
                'id' => $entite->getId(),
                'cell' => array(
                    $entite->getNom(),
                    $entite->getFormeJur(),
                    $entite->getEmail(),
                    $entite->getTel(),
                    $entite->getAdresse(),
                    $entite->getOperateur()->getPrenom() . ' ' . $entite->getOperateur()->getNom(),
                    $entite->getDateCreation()->format('Y-m-d'),
                    '<i class="fa fa-save icon-action js-save-button js-save-entite" title="Enregistrer"></i><i class="fa fa-trash icon-action js-delete-entite" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);
    }
}
