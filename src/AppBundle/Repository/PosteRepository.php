<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PosteRepository extends EntityRepository
{
    /**
     * Liste des postes
     *
     * @return array
     */
    public function getAllPoste()
    {
        $postes = $this->getEntityManager()->getRepository('AppBundle:Poste')
            ->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.cellule', 'c')
            ->addSelect('c')
            ->where('p.supprimer != :supprimer')
            ->setParameter('supprimer', 1)
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $postes;
    }

    /**
     * Listes des postes groupés par cellules
     * 
     * @return array
     */
    public function getAllPosteWithCellule()
    {
        $postes = $this->getEntityManager()
            ->getRepository('AppBundle:Poste')
            ->getAllPoste();

        $liste_poste = array();

        foreach ($postes as $poste) {
            $cellule = $poste->getCellule()->getNom();
            $liste_poste[$cellule][] = $poste;
        }

        return $liste_poste;
    }
}