<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Menu;

class MenuRepository extends EntityRepository
{
    public function getParent($menu)
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.admin = :admin')
            ->andWhere('m.menu IS NULL')
            ->setParameter('admin', $menu)
            ->orderBy('m.rang', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function getChilds(Menu $parent, $menu)
    {
        $query = $this->createQueryBuilder('m')
                    ->where('m.admin = :admin')
                    ->andWhere('m.menu = :parent')
                    ->setParameter('admin', $menu)
                    ->setParameter('parent', $parent)
                    ->orderBy('m.rang', 'ASC')
                    ->getQuery();

        return $query->getResult();
    }

    //get all menu parent
    public function getMenuParent($user)
    {
        $mu_disabled = $this->getMenuDisabled($user);
        $admin = ($user->getAccesUtilisateur()->getCode() == 'ROLE_SUPER_ADMIN' || $user->getAccesUtilisateur()->getCode() == 'ROLE_ADMIN') ? 1 : 0;

        if(empty($mu_disabled))
            $query = $this->getEntityManager()->getRepository('AppBundle:Menu')->createQueryBuilder('m')
                    ->where('m.menu IS NULL')
                    ->andWhere('m.admin = :admin')
                    ->setParameter('admin',$admin)
                    ->orderBy('m.rang','ASC')
                    ->getQuery();        
        else
            $query = $this->getEntityManager()->getRepository('AppBundle:Menu')->createQueryBuilder('m')
                    ->where('m NOT IN (:mu_disabled)')
                    ->andWhere('m.menu IS NULL')
                    ->andWhere('m.admin = :admin')
                    ->setParameter('mu_disabled',$mu_disabled)
                    ->setParameter('admin',$admin)
                    ->orderBy('m.rang','ASC')
                    ->getQuery();

        return $query->getResult();
    }

    //get menu child
    public function getMenuChild(Menu $parent, $user)
    {
        $mu_disabled = $this->getMenuDisabled($user);
        $admin = ($user->getAccesUtilisateur()->getCode() == 'ROLE_SUPER_ADMIN' || $user->getAccesUtilisateur()->getCode() == 'ROLE_ADMIN') ? 1 : 0;

        if(empty($mu_disabled))
            $query = $this->getEntityManager()->getRepository('AppBundle:Menu')->createQueryBuilder('m')
                    ->where('m.menu = :parent')
                    ->andWhere('m.admin = :admin')
                    ->setParameter('parent',$parent)
                    ->setParameter('admin',$admin)
                    ->orderBy('m.rang','ASC')
                    ->getQuery();
        else
            $query = $this->getEntityManager()->getRepository('AppBundle:Menu')->createQueryBuilder('m')
                    ->where('m.menu = :parent')
                    ->andWhere('m NOT IN (:mu_disabled)')
                    ->andWhere('m.admin = :admin')
                    ->setParameter('mu_disabled',$mu_disabled)
                    ->setParameter('parent',$parent)
                    ->setParameter('admin',$admin)
                    ->orderBy('m.rang','ASC')
                    ->getQuery();

        return $query->getResult();        
    }

    //get menu disabled
    public function getMenuDisabled($user)
    {
        $query = $this->getEntityManager()->getRepository('AppBundle:MenuUtilisateur')->createQueryBuilder('mu')
                ->where('mu.utilisateur = :utilisateur')
                ->setParameter('utilisateur',$user)
                ->getQuery();
        $mu_temps = $query->getResult();
        $mu_disabled = array();
        foreach($mu_temps as &$mu_temp)
        {
            $mu_disabled[] = $mu_temp->getMenu()->setIdMenuUtilisateur($mu_temp->getId());
        }

        return $mu_disabled;
    }

    //get all parent
    public function getAllParent($user)
    {
        $mu_disabled = $this->getMenuDisabled($user);
        $admin = ($user->getAccesUtilisateur()->getCode() == 'ROLE_SUPER_ADMIN' || $user->getAccesUtilisateur()->getCode() == 'ROLE_ADMIN') ? 1 : 0;

        $query = $this->getEntityManager()->getRepository('AppBundle:Menu')->createQueryBuilder('m')
                ->where('m.menu IS NULL')
                ->andWhere('m.admin = :admin')
                ->setParameter('admin',$admin)
                ->orderBy('m.rang','ASC')
                ->getQuery();

        $menus = $query->getResult();

        foreach($menus as &$menu)
            if(in_array($menu,$mu_disabled)) $menu->setActive(false);
            else $menu->setActive(true);

        return $menus;        
    }

    //get all chlid
    public function getAllChild(Menu $parent,$user)
    {
        $mu_disabled = $this->getMenuDisabled($user);
        $admin = ($user->getAccesUtilisateur()->getCode() == 'ROLE_SUPER_ADMIN' || $user->getAccesUtilisateur()->getCode() == 'ROLE_ADMIN') ? 1 : 0;

        $query = $this->getEntityManager()->getRepository('AppBundle:Menu')->createQueryBuilder('m')
                ->andWhere('m.admin = :admin')
                ->andWhere('m.menu = :parent')
                ->setParameter('admin',$admin)
                ->setParameter('parent',$parent)
                ->orderBy('m.rang','ASC')
                ->getQuery();

        $menus = $query->getResult();

        foreach($menus as &$menu)
            if(in_array($menu,$mu_disabled)) $menu->setActive(false);
            else $menu->setActive(true);

        return $menus;        
    }    
}