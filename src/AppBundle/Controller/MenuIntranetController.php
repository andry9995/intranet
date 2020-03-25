<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

class MenuIntranetController extends Controller
{
    public function getMenuIntranetLeftAction()
    {
        $requestStack = $this->container->get('request_stack');
        $masterRequest = $requestStack->getMasterRequest();

        if($masterRequest){
            $currentRoute = $masterRequest->attributes->get('_route');
            $parent = $this->getDoctrine()
                           ->getRepository('AppBundle:MenuIntranet')
                           ->getMenuParentByLien($currentRoute);

            if(count($parent[0]->getMenuIntranet()) > 0){
                while(count($parent[0]->getMenuIntranet()) != 0) {
                    $parent = $this->getDoctrine()
                                   ->getRepository('AppBundle:MenuIntranet')
                                   ->getMenuParentById($parent[0]->getMenuIntranet()->getId());
                }
            }

            $parent = $parent[0];

            $array_child3 = array();
            $menu_complet = $this->getDoctrine()
                            ->getRepository('AppBundle:MenuIntranet')
                            ->getMenuIntranet($this->getUser());
            $menus_id = [];
            if ($this->getUser()->getAccesOperateur()->getCode() == 'ROLE_SUPER_ADMIN') {
                $menus = $this->getDoctrine()
                              ->getRepository('AppBundle:MenuIntranet')
                              ->findAll();
                foreach ($menus as $menu) {
                    $menus_id[] = $menu->getId();
                }
            }else{
                $menus = $this->getDoctrine()
                              ->getRepository('AppBundle:MenuIntranet')
                              ->getMenuOperateur($this->getUser());
                foreach ( $menus as $menu ) {
                    $menus_id[] = $menu->getMenuIntranet()->getId();
                }
            }

            return $this->render('AppBundle:index:menu-left-intranet.html.twig', array(
                'menus' => $parent->getChild(),
                'menus_id' => $menus_id
            ));
        }
        return new JsonResponse('Erreur');
    }

    public function getMenuListAction()
    {
        $liste_parent_menu = $this->getDoctrine()
                                  ->getRepository('AppBundle:MenuIntranet')
                                  ->findBy(array('menuIntranet' => null));

        $menu_complet = $this->getDoctrine()
                             ->getRepository('AppBundle:MenuIntranet')
                             ->getMenuIntranet($this->getUser());

        $menus_id = [];

        if ($this->getUser()->getAccesOperateur()->getCode() == 'ROLE_SUPER_ADMIN') {
            $menus = $this->getDoctrine()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->findAll();
            foreach ($menus as $menu) {
                $menus_id[] = $menu->getId();
            }
        }else{
            $menus = $this->getDoctrine()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->getMenuOperateur($this->getUser());
            foreach ( $menus as $menu ) {
                $menus_id[] = $menu->getMenuIntranet()->getId();
            }
        }
        return $this->render('AppBundle:index:liste-menu.html.twig', array(
            'liste_menu' => $menu_complet,
            'menus_id' => $menus_id
        ));
    }

    public function regroupeMenuChoisiAction(Request $request)
    {
        $menuChoisis = $request->request->get('menus');
        $processId = $request->request->get('processId');
        $menus = $this->getDoctrine()
            ->getRepository('AppBundle:MenuIntranet')
            ->getAll();

        $menuGroupes = array();
        if (count($menuChoisis) > 0) {
            foreach ($menuChoisis as $menuChoisi) {
                foreach ($menus as $menu) {
                    if (intval($menuChoisi) == intval($menu->id)) {
                        //Rechercher parent si existe
                        $parent = 0;
                        foreach ($menus as $child) {
                            if ($child->id == $menu->menu_intranet_id) {
                                $parent = $child->id;
                                break;
                            }
                        }
                        if ($parent != 0)
                            array_push($menuGroupes, array('id' => $menu->id, 'nom' => $menu->libelle, 'parent' => $parent));
                        else
                            array_push($menuGroupes, array('id' => $menu->id, 'nom' => $menu->libelle, 'parent' => 0));
                    }
                }
            }
        }
        return $this->render('ParametreBundle:Organisation:menus_choisis_processus.html.twig', array(
            'menusChoisis' => $menuGroupes,
            'processId' => $processId,
        ));
    }
}
