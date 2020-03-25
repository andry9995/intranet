<?php

namespace ParametreBundle\Controller;

use AppBundle\Entity\AccesOperateur;
use AppBundle\Entity\MenuIntranetAccess;
use AppBundle\Entity\MenuIntranetOperateur;
use AppBundle\Entity\MenuIntranetPoste;
use AppBundle\Entity\OperateurUtilisateur;
use AppBundle\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Operateur;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PersonnelController extends Controller
{
    /**
     * Index Personnel + ADD NEW + EDIT + DELETE
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        $method = $request->getMethod();

//        $postes = $this->getDoctrine()
//            ->getRepository('AppBundle:Poste')
//            ->getAllPosteWithCellule();
        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getAllPoste();


        $roles = $this->getDoctrine()
            ->getRepository('AppBundle:AccesOperateur')
            ->getAllAcessOperateur();

        $rattachements = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getManagerAndSuperviseur();

        if ($method == 'GET') {
            return $this->render('ParametreBundle:Organisation/Personnel:personnel.html.twig', array(
                'postes' => $postes,
                'roles' => $roles,
                'rattachements' => $rattachements,
            ));
        } elseif ($method == 'POST') {
            $id = $request->request->get('id', '');
            $id_poste = $request->request->get('poste');
            $id_role = $request->request->get('role');
            $id_rattachement = $request->request->get('rattachement');
            $matricule = $request->request->get('matricule');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $adresse = $request->request->get('adresse');
            $telephone = $request->request->get('telephone');
            $sexe = $request->request->get('sexe');
            $login = $request->request->get('login');
            $password = $request->request->get('password');
            $date_entree = $request->request->get('date_entree');
            $date_sortie = $request->request->get('date_sortie');

            try {
                $em = $this->getDoctrine()
                    ->getManager();
                $poste = $this->getDoctrine()
                    ->getRepository('AppBundle:Organisation')
                    ->find($id_poste);
                $role = $this->getDoctrine()
                    ->getRepository('AppBundle:AccesOperateur')
                    ->find($id_role);

                if ($id == '') {
                    // Ajout nouveau personnel
                    $operateur = new Operateur();
                    $operateur->setMatricule($matricule);
                    $operateur->setNom($nom);
                    $operateur->setPrenom($prenom);
                    $operateur->setAdresse($adresse);
                    $operateur->setTel($telephone);
                    $operateur->setSexe($sexe);
                    $operateur->setLogin($login);
                    $operateur->setPassword($password);
                    $operateur->setCoeff(1);

                    if ($date_entree != "") {
                        $operateur->setDateEntree(\DateTime::createFromFormat('d-m-Y', $date_entree));
                    } else {
                        $operateur->setDateEntree(null);
                    }
                    if ($date_sortie != "") {
                        $operateur->setDateSortie(\DateTime::createFromFormat('d-m-Y', $date_sortie));
                    } else {
                        $operateur->setDateSortie(null);
                    }
                    if ($poste) {
                        $operateur->setOrganisation($poste);
                    }
                    if ($role) {
                        $operateur->setAccesOperateur($role);
                    }
                    $em->persist($operateur);
                    $em->flush();
                    $em->refresh($operateur);

                    $this->getDoctrine()
                        ->getRepository('AppBundle:Rattachement')
                        ->saveRattachement($operateur->getId(), $id_rattachement);

                } else {
                    // Modification d'un personnel existant
                    $operateur = $this->getDoctrine()->getManager()
                        ->getRepository('AppBundle:Operateur')
                        ->find($id);
                    if ($operateur) {
                        $operateur->setMatricule($matricule);
                        $operateur->setNom($nom);
                        $operateur->setPrenom($prenom);
                        $operateur->setAdresse($adresse);
                        $operateur->setTel($telephone);
                        $operateur->setSexe($sexe);
                        $operateur->setLogin($login);
                        $operateur->setPassword($password);

                        if ($date_entree != "") {
                            $operateur->setDateEntree(\DateTime::createFromFormat('d-m-Y', $date_entree));
                        } else {
                            $operateur->setDateEntree(null);
                        }
                        if ($date_sortie != "") {
                            $operateur->setDateSortie(\DateTime::createFromFormat('d-m-Y', $date_sortie));
                        } else {
                            $operateur->setDateSortie(null);
                        }
                        if ($poste) {
                            $operateur->setOrganisation($poste);
                        }
                        if ($role) {
                            $operateur->setAccesOperateur($role);
                        }

                        $em->flush();
                        $em->refresh($operateur);

                        $this->getDoctrine()
                            ->getRepository('AppBundle:Rattachement')
                            ->saveRattachement($operateur->getId(), $id_rattachement);

                    }
                }

                $data = array('erreur' => false);
                return new JsonResponse(json_encode($data));
            } catch (\Exception $ex) {
                if (strpos($ex->getMessage(), "UNIQUE_login")) {
                    $erreur_text = "Ce login est déjà utilisé!";
                } elseif (strpos($ex->getMessage(), "UNIQUE_matricule")) {
                    $erreur_text = "Ce matricule est déjà utilisé!";
                } else {
                    $erreur_text = "Il y a une erreur !";
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

                $operateur = $em->getRepository('AppBundle:Operateur')
                    ->find($id);
                if ($operateur) {
                    $operateur->setSupprimer(1);
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
     * Modifier un Personnel existant
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            $action = $request->request->get('oper');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            //$adresse = $request->request->get('adresse');
            $telephone = $request->request->get('telephone');
            $sexe = $request->request->get('sexe');
            $login = $request->request->get('login');
            $password = $request->request->get('password');
            $poste_id = $request->request->get('poste');
            //$role_id = $request->request->get('role');
            $rattachement_id = $request->request->get('rattachement');
            $date_entree = $request->request->get('date_entree');
            $date_sortie = $request->request->get('date_sortie');
            $aff = $request->request->get('affecter-dossier');
            if ($aff == 'Oui')
                $aff_dossier = 1;
            else
                $aff_dossier = 0;
            $em = $this->getDoctrine()
                ->getManager();
            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find($id);
            if ($operateur) {
                $poste = false;
                $role = false;
                if ($poste_id != '0') {
                    $poste = $this->getDoctrine()
                        ->getRepository('AppBundle:Organisation')
                        ->find($poste_id);
                }
                /*if ($role_id != '0') {
                    $role = $this->getDoctrine()
                        ->getRepository('AppBundle:AccesOperateur')
                        ->find($role_id);
                }*/

                if ($action == 'edit') {
                    $operateur->setNom($nom);
                    $operateur->setPrenom($prenom);
                    //$operateur->setAdresse($adresse);
                    $operateur->setTel($telephone);
                    $operateur->setSexe($sexe);
                    $operateur->setLogin($login);
                    $operateur->setPassword($password);

                    if ($date_entree != "") {
                        $operateur->setDateEntree(\DateTime::createFromFormat('d-m-Y', $date_entree));
                    } else {
                        $operateur->setDateEntree(null);
                    }
                    if ($date_sortie != "") {
                        $operateur->setDateSortie(\DateTime::createFromFormat('d-m-Y', $date_sortie));
                    } else {
                        $operateur->setDateSortie(null);
                    }
                    if ($poste) {
                        $operateur->setOrganisation($poste);
                    }
                    if ($role) {
                        $operateur->setAccesOperateur($role);
                    }

                    $operateur->setAffecterDossier($aff_dossier);

                    $em->flush();

                    $rattachement = $this->getDoctrine()
                        ->getRepository('AppBundle:Rattachement')
                        ->saveRattachement($id, $rattachement_id);
                }
            }
            Return new JsonResponse(json_encode(array('ok')));
        }
        Return new JsonResponse(json_encode(array('ko')));
    }

    /**
     * Liste des personnels pour jqGrid
     *
     * @return JsonResponse
     */
    public function listeAction()
    {
        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getAllOperateur();

        $rows = array();

        /** @var Operateur $operateur */
        foreach ($operateurs as $operateur) {
            $poste = '';
            $poste_id = '0';
            $role = '';
            $role_id = '0';
            $rattachement_id='0';
            $rattachement='';

            if ($operateur->getOrganisation()) {
                $poste = $operateur->getOrganisation()->getNom();
                $poste_id = $operateur->getOrganisation()->getId();
            }

            /*if ($operateur->getAccesOperateur()) {
                $role = $operateur->getAccesOperateur()
                    ->getLibelle();
                $role_id = $operateur->getAccesOperateur()
                    ->getId();
            }*/

            if ($operateur->getDateEntree()) {
                $date_entree = $operateur->getDateEntree()->format('Y-m-d');
            } else {
                $date_entree = "";
            }

            if ($operateur->getDateSortie()) {
                $date_sortie = $operateur->getDateSortie()->format('Y-m-d');
            } else {
                $date_sortie = "";
            }


            $rattachements = $this->getDoctrine()
                ->getRepository('AppBundle:Rattachement')
                ->getManagerSup($operateur->getId());
            if (count($rattachements)>0) {
                $rattachement_id = $rattachements['id'];
                $rattachement = $rattachements['prenom']." ".$rattachements['nom'] ;
            }
            if ($operateur->getAffecterDossier() === 0 )
                $affecterDossier = "Non";
            else
                $affecterDossier = "Oui";

            $rows[] = array(
                'id' => $operateur->getId(),
                'cell' => array(
                    'matricule'=> $operateur->getMatricule(),
                    'nom'=>$operateur->getNom(),
                    'prenom'=>$operateur->getPrenom(),
                    //'adresse'=>$operateur->getAdresse(),
                    'telephone'=>$operateur->getTel(),
                    'sexe'=>$operateur->getSexe(),
                    'login'=>$operateur->getLogin(),
                    'password'=>$operateur->getPassword(),
                    'date_entree'=>$date_entree,
                    'date_sortie'=>$date_sortie,
                    'poste-id'=>$poste_id,
                    'poste'=>$poste,
                    /*'role-id'=>$role_id,
                    'role'=>$role,*/
                    'rattachement-id'=>$rattachement_id,
                    'rattachement'=>$rattachement,
                    'affecter-dossier'=> $affecterDossier,
                    'reinitialiser'=>'<i class="fa fa-eraser icon-action js-erase-pwd-personnel" title="Reinitialiser Pwd"></i>',
                    'action'=>'<i class="fa fa-save icon-action js-save-button js-save-personnel" title="Enregister"></i><i class="fa fa-trash icon-action js-delete-personnel" title="Supprimer"></i>',
                )
            );
        }
        $liste = array(
            'rows' => $rows,
        );
        return new JsonResponse($liste);

    }

    /**
     * Corréspondance utilisateur entre intranet et picdata
     *
     */
    public function picDataLoginAction()
    {
        return $this->render('ParametreBundle:Organisation/Personnel:param-picdata-login.html.twig');
    }

    /**
     * @return JsonResponse
     */
    public function operateursUtilisateurAction()
    {
        $couples = $this->getDoctrine()->getRepository('AppBundle:OperateurUtilisateur')
            ->getAllCouple();
        return new JsonResponse($this->getCouple($couples));
    }

    /**
     * @param array $couples
     * @return array
     */
    private function getCouple($couples = [])
    {
        $results = [];
        foreach ($couples as $couple)
        {
            /** @var Operateur $operateur */
            $operateur = $couple->op;

            /** @var OperateurUtilisateur $operateurUtilisateur */
            $operateurUtilisateur = $couple->uo;

            $utilisateurNom = '';
            if ($operateurUtilisateur)
            {
                $utilisateur = $operateurUtilisateur->getUtilisateur();
                $utilisateurNom = $utilisateur->getEmail();

                $utilisateurNom .= ' ('.$utilisateur->getNom().' '.$utilisateur->getPrenom().')';
            }

            $results[] = (object)
            [
                'id' => $operateur->getId(),
                'nom' => $operateur->getNom() . ' ' . $operateur->getPrenom(),
                'login' => $operateur->getLogin(),
                'ou_id' => $operateurUtilisateur ? $operateurUtilisateur->getId() : 0,
                'utilisateur' => $utilisateurNom
            ];
        }

        return $results;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function operateurUtilisateurEditAction(Request $request)
    {
        /** @var Operateur $operateur */
        $operateur = $this->getDoctrine()->getRepository('AppBundle:Operateur')
            ->find($request->request->get('operateur'));

        $operateurUtilisateur = $this->getDoctrine()->getRepository('AppBundle:OperateurUtilisateur')
            ->getUtilisateurOperateur($operateur);

        $utilisateurId = 0;
        if ($operateurUtilisateur) $utilisateurId = $operateurUtilisateur->getUtilisateur()->getId();

        $utilisateurs = $this->getDoctrine()->getRepository('AppBundle:Utilisateur')
            ->getAllUtilisateurs();

        return $this->render('@Parametre/Organisation/Personnel/param-picdata-edit.html.twig',[
            'operateur' => $operateur,
            'utilisateurId' => $utilisateurId,
            'utilisateurs' => $utilisateurs
        ]);
    }

    public function operateurUtilisateurSaveAction(Request $request)
    {
        $operateur = $this->getDoctrine()->getRepository('AppBundle:Operateur')
            ->find($request->request->get('operateur'));
        $utilisateur = $this->getDoctrine()->getRepository('AppBundle:Utilisateur')
            ->find($request->request->get('utilisateur'));

        $operateurUtilisateur = $this->getDoctrine()->getRepository('AppBundle:OperateurUtilisateur')
            ->getUtilisateurOperateur($operateur);

        $em = $this->getDoctrine()->getManager();
        if ($operateurUtilisateur)
        {
            if ($utilisateur) $operateurUtilisateur->setUtilisateur($utilisateur);
            else $em->remove($operateurUtilisateur);
        }
        else
        {
            if ($utilisateur)
            {
                $operateurUtilisateur = new OperateurUtilisateur();
                $operateurUtilisateur
                    ->setOperateur($operateur)
                    ->setUtilisateur($utilisateur);
                $em->persist($operateurUtilisateur);
            }
        }
        $em->flush();

        $couples = $this->getCouple($this->getDoctrine()->getRepository('AppBundle:OperateurUtilisateur')
            ->getAllCouple($operateur));

        return new JsonResponse($couples[0]);
    }

    /**
     * Index Operateur + ADD NEW + EDIT
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function selectOperateurAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $idOperat = $request->request
                ->get('idopera');
            $idUtilis = $request->request
                ->get('idutilis');
            $status = array();
            $em = $this->getDoctrine()
                ->getManager();
            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find($idOperat);
            $utilisateur = $this->getDoctrine()
                ->getRepository('AppBundle:Utilisateur')
                ->find($idUtilis);

            if ($operateur) {
                /** @var OperateurUtilisateur $verifOpera */
                $verifOpera = $this->getDoctrine()
                    ->getRepository('AppBundle:OperateurUtilisateur')
                    ->findOneBy(array('operateur' => $idOperat));

                if ($verifOpera) {
                    if ($utilisateur) {
                        $verifOpera->setUtilisateur($utilisateur);
                        $em->flush();
                    }
                    $status = array('etat' => 'update');
                }else{
                    $operaUtilis = new OperateurUtilisateur();

                    $operaUtilis->setOperateur($operateur);
                    $operaUtilis->setUtilisateur($utilisateur);
                    $em->persist($operaUtilis);
                    $em->flush();
                    $status = array('etat' => 'new');
                }
            }
            return new JsonResponse($status);
        }
        throw new BadRequestHttpException("Method not allowed.");
    }

    /**
     * Verification Operateur Utilisateur
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function verifOperaUtilisAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $idOperat = $request->request
                ->get('idopera');

            $status = array();
            $operateur = $this->getDoctrine()
                ->getRepository('AppBundle:Operateur')
                ->find($idOperat);
            if ($operateur) {
                /** @var OperateurUtilisateur $verifOpera */
                $verifOpera = $this->getDoctrine()
                    ->getRepository('AppBundle:OperateurUtilisateur')
                    ->findOneBy(array('operateur' => $idOperat));
                if ($verifOpera) {
                    $idUtilis =  $verifOpera->getUtilisateur()->getId();
                    $status = array('etat' => 'exist', 'idUtil' => $idUtilis);
                }else {
                    $status = array('etat' => 'notfound');
                }
            }
            return new JsonResponse($status);
        }
        throw new BadRequestHttpException("Method not allowed.");
    }

    /**
     * Reinitialiser mot de pass operateur
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function reinitialisePwdAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $idOperat = $request->request
                    ->get('idopera');
                $status = array();
                $em = $this->getDoctrine()
                    ->getManager();
                /** @var Operateur $operateur */
                $operateur = $this->getDoctrine()
                    ->getRepository('AppBundle:Operateur')
                    ->find($idOperat);
                if ($operateur) {
                    $operateur->setPassword('scriptura');
                    $operateur->setLastLogin(NULL);
                    $em->flush();
                    $status = array('etat' => 'done',);
                    return new JsonResponse(json_encode($status));
                }else{
                    $status = array('etat' => 'notdone',);
                    return new JsonResponse(json_encode($status));
                }
            } catch ( \Exception $ex) {
                $status = array(
                    'erreur' => true,
                    'erreur_text' => $ex->getMessage(),
                );
                return new JsonResponse(json_encode($status));
            }
        }
        throw new BadRequestHttpException("Method not allowed.");
    }

    public function accesMenuAction(){
        $listeUsers = [];
        $liste_postes = $this->getDoctrine()
                                ->getRepository('AppBundle:Organisation')
                                ->findAll();

        foreach ($liste_postes as $k => $v) {
            $listeUsers[$v->getId()]['listes'] = $this->getDoctrine()->getRepository('AppBundle:Operateur')->operateurActifByOrganisation($v);
            $listeUsers[$v->getId()]['nb'] = count($listeUsers[$v->getId()]['listes']);
        }

        $listes_operateurs = $this->getDoctrine()
                                    ->getRepository('AppBundle:Operateur')
                                    ->findBy(array(
                                        'accesOperateur' => $this->getUser()->getAccesOperateur()
                                    ));

        return $this->render('ParametreBundle:Personnel:acces-menu.html.twig', array(
            'liste_poste' => $liste_postes,
            'liste_operateur' => $listes_operateurs,
            'liste_user' => $listeUsers,
        ));
    }

    public function roleAccesMenuEditAction(Request $request, Organisation $poste){
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                try {
                     $menus_id = $request->request->get('menus');
                     $this->getDoctrine()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->removeRoleMenus($poste);
                     if ($menus_id && is_array($menus_id)) {
                        $em = $this->getDoctrine()
                                   ->getManager();
                        foreach ($menus_id as $menu_id) {
                            $menu = $this->getDoctrine()
                                         ->getRepository('AppBundle:MenuIntranet')
                                         ->find($menu_id['menu']);
                            if ($menu) {
                                $poste_menu = new MenuIntranetPoste();
                                $poste_menu
                                    ->setOrganisation($poste)
                                    ->setMenuIntranet($menu);
                                $em->persist($poste_menu);
                            }
                        }
                        $em->flush();
                    }
                    $menus = $this->getDoctrine()
                                      ->getRepository('AppBundle:MenuIntranet')
                                      ->getMenuParPoste($poste);

                    $encoder = new JsonEncoder();
                    $normalizer = new ObjectNormalizer();
                    $normalizer->setCircularReferenceHandler(function ($object) {
                        return $object->getId();
                    });
                    $serializer = new Serializer(array($normalizer), array($encoder));

                    $data = [
                        'erreur' => false,
                        'menus' => $menus,
                    ];
                    return new JsonResponse($serializer->serialize($data, 'json'));
                } catch (\Exception $ex) {
                    $data = [
                        'erreur' => true,
                        'erreur_text' => "Une erreur est survenue.",
                    ];
                    return new JsonResponse(json_encode($data));
                }
            } else {
                throw new AccessDeniedHttpException('Accès refusé.');
            }
        } else {
            throw new AccessDeniedHttpException('Accès refusé.');
        }
    }

    public function accesOperateurMenuAction(Request $request, Organisation $poste)
    {
        if ($request->isXmlHttpRequest()) {
            $menus = $this->getDoctrine()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->getMenuParPoste($poste);

            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $serializer = new Serializer(array($normalizer), array($encoder));
            return new Response($serializer->serialize($menus, 'json'));
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function operateurMenuAction(Request $request, $operateur)
    {
        if ($request->isXmlHttpRequest()) {
            $operateur = $this->getDoctrine()
                                ->getRepository('AppBundle:Operateur')
                                ->find($operateur);
            if ($operateur) {
                $menus = $this->getDoctrine()
                              ->getRepository('AppBundle:MenuIntranet')
                              ->getMenuOperateur($operateur);

                $menusPoste = $this->getDoctrine()
                              ->getRepository('AppBundle:MenuIntranet')
                              ->getMenuParPoste($operateur->getOrganisation());

                $encoder = new JsonEncoder();
                $normalizer = new ObjectNormalizer();
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $serializer = new Serializer(array($normalizer), array($encoder));
                $data = [
                            'menusRefuser' => $menusPoste,
                            'menus' => $menus,
                        ];
                return new JsonResponse($serializer->serialize($data, 'json'));
            } else {
                throw new NotFoundHttpException("Utilisateur introuvable.");
            }
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function operateurMenuEditAction(Request $request, $operateur)
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                try {
                    $operateur = $this->getDoctrine()
                                        ->getRepository('AppBundle:Operateur')
                                        ->find($operateur);
                    if ($operateur) {
                        $menus_id = $request->request->get('menus');
                        $this->getDoctrine()
                             ->getRepository('AppBundle:MenuIntranet')
                             ->removeMenuOperateur($operateur);
                        if ($menus_id && is_array($menus_id)) {
                            $em = $this->getDoctrine()
                                       ->getManager();
                            foreach ($menus_id as $menu_id) {
                                $menu = $this->getDoctrine()
                                             ->getRepository('AppBundle:MenuIntranet')
                                             ->find($menu_id['menu']);
                                if ($menu) {
                                    $operateur_menu = new MenuIntranetOperateur();
                                    $operateur_menu
                                        ->setOperateur($operateur)
                                        ->setMenuIntranet($menu)
                                        ->setAccessOperateur($operateur->getAccesOperateur());
                                    $em->persist($operateur_menu);
                                }
                            }
                            $em->flush();
                        }
                        $menus = $this->getDoctrine()
                                      ->getRepository('AppBundle:MenuIntranet')
                                      ->getMenuOperateur($operateur);

                        $encoder = new JsonEncoder();
                        $normalizer = new ObjectNormalizer();
                        $normalizer->setCircularReferenceHandler(function ($object) {
                            return $object->getId();
                        });
                        $serializer = new Serializer(array($normalizer), array($encoder));

                        $data = [
                            'erreur' => false,
                            'menus' => $menus,
                        ];
                        return new JsonResponse($serializer->serialize($data, 'json'));
                    } else {
                        throw new NotFoundHttpException("Utilisateur introuvable.");
                    }
                } catch (\Exception $ex) {
                    $data = [
                        'erreur' => true,
                        'erreur_text' => "Une erreur est survenue.",
                    ];
                    return new JsonResponse(json_encode($data));
                }
            } else {
                throw new AccessDeniedHttpException('Accès refusé.');
            }
        } else {
            throw new AccessDeniedHttpException('Accès refusé.');
        }
    }

    public function accesOperateurParUserMenuAction($poste, $user)
    {
        if ($request->isXmlHttpRequest()) {
            $menus = $this->getDoctrine()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->getMenuParPoste($poste);

            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $serializer = new Serializer(array($normalizer), array($encoder));
            return new Response($serializer->serialize($menus, 'json'));
        } else {
            throw new AccessDeniedHttpException("Accès refusé.");
        }
    }

    public function accesOperateurParUserDefautMenuAction($operateur)
    {
        $operateur = $this->getDoctrine()
                          ->getRepository('AppBundle:Operateur')
                          ->find($operateur);
        if ($operateur) {
            /*$this->getDoctrine()
                 ->getRepository('AppBundle:MenuIntranet')
                 ->removeMenuOperateur($operateur);*/
            $menusPoste = $this->getDoctrine()
                               ->getRepository('AppBundle:MenuIntranet')
                               ->getMenuParPoste($operateur->getOrganisation());
            $em = $this->getDoctrine()
                       ->getManager();
            foreach ($menusPoste as $m) {
                $menu = $m->getMenuIntranet();
                if ($menu) {
                    $operateur_menu = new MenuIntranetOperateur();
                    $operateur_menu
                        ->setOperateur($operateur)
                        ->setMenuIntranet($menu)
                        ->setAccessOperateur($operateur->getAccesOperateur());
                    $em->persist($operateur_menu);
                }
            }
            $em->flush();
            $menus = $this->getDoctrine()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->getMenuOperateur($operateur);

            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $serializer = new Serializer(array($normalizer), array($encoder));
            $data = [
                'menusRefuser' => $menusPoste,
                'menus' => $menus,
            ];
            return new Response($serializer->serialize($data, 'json'));
        }else {
            throw new AccessDeniedHttpException("Utilisateur introuvable.");
        }
    }
}
