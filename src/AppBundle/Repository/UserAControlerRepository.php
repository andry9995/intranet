<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Operateur;
use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;


class UserAControlerRepository extends EntityRepository
{
	public function getAllResponsableSeparation()
	{
		$codes = ['CODE_CHEF_SEPARATION', 'CODE_CHEF_REC_SEP', 'CODE_ASSISTANT_SEPARATION'];
		$qb = $this->getEntityManager()
	            ->getRepository('AppBundle:Operateur')
	            ->createQueryBuilder('op');
	            $res = $qb->select()
	            ->innerJoin('op.organisation', 'og')
	            ->addSelect('og')
	            ->where($qb->expr()->in('og.code', ':codes'))
                ->andWhere('op.supprimer=:supprimer')
	            ->setParameters(array(
	                'codes' => $codes,
                    'supprimer' => 0,
	            ))
	            ->orderBy('op.nom')
	            ->getQuery()
	            ->getResult();

        return $res;
	}
}