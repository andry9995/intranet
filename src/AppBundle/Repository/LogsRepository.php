<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 17/06/2016
 * Time: 09:20
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Operateur;
use Doctrine\ORM\EntityRepository;

class LogsRepository extends EntityRepository
{
    public function getLotByDate(\Datetime $date, Operateur $operateur, $to_array = false)
    {
        $from = new \DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($date->format("Y-m-d")." 23:59:59");

        $qb = $this->createQueryBuilder("l");
        $qb
            ->select('l as log')
            ->andWhere('l.dateFin BETWEEN :from AND :to')
            ->andWhere('l.operateur = :operateur')
            ->innerJoin('l.lot', 'lot', 'WITH', 'lot.status=2 OR lot.status=4')
            ->addSelect('lot')
            ->innerJoin('AppBundle\Entity\Image', 'i', 'WITH', 'i.lot=lot.id AND i.supprimer=0')
            ->addSelect('i.exercice as exercice')
            ->innerJoin('AppBundle\Entity\Dossier', 'd', 'WITH', 'lot.dossier=d.id')
            ->addSelect('d.nom as dossier')
            ->innerJoin('AppBundle\Entity\Site', 's', 'WITH', 'd.site=s.id')
            ->addSelect('s.nom as site')
            ->innerJoin('AppBundle\Entity\Client', 'c', 'WITH', 's.client=c.id')
            ->addSelect('c.nom as client')
            ->groupBy('lot.id')
            ->addGroupBy('lot.status')
            ->setParameter('from', $from )
            ->setParameter('to', $to)
            ->setParameter('operateur', $operateur)
        ;
        if ($to_array) {
            $result = $qb->getQuery()->getArrayResult();
        } else {
            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }

    public function lotReceptionFiniN1(\DateTime $date, Operateur $operateur, $to_array = false)
    {
        $from = new \DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($date->format("Y-m-d")." 23:59:59");

        $qb = $this->createQueryBuilder("l");
        $qb
            ->select('l as log')
            ->andWhere('l.dateFin BETWEEN :from AND :to')
            ->andWhere('l.operateur = :operateur')
            ->innerJoin('l.lot', 'lot', 'WITH', 'lot.status=2')
            ->addSelect('lot')
            ->innerJoin('AppBundle\Entity\Image', 'i', 'WITH', 'i.lot = lot.id AND i.nbpage > 1 AND i.supprimer=0')
            ->addSelect('i.exercice, COUNT(i) AS nb_image')
            ->innerJoin('AppBundle\Entity\Dossier', 'd', 'WITH', 'lot.dossier = d.id')
            ->addSelect('d.nom AS dossier')
            ->innerJoin('AppBundle\Entity\Site', 's', 'WITH', 'd.site = s.id')
            ->addSelect('s.nom AS site')
            ->innerJoin('AppBundle\Entity\Client', 'c', 'WITH', 's.client = c.id')
            ->addSelect('c.nom AS client')
            ->groupBy('lot.id')
            ->addGroupBy('lot.status')
            ->setParameter('from', $from )
            ->setParameter('to', $to)
            ->setParameter('operateur', $operateur)
        ;
        if ($to_array) {
            $result = $qb->getQuery()->getArrayResult();
        } else {
            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }

    public function lotReceptionFiniN2(\DateTime $date, Operateur $operateur, $to_array = false)
    {
        $from = new \DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($date->format("Y-m-d")." 23:59:59");

        $qb = $this->createQueryBuilder("l");
        $qb
            ->select('l as log')
            ->andWhere('l.dateFin BETWEEN :from AND :to')
            ->andWhere('l.operateur = :operateur')
            ->innerJoin('l.lot', 'lot', 'WITH', 'lot.status=4')
            ->addSelect('lot')
            ->innerJoin('AppBundle\Entity\Image', 'i', 'WITH', 'i.lot = lot.id AND i.supprimer=0')
            ->addSelect('i.exercice, COUNT(i) AS nb_image')
            ->innerJoin('AppBundle\Entity\Dossier', 'd', 'WITH', 'lot.dossier = d.id')
            ->addSelect('d.nom AS dossier')
            ->innerJoin('AppBundle\Entity\Site', 's', 'WITH', 'd.site = s.id')
            ->addSelect('s.nom AS site')
            ->innerJoin('AppBundle\Entity\Client', 'c', 'WITH', 's.client = c.id')
            ->addSelect('c.nom AS client')
            ->groupBy('lot.id')
            ->addGroupBy('lot.status')
            ->setParameter('from', $from )
            ->setParameter('to', $to)
            ->setParameter('operateur', $operateur)
        ;
        if ($to_array) {
            $result = $qb->getQuery()->getArrayResult();
        } else {
            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }

    public function ImageReceptionFiniN1(\DateTime $date, Operateur $operateur, $to_array = false)
    {
        $from = new \DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($date->format("Y-m-d")." 23:59:59");

        $qb = $this->createQueryBuilder("l");
        $qb
            ->andWhere('l.dateFin BETWEEN :from AND :to')
            ->andWhere('l.operateur = :operateur')
            ->innerJoin('l.lot', 'lot', 'WITH', 'lot.status=2')
            ->innerJoin('AppBundle\Entity\Image', 'i', 'WITH', 'i.lot=lot.id AND i.supprimer=0')
            ->andWhere('i.nbpage>1')
            ->addSelect('COUNT(i) as nb_image')
            ->distinct()
            ->setParameter('from', $from )
            ->setParameter('to', $to)
            ->setParameter('operateur', $operateur)
        ;
        if ($to_array) {
            $result = $qb->getQuery()->getArrayResult();
        } else {
            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }

    public function ImageReceptionFiniN2(\DateTime $date, Operateur $operateur, $to_array = false)
    {
        $from = new \DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($date->format("Y-m-d")." 23:59:59");

        $qb = $this->createQueryBuilder("l");
        $qb
            ->andWhere('l.dateFin BETWEEN :from AND :to')
            ->andWhere('l.operateur = :operateur')
            ->innerJoin('l.lot', 'lot', 'WITH', 'lot.status=4')
            ->innerJoin('AppBundle\Entity\Image', 'i', 'WITH', 'i.lot=lot.id AND i.supprimer=0')
            ->addSelect('COUNT(i) as nb_image')
            ->distinct()
            ->setParameter('from', $from )
            ->setParameter('to', $to)
            ->setParameter('operateur', $operateur)
        ;
        if ($to_array) {
            $result = $qb->getQuery()->getArrayResult();
        } else {
            $result = $qb->getQuery()->getResult();
        }

        return $result;
    }
}