<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 25/10/2017
 * Time: 11:27
 */

namespace AppBundle\Repository;

use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Categorie;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\Image;
use AppBundle\Entity\Lot;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class ImageAtraiterRepository extends EntityRepository
{
    public function getImageByLot($lot_id)
    {
        $images = $this->getEntityManager()
            ->getRepository('AppBundle:ImageATraiter')
            ->createQueryBuilder('A')
            ->select('A')
            ->innerJoin('A.image', 'image')
            ->innerJoin('image.lot', 'lot')
            ->where('lot.id = :lot_id')
            ->setParameters(array(
                'lot_id' => $lot_id
            ))
            ->getQuery()
            ->getResult();
        return $images;
    }

    public function getImageATraiterSaisie2Banque(BanqueCompte $banqueCompte){

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "select i.id as image_id from image i 
                    inner join separation sep on sep.image_id = i.id 
                    where sep.souscategorie_id = 10 
                    and i.id not in (select distinct image_id from releve where banque_compte_id = :banquecompteid)";

        $prep = $pdo->prepare($query);
        $param = ['banquecompteid' => $banqueCompte->getId()];

        $prep->execute($param);

        $res = $prep->fetchAll();

        $images = [];

        foreach ($res as $image){
            $images[] = $this->getEntityManager()
                ->getRepository('AppBundle:Image')
                ->find($image->image_id);
        }

        return $images;
    }

    public function getImageATraiterByLotAndCategorie(Lot $lot, $categorie, EtapeTraitement $etape, $isReleve = null)
    {
        //TODO VERIF IMAGE SANS CATEGORIE

        $images = $this->getEntityManager()
            ->getRepository('AppBundle:Image')
            ->createQueryBuilder('image')
            ->select('image')
            ->innerJoin('image.lot', 'LOT')
            ->innerJoin('AppBundle\Entity\Separation', 'SEP', 'WITH', 'SEP.image = image')
            ->innerJoin('AppBundle\Entity\ImageATraiter', 'A', 'WITH', 'A.image = image')
            ->where('LOT = :lot')
            ->andWhere('SEP.categorie IS NOT NULL');
        if ($categorie) {
            $images = $images
                ->innerJoin('SEP.categorie', 'CAT')
                ->andWhere('CAT.id = ' . $categorie->getId());

            if($isReleve !== null) {

                if ($isReleve === true) {
                    $images = $images
                        ->innerJoin('SEP.souscategorie', 'SCAT')
                        ->andWhere('SCAT.id = 10');
                } else  {
                    $images = $images
                        ->innerJoin('SEP.souscategorie', 'SCAT')
                        ->andWhere('SCAT.id <> 10');
                }
            }
        }
        if ($etape->getCode() === 'OS_1') {
            $images = $images
                ->andWhere('A.status = 4')
                ->andWhere('A.saisie1 = 0');
        } elseif ($etape->getCode() === 'OS_2') {
            $images = $images
                ->andWhere('A.status = 4')
                ->andWhere('A.saisie2 = 0');
        } elseif ($etape->getCode() === 'CTRL_OS') {
            $images = $images
                ->andWhere('A.status = 4')
                ->andWhere('A.saisie1 = 2')
                ->andWhere('A.saisie2 = 2');
        } elseif ($etape->getCode() === 'IMP') {
            $images = $images->andWhere('A.status = 6');
        } elseif ($etape->getCode() === 'CTRL_IMP') {
            $images = $images->andWhere('A.status = 9');
        } else {
            $images = $images->andWhere('A.status = -1');
        }

        $images = $images
            ->setParameters(array(
                'lot' => $lot,
            ))
            ->getQuery()
            ->getResult();

        return $images;
    }


    public function getImageATraiterByDossierAndCategorie(Dossier $dossier, $categorie, EtapeTraitement $etape, $isReleve = null)
    {
        //TODO VERIF IMAGE SANS CATEGORIE

        $images = $this->getEntityManager()
            ->getRepository('AppBundle:Image')
            ->createQueryBuilder('image')
            ->select('image')
            ->innerJoin('image.lot', 'lot')
            ->innerJoin('AppBundle\Entity\Separation', 'SEP', 'WITH', 'SEP.image = image')
            ->innerJoin('AppBundle\Entity\ImageATraiter', 'A', 'WITH', 'A.image = image')
            ->where('lot.dossier = :dossier')
            ->andWhere('SEP.categorie IS NOT NULL')
            ->setParameter('dossier', $dossier)
        ;
        if ($categorie) {
            $images = $images
                ->innerJoin('SEP.categorie', 'CAT')
                ->andWhere('CAT.id = ' . $categorie->getId());

            if($isReleve !== null) {

                if ($isReleve === true) {
                    $images = $images
                        ->innerJoin('SEP.souscategorie', 'SCAT')
                        ->andWhere('SCAT.id = 10');
                } else  {
                    $images = $images
                        ->innerJoin('SEP.souscategorie', 'SCAT')
                        ->andWhere('SCAT.id <> 10');
                }
            }
        }
        if ($etape->getCode() === 'OS_1') {
            $images = $images
                ->andWhere('A.status = 4')
                ->andWhere('A.saisie1 = 0');
        } elseif ($etape->getCode() === 'OS_2') {
            $images = $images
                ->andWhere('A.status = 4')
                ->andWhere('A.saisie2 = 0');
        } elseif ($etape->getCode() === 'CTRL_OS') {
            $images = $images
                ->andWhere('A.status = 4')
                ->andWhere('A.saisie1 = 2')
                ->andWhere('A.saisie2 = 2');
        } elseif ($etape->getCode() === 'IMP') {
            $images = $images->andWhere('A.status = 6');
        } elseif ($etape->getCode() === 'CTRL_IMP') {
            $images = $images->andWhere('A.status = 9');
        } else {
            $images = $images->andWhere('A.status = -1');
        }

        $images = $images
            ->getQuery()
            ->getResult();

        return $images;
    }

    public function getImageAtraiterByImage(Image $image){
        $images = $this->createQueryBuilder('i')
            ->where('i.image = :image')
            ->setParameter('image', $image)
            ->getQuery()
            ->getResult();

        if(count($images)> 0){
            return $images[0];
        }

        return null;
    }
}