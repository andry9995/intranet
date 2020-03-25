<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 25/07/2018
 * Time: 15:08
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ResponsableScriptura;
use Doctrine\ORM\EntityRepository;

class ResponsableScripturaRepository extends EntityRepository
{

    public function getSubDirections()
    {
        $directions = $this->getAllResponsable();

        $sub = array();

        foreach ($directions as $direction) {
            
            $childs = $direction->child;

            if (!empty($childs)) {
                
                foreach ($childs as $child) {
                    
                    $item = array();

                    $item['operateur_id'] = $child->getOperateur()->getId();
                    $item['prenom'] = $child->getOperateur()->getPrenom();

                    array_push($sub, $item) ;
                }
            }
        }

        return $sub;
    }


    public function getAllResponsable()
    {
        $directions = $this->getEntityManager()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->createQueryBuilder('resp')
            ->select('resp')
            ->where('resp.superieur IS NULL')
            ->join('resp.operateur', 'op')
            ->andWhere('op.affecterDossier=1')
            ->getQuery()
            ->getResult();
        foreach ($directions as &$direction) {
            $direction->child = $this->getChild($direction);
            foreach ($direction->child as &$child2) {
                $child2->child = $this->getChild($child2);
                foreach ($child2->child as &$child3) {
                    $child3->child = $this->getChild($child3);
                    foreach ($child3->child as &$child4) {
                        $child4->child = $this->getChild($child4);
                    }
                }
            }
        }
        return $directions;
    }

    public function getChild(ResponsableScriptura $responsable)
    {
        $childs =  $this->getEntityManager()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->createQueryBuilder('resp')
            ->select('resp')
            ->where('resp.superieur = :superieur')
            ->join('resp.operateur', 'op')
            ->andWhere('op.affecterDossier=1')
            ->setParameters(array(
                'superieur' => $responsable->getOperateur(),
            ))
            ->getQuery()
            ->getResult();
        return $childs;
    }

    public function getParentIds($responsable)
    {
        $ids = array();
        $ids[] = $responsable;
        if ($this->getParentId($responsable)>0){
            $r2 = $this->getParentId($responsable);
            $ids[] = $r2;
            if ($this->getParentId($r2)>0){
                $r3 = $this->getParentId($r2);
                $ids[] = $r3;
                if ($this->getParentId($r3)>0){
                    $r4 = $this->getParentId($r3);
                    $ids[] = $r4;
                }
            }
        }
        return $ids;
    }
    public function getParentId($responsable)
    {
        $id =0;
        $directions = $this->getEntityManager()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->createQueryBuilder('resp')
            ->select('resp')
            ->where('resp.operateur = :opid')
            ->andWhere('resp.superieur IS NOT NULL')
            ->setParameters(array('opid' => $responsable))
            ->getQuery()
            ->getResult();
        foreach ($directions as &$direction) {
            $id = $direction->getSuperieur()->getId();
        }
        return $id;
    }

    /**
     * @param $responsables
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveResponsable($responsables)
    {
        $em = $this->getEntityManager();
        $ids = array_map(function ($item) {
            if (isset($item['id'])) {
                return $item['id'];
            }
            return null;
        }, $responsables);

        $ids = array_merge($ids, [0]);

        $qb = $this->getEntityManager()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->createQueryBuilder('resp');
        $to_be_deleting = $qb->select('resp')
            ->innerJoin('resp.operateur', 'operateur')
            ->where($qb->expr()->notIn('operateur.id', $ids))
            ->getQuery()
            ->getResult();
        foreach ($to_be_deleting as $item) {
            $em->remove($item);
            $em->flush();
        }

        foreach ($responsables as $item) {
            $operateur = $this->getEntityManager()
                ->getRepository('AppBundle:Operateur')
                ->find($item['id']);
            if ($operateur) {
                $responsable = $this->getEntityManager()
                    ->getRepository('AppBundle:ResponsableScriptura')
                    ->findOneBy(array(
                        'operateur' => $operateur,
                    ));
                if (!$responsable) {
                    $responsable = new ResponsableScriptura();
                    $responsable->setOperateur($operateur);
                    $em->persist($responsable);
                }
                $responsable->setSuperieur(null);
                if (isset($item['sup']) && $item['sup'] !== null) {
                    $superieur = $this->getEntityManager()
                        ->getRepository('AppBundle:Operateur')
                        ->find($item['sup']);
                    if ($superieur) {
                        $responsable->setSuperieur($superieur);
                    }
                }
                $em->flush();
            }
        }
    }
}