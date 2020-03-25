<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $user_app = $this->get('user_app_check')->check();
        $parents = $this->getDoctrine()
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

        return $this->render('AppBundle:index:index.html.twig', array(
            'menus_parents' => $parents,
            'menus_childs' => $menu_complet,
            'menus_id' => $menus_id
        ));
    }

    public function userAppMissingAction()
    {
        $this->get('security.token_storage')->setToken(null);
        return $this->render('@App/index/user-app-missing.html.twig');
    }

    public function userAppMismatchAction()
    {
        $this->get('security.token_storage')->setToken(null);
        return $this->render('@App/index/user-app.mismatch.html.twig');
    }
}
