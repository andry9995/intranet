<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 19/09/2017
 * Time: 11:55
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\TacheLegaleAction;
use Doctrine\ORM\EntityRepository;

class TacheLegaleParamRepository extends EntityRepository
{
    public function getByDossierAndAction(TacheLegaleAction $action, Dossier $dossier)
    {
        $param = $this->getEntityManager()
            ->getRepository('AppBundle:TacheLegaleParam')
            ->createQueryBuilder('param')
            ->select('param')
            ->where('param.tacheLegaleAction = :action')
            ->andWhere('param.dossier = :dossier')
            ->setParameters(array(
                'action' => $action,
                'dossier' => $dossier
            ))
            ->getQuery()
            ->getResult();
        return $param;
    }
}