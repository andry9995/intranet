<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 01/03/2018
 * Time: 11:25
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Gamme;
use Doctrine\ORM\EntityRepository;

class GammeRepository extends EntityRepository
{
    public function getAll()
    {
        $gammes = $this->getEntityManager()
            ->getRepository('AppBundle:Gamme')
            ->createQueryBuilder('gamme')
            ->select('gamme')
            ->orderBy('gamme.nom')
            ->getQuery()
            ->getResult();
        /** @var Gamme $gamme */
        foreach ($gammes as &$gamme) {
            $gamme->setProcedures($this->getProcedures($gamme));
        }

        return $gammes;
    }

    public function getProcedures(Gamme $gamme)
    {
        $procedures = [];
        if (is_array($gamme->getProcedures())) {
            foreach ($gamme->getProcedures() as $item) {
                $procedure = $this->getEntityManager()
                    ->getRepository('AppBundle:ProcedureIntranet')
                    ->find($item);
                if ($procedure) {
                    $procedures[] = $procedure;
                }
            }
        }

        return $procedures;
    }
}