<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 02/05/2016
 * Time: 14:17
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CodeCelluleRepository extends EntityRepository
{
    /**
     * Liste des codes cellules
     *
     * @return array
     */
    public function getAllCodeCellule()
    {
        $code_cellules = $this->getEntityManager()->getRepository('AppBundle:CodeCellule')
            ->createQueryBuilder('c')
            ->orderBy('c.code', 'ASC')
            ->getQuery()
            ->getResult();

        return $code_cellules;
    }
}