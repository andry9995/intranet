<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 10/08/2018
 * Time: 13:16
 */

namespace BanqueBundle\Controller;


class functions
{
    public static function getParentsChilds($comptes, $doctrine)
    {
        $pcgsChilds = [];
        $pcgsParents = [];
        $pcgsObjects = [];
        foreach ($comptes as $pcg)
        {
            //$pcg = new Pcc();
            $compte = $pcg->getCompte();
            $pcgsChilds[$compte] = [];
            $pcgsObjects[$compte] = (object)
            [
                'compte' => $compte,
                'intitule' => $pcg->getIntitule(),
                'id' => $pcg->getId(),
                't' => 0
            ];
            $parent = null;

            for ($i = strlen($compte) - 1; $i >= 0; $i--)
            {
                $key = substr($compte,0,$i);
                if (array_key_exists($key,$pcgsChilds))
                {
                    $pcgsChilds[$key][] = $compte;
                    $parent = $key;
                    break;
                }
            }

            if ($pcg->getCollectifTiers() != -1)
            {
                $tiers = $doctrine->getRepository('AppBundle:Tiers')
                    ->createQueryBuilder('t')
                    ->where('t.pcc = :pcc')
                    ->setParameter('pcc',$pcg)
                    ->orderBy('t.intitule')
                    ->getQuery()
                    ->getResult();

                $existe = false;
                foreach ($tiers as $tier)
                {
                    //$tier = new Tiers();
                    $compteTiers = $tier->getCompteStr();
                    $pcgsChilds[$compteTiers] = [];
                    $pcgsChilds[$compte][] = $compteTiers;

                    $pcgsObjects[$compteTiers] = (object)
                    [
                        'compte' => $compteTiers,
                        'intitule' => $tier->getIntitule(),
                        'id' => $tier->getId(),
                        't' => 1
                    ];
                    $existe = true;
                }
            }

            if ($parent == null) $pcgsParents[] = $compte;
        }

        $results = [];
        foreach ($pcgsParents as $pcgsParent)
        {
            $results[] = functions::getTree($pcgsParent,$pcgsChilds,$pcgsObjects,[]);
        }

        return $results;
    }

    public static function getTree($parent,$childs,$objects,$selecteds = [])
    {
        if (count($childs[$parent]) !=  0)
        {
            $childrens = [];
            foreach ($childs[$parent] as $child)
            {
                $childrens[] = functions::getTree($child,$childs,$objects,$selecteds);
            }

            return (object)
            [
                'text' => $objects[$parent]->compte . ' - ' . $objects[$parent]->intitule,
                'icon' => 'none',
                'children' => $childrens,
                'id' => $objects[$parent]->t . '#' . $objects[$parent]->id,
                'state' => (object)
                [
                    'selected' => in_array($objects[$parent]->t . '#' . $objects[$parent]->id,$selecteds),
                    'checked' => in_array($objects[$parent]->t . '#' . $objects[$parent]->id,$selecteds)
                ]
            ];
        }
        else return (object)
        [
            'text' => $objects[$parent]->compte . ' - ' . $objects[$parent]->intitule,
            'icon' => 'none',
            'children'  => [],
            'id' => $objects[$parent]->t . '#' . $objects[$parent]->id,
            'state' => (object)
            [
                'selected' => in_array($objects[$parent]->t . '#' . $objects[$parent]->id,$selecteds),
                'checked' => in_array($objects[$parent]->t . '#' . $objects[$parent]->id,$selecteds)
            ]
        ];
    }
}