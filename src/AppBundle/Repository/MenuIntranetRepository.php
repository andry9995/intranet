<?php
/**
 * Created by PhpStorm.
 * User: Dinoh
 * Date: 06/03/2019
 * Time: 15:00
 */

namespace AppBundle\Repository;

use AppBundle\Entity\MenuIntranet;
use AppBundle\Entity\Operateur;
use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;

class MenuIntranetRepository extends EntityRepository
{
    public function getMenuParentByLien($lien)
    {
        $parent = $this->createQueryBuilder('mi')
                       ->where('mi.lien = :lien')
                       ->setParameter('lien', $lien)
                       ->orderBy('mi.rang', 'ASC')
                       ->getQuery()
                       ->getResult();

        return $parent;
    }

    public function getMenuParentById($parent_id)
    {
        $parent = $this->createQueryBuilder('mi')
                       ->where('mi.id = :id')
                       ->setParameter('id', $parent_id)
                       ->orderBy('mi.rang', 'ASC')
                       ->getQuery()
                       ->getResult();

        return $parent;
    }

    public function getMenuChild(MenuIntranet $parent)
    {
        $query = $this->createQueryBuilder('mi')
                      ->where('mi.menuIntranet = :menu_intranet')
                      ->setParameter('menu_intranet', $parent)
                      ->orderBy('mi.rang', 'ASC')
                      ->getQuery();

        return $query->getResult();
    }

    public function getMenuChildByOperateurParentId(Operateur $operateur, MenuIntranet $parent)
    {
        $parents = $this->getEntityManager()
                        ->getRepository('AppBundle:MenuIntranetOperateur')
                        ->createQueryBuilder('menu_intranet_operateur')
                        ->select('menu_intranet_operateur')
                        ->innerJoin('menu_intranet_operateur.operateur', 'operateur')
                        ->addSelect('operateur')
                        ->where('operateur = :operateur_id')
                        ->innerJoin('menu_intranet_operateur.menuIntranet', 'menu_intranet')
                        ->addSelect('menu_intranet')
                        ->andWhere('menu_intranet.menuIntranet = :menuIntra')
                        ->setParameters(array(
                            'operateur_id' => $operateur,
                            'menuIntra' => $parent
                        ))
                        ->orderBy('menu_intranet.rang', 'ASC')
                        ->getQuery()
                        ->getResult();
        if (count($parents) == 0) {
            $parents = $this->getEntityManager()
                            ->getRepository('AppBundle:MenuIntranetPoste')
                            ->createQueryBuilder('menuParPoste')
                            ->select('menuParPoste')
                            ->innerJoin('menuParPoste.menuIntranet', 'menu_intranet')
                            ->addSelect('menu_intranet')
                            ->innerJoin('menuParPoste.organisation', 'organisation')
                            ->addSelect('organisation')
                            ->where('menuParPoste.organisation = :organisation')
                            ->andWhere('menu_intranet.menuIntranet = :menuIntra')
                            ->setParameters(array(
                                'organisation' => $operateur->getOrganisation(),
                                'menuIntra' => $parent
                            ))
                            ->orderBy('menu_intranet.rang', 'ASC')
                            ->getQuery()
                            ->getResult();
        }

        return $parents;
    }

    public function getMenuIntranet(Operateur $operateur, $parents = false)
    {
        if (!$parents) {
            $parents = $this->getEntityManager()
                ->getRepository('AppBundle:MenuIntranetOperateur')
                ->createQueryBuilder('menu_intranet_operateur')
                ->select('menu_intranet_operateur')
                ->innerJoin('menu_intranet_operateur.operateur', 'operateur')
                ->addSelect('operateur')
                ->innerJoin('menu_intranet_operateur.menuIntranet', 'menu_intranet')
                ->addSelect('menu_intranet')
                ->where('menu_intranet.menuIntranet IS NULL')
                ->andWhere('operateur = :operateur')
                ->setParameter('operateur', $operateur)
                ->orderBy('menu_intranet.rang', 'ASC')
                ->getQuery()
                ->getResult();
            if (count($parents) == 0) {
                $parents = $this->getEntityManager()
                    ->getRepository('AppBundle:MenuIntranetPoste')
                    ->createQueryBuilder('menuParPoste')
                    ->select('menuParPoste')
                    ->innerJoin('menuParPoste.menuIntranet', 'menu_intranet')
                    ->addSelect('menu_intranet')
                    ->innerJoin('menuParPoste.organisation', 'organisation')
                    ->addSelect('organisation')
                    ->where('menu_intranet.menuIntranet IS NULL')
                    ->orderBy('menu_intranet.rang', 'ASC')
                    ->getQuery()
                    ->getResult();
            }
            $parent_existe = false;
        } else {
            $parent_existe = true;
        }

        $liste_menus = [];
        if (count($parents) == 0) {
            return [];
        } else {
            foreach ($parents as &$parent) {
                $level1 = (!$parent_existe) ? $parent->getMenuIntranet() : $parent;
                $liste_menus[] = $level1;
                $childs = $this->getEntityManager()
                    ->getRepository('AppBundle:MenuIntranet')
                    ->getMenuChild($level1);
                if (count($childs) > 0) {
                    $level1->setChild($childs);
                    foreach ($childs as &$child) {
                        $childs2 = $this->getEntityManager()
                            ->getRepository('AppBundle:MenuIntranet')
                            ->getMenuChild($child);
                        if (count($childs2) > 0) {
                            $child->setChild($childs2);
                            foreach ($childs2 as &$child2) {
                                $childs3 = $this->getEntityManager()
                                    ->getRepository('AppBundle:MenuIntranet')
                                    ->getMenuChild($child2);
                                if (count($childs3) > 0) {
                                    $child2->setChild($childs3);
                                    foreach ($childs3 as &$child3) {
                                        $childs4 = $this->getEntityManager()
                                            ->getRepository('AppBundle:MenuIntranet')
                                            ->getMenuChild($child3);
                                        if (count($childs4) > 0) {
                                            $child3->setChild($childs4);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $liste_menus;
        }
    }

    public function removeRoleMenus(Organisation $organisation)
    {
        $em = $this->getEntityManager();
        $menus = $this->getEntityManager()
                      ->getRepository('AppBundle:MenuIntranet')
                      ->getMenuParPoste($organisation);
        if(count($menus) > 0){
            foreach ($menus as $menu) {
                $em->remove($menu);
            }
            $em->flush();
        }
        return true;
    }

    public function getMenuParPoste(Organisation $organisation)
    {
        $menus = $this->getEntityManager()
                      ->getRepository('AppBundle:MenuIntranetPoste')
                      ->createQueryBuilder('menuParPoste')
                      ->select('menuParPoste')
                      ->innerJoin('menuParPoste.organisation', 'organisation')
                      ->addSelect('organisation')
                      ->where('organisation = :organisation')
                      ->innerJoin('menuParPoste.menuIntranet', 'menu')
                      ->addSelect('menu')
                      ->setParameters(array(
                          'organisation' => $organisation
                      ))
                      ->orderBy('menu.rang')
                      ->getQuery()
                      ->getResult();
        return $menus;
    }

    public function getMenuOperateur(Operateur $operateur)
    {
        $menus = $this->getEntityManager()
                      ->getRepository('AppBundle:MenuIntranetOperateur')
                      ->createQueryBuilder('menu_operateur')
                      ->select('menu_operateur')
                      ->innerJoin('menu_operateur.menuIntranet', 'menu')
                      ->addSelect('menu')
                      ->innerJoin('menu_operateur.operateur', 'operateur')
                      ->addSelect('operateur')
                      ->where('operateur = :operateur')
                      ->setParameters(array(
                          'operateur' => $operateur,
                      ))
                      ->getQuery()
                      ->getResult();

        if (count($menus) == 0) {
            $menus = $this->getEntityManager()
                          ->getRepository('AppBundle:MenuIntranet')
                          ->getMenuParPoste($operateur->getOrganisation());
        }

        return $menus;
    }

    public function removeMenuOperateur(Operateur $operateur)
    {
        $menus = $menus = $this->getEntityManager()
                               ->getRepository('AppBundle:MenuIntranetOperateur')
                               ->createQueryBuilder('menu_operateur')
                               ->select('menu_operateur')
                               ->innerJoin('menu_operateur.menuIntranet', 'menu')
                               ->addSelect('menu')
                               ->innerJoin('menu_operateur.operateur', 'operateur')
                               ->addSelect('operateur')
                               ->where('operateur = :operateur')
                               ->setParameters(array(
                                   'operateur' => $operateur,
                               ))
                               ->getQuery()
                               ->getResult();

        if (count($menus) > 0) {
            $em = $this->getEntityManager();
            foreach ($menus as $menu) {
                $em->remove($menu);
            }
            $em->flush();
        }
    }

    public function getMenuParentOperateur(Operateur $operateur)
    {
        $menus = $this->getEntityManager()
                      ->getRepository('AppBundle:MenuIntranetOperateur')
                      ->createQueryBuilder('menu_operateur')
                      ->select('menu_operateur')
                      ->innerJoin('menu_operateur.menuIntranet', 'menu')
                      ->addSelect('menu')
                      ->innerJoin('menu_operateur.operateur', 'operateur')
                      ->addSelect('operateur')
                      ->where('operateur = :operateur')
                      ->andWhere('menu.menuIntranet IS NULL')
                      ->setParameters(array(
                          'operateur' => $operateur,
                      ))
                      ->orderBy('menu.rang', 'ASC')
                      ->getQuery()
                      ->getResult();
        if (count($menus) == 0) {
            $menus = $this->getEntityManager()
                          ->getRepository('AppBundle:MenuIntranetPoste')
                          ->createQueryBuilder('menuParPoste')
                          ->select('menuParPoste')
                          ->innerJoin('menuParPoste.organisation', 'organisation')
                          ->addSelect('organisation')
                          ->where('organisation = :organisation')
                          ->innerJoin('menuParPoste.menuIntranet', 'menu')
                          ->addSelect('menu')
                          ->andWhere('menu.menuIntranet IS NULL')
                          ->setParameters(array(
                              'organisation' => $operateur->Organisation(),
                          ))
                          ->orderBy('menu.rang', 'ASC')
                          ->getQuery()
                          ->getResult();
        }

        $parents = [];
        foreach ($menus as $menu)
        {
            $parents[] = $menu->getMenuIntranet();
        }
        return $parents;
    }

    function getMenuParentOnly()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, libelle, rang FROM menu_intranet WHERE menu_intranet_id =:menuId ORDER BY rang";
        $prep = $pdo->prepare($query);
        $prep->execute(array('menuId' => null));
        $menu = $prep->fetchAll();
        return $menu;
    }
    function getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, libelle, menu_intranet_id, rang FROM menu_intranet";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $menu = $prep->fetchAll();
        return $menu;
    }
}