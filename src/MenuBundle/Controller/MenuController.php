<?php

namespace MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Menu;

class MenuController extends Controller
{
    //MENU GAUCHE
    public function menuLeftAction()
    {
//        $user = $this->getUser();
//        $em = $this->getDoctrine()->getManager();
//
//        $menu_parents = $em->getRepository('AppBundle:Menu')->getMenuParent($user);
//        foreach($menu_parents as &$menu_parent)
//        {
//            $menu_childs = $em->getRepository('AppBundle:Menu')->getMenuChild($menu_parent,$user);
//            $menu_parent->setChild($menu_childs);
//
//            $array_temp = $menu_parent->getChild();
//            if($array_temp != null)
//            {
//                foreach ($array_temp as &$menu_child)
//                {
//                    $menu_childss = $em->getRepository('AppBundle:Menu')->getMenuChild($menu_child, $user);
//                    $menu_child->setChild($menu_childss);
//
//                    $array_temp_2 = $menu_child->getChild();
//                    if($array_temp_2 != null)
//                    {
//                        foreach ($array_temp_2 as &$menu_child2)
//                        {
//                            $menu_childss2 = $em->getRepository('AppBundle:Menu')->getMenuChild($menu_child2, $user);
//                            $menu_child2->setChild($menu_childss2);
//
//                            $array_temp_3 = $menu_child2->getChild();
//                            if($array_temp_3 != null)
//                            {
//                                foreach($array_temp_3 as &$menu_child3)
//                                {
//                                    $menu_childss3 = $em->getRepository('AppBundle:Menu')->getMenuChild($menu_child3, $user);
//                                    $menu_child3->setChild($menu_childss3);
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }

        return $this->render('MenuBundle:Default:menu-left.html.twig');
    }

    //MENU HAUT
    public function menuTopAction()
    {
        return $this->render('MenuBundle:Default:menu-top.html.twig',array());    
    }
}
