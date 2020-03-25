<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 20/03/2019
 * Time: 09:46
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;

class ProcessusMenuIntranetRepository extends EntityRepository
{
    public function getAll()
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id, processus_id, menu_intranet_id, 
                  (SELECT libelle FROM menu_intranet WHERE id=pm.menu_intranet_id) as libMenu,
                  (SELECT menu_intranet_id FROM menu_intranet WHERE id=pm.menu_intranet_id) as parentMenu FROM processus_menu_intranet pm
                  ORDER BY processus_id, id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        $menus = $prep->fetchAll();
        $menusParProcessus = array();
        $menusTmp = array();
        $proId = 0;
        foreach ($menus as $menu ) {
                $menusParProcessus[$menu->processus_id][] = array(
                    'id' => $menu->menu_intranet_id,
                    'nom'=>$menu->libMenu,
                    'parent' => ($menu->parentMenu == null)? 0: $menu->parentMenu,);
        }

        return $menusParProcessus;
    }
}