<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 06/07/2016
 * Time: 15:57
 */

namespace AppBundle\Repository;

use AppBundle\Entity\EtapeTraitement;
use Doctrine\ORM\EntityRepository;

class PartageSuiviRepository extends EntityRepository
{
    public function getFromLotArray($paniers, EtapeTraitement $etape, $to_array = false)
    {
        $images = array();
        $categories = array();

        foreach ($paniers as $panier) {
            $images[] = $panier->getImage()->getId();
        }

        $liste_categ = $this->getEntityManager()
            ->getRepository('AppBundle:Separation')
            ->createQueryBuilder('sep')
            ->select('DISTINCT(sep.categorie)')
            ->where('sep.image IN (:images)')
            ->setParameter('images', $images)
            ->getQuery()
            ->getResult();

        foreach ($liste_categ as $categ) {
            $categories[] = $categ;
        }

        $partage = $this->getEntityManager()
            ->getRepository('AppBundle:PartageSuivi')
            ->createQueryBuilder('suivi')
            ->select('suivi')
            ->where('suivi.categorie IN (:categories)')
            ->andWhere('suivi.etapeTraitement = :etape')
            ->setParameter('categories', $categories)
            ->setParameter('etape', $etape)
            ->getQuery();
        if ($to_array) {
            return $partage->getArrayResult();
        } else {
            return $partage->getResult();
        }
    }
}