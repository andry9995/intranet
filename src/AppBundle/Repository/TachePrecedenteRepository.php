<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 04/08/2016
 * Time: 16:44
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Tache;
use Doctrine\ORM\EntityRepository;

class TachePrecedenteRepository extends EntityRepository
{
    /**
     * Listes de toutes les taches précédentes d'une tache
     *
     * @param Tache $tache_principale
     * @param bool $to_array
     * @return array
     */
    public function getAllTachePrecedente(Tache $tache_principale, $to_array = false)
    {
        $taches = $this->getEntityManager()->getRepository('AppBundle:TachePrecedente')
            ->createQueryBuilder('t')
            ->select('t')
            ->innerJoin('t.tachePrecedente', 'prec')
            ->addSelect('prec')
            ->innerJoin('t.tachePrincipale', 'princ')
            ->addSelect('princ')
            ->where('t.tachePrincipale = :tache_principale')
            ->setParameter('tache_principale', $tache_principale)
            ->orderBy('t.ordre')
            ->getQuery();

        if ($to_array) {
            return $taches->getArrayResult();
        }
        return $taches->getResult();
    }
}