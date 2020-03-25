<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 31/08/2018
 * Time: 16:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\BanqueSousCategorieAutre;
use AppBundle\Entity\Image;
use AppBundle\Entity\Separation;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class BanqueSousCategorieAutreRepository extends EntityRepository
{
    public function getObByReleve($dossier, $montant, \DateTime $dateReleve, $intervalle){
        $periode_du = clone  $dateReleve;
        $periode_du = $periode_du->modify('-'.$intervalle.'days');
        $periode_au = clone  $dateReleve;
        $periode_au = $periode_au->modify('+'.$intervalle.'days');

        return $this->createQueryBuilder('ob')
            ->innerJoin('ob.image', 'image')
            ->innerJoin('image.lot','lot')
            ->where('lot.dossier = :dossier')
            ->andWhere('ob.date >= :periode_du and ob.date <= :periode_au')
            ->andWhere('ob.montant = :montant')
            ->setParameter('periode_du', $periode_du)
            ->setParameter('periode_au', $periode_au)
            ->setParameter('dossier', $dossier)
            ->setParameter('montant', $montant)
            ->select('ob')
            ->getQuery()
            ->getResult();
    }


    public function getRelevesByPiece($dossier, $montant,\DateTime $datePiece, $intervalle){

        $periode_du = $datePiece->modify('-'.$intervalle.'days');
        $periode_au = $datePiece->modify('+'.$intervalle.'days');

        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        $query = "SELECT ba.id as cb_id,i.id AS image_id, i.nom AS image_nom, ba.id AS cb_id, ba.date, b.nom AS banque_nom, ba.libelle, ba.montant 
                FROM banque_sous_categorie_autre ba 
                INNER JOIN image i ON i.id = ba.image_id  
                INNER JOIN lot l ON l.id = i.lot_id
                INNER JOIN imputation_controle ic ON ic.image_id = i.id
                INNER JOIN banque_compte bc ON bc.id = ic.banque_compte_id
                INNER JOIN banque b on b.id = bc.banque_id
                WHERE ba.sous_categorie_id = 11 
                AND l.dossier_id = :dossier_id
                AND ba.date_facture >= :periode_du
                AND ba.date_facture <= :periode_au
                AND ba.montant = :montant;";


        $prep = $pdo->prepare($query);
        $prep->execute(
            [
                'dossier_id' => $dossier,
                'montant' => $montant,
                'periode_du' => $periode_du->format('Y-m-d'),
                'periode_au' => $periode_au->format('Y-m-d')
            ]
        );


//        $query = "SELECT ba.id as cb_id,i.id AS image_id, i.nom AS image_nom, ba.id AS cb_id, ba.date, b.nom AS banque_nom, ba.libelle, ba.montant
//                        FROM banque_sous_categorie_autre ba
//                        INNER JOIN image i ON i.id = ba.image_id
//                        INNER JOIN lot l ON l.id = i.lot_id
//                        INNER JOIN imputation_controle ic ON ic.image_id = i.id
//                        INNER JOIN banque_compte bc ON bc.id = ic.banque_compte_id
//                        INNER JOIN banque b on b.id = bc.banque_id
//                        WHERE ba.sous_categorie_id = 11 LIMIT 10;";

//        $prep = $pdo->prepare($query);
//        $prep->execute(
//            []
//        );
        $res = $prep->fetchAll();

        return $res;



    }

    /**
     * @param array $ids
     * @return array
     */
    public function getObByIds(array $ids){
        return $this->createQueryBuilder('ob')
            ->where('ob.id in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function getObByImage(Image $image){
        return $this->createQueryBuilder('ob')
            ->where('ob.image = :image')
            ->setParameter('image', $image)
            ->orderBy('ob.date', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function validerImageByOb(Image $image){
        $ret = 0;

        $scs = $this->getEntityManager()
            ->getRepository('AppBundle:SaisieControle')
            ->findBy(['image' => $image]);

        $obs = $this->findBy(['image' => $image]);
        $em = $this->getEntityManager();

        if(count($scs) > 0) {
            $sc = $scs[0];
            $montant = $sc->getMontantTtc();
            $total = 0;

            if(count($obs) > 0) {
                $ret = 1;
                /** @var BanqueSousCategorieAutre $ob */
                foreach ($obs as $ob) {
                    if($ob->getSens() ===  0)
                        $total -= $ob->getMontant();
                    else
                        $total += $ob->getMontant();
                }

                $ecart = $total - $montant;

                if (abs($ecart) < 0.001) {

                    $image->setValider(100);
                    $em->flush();
                    $ret = 2;
                }
            }
        }
        //Carte Credit Ticket vao misy ligne 1 dia valider
        if($ret !== 2){
            $separations = $this->getEntityManager()
                ->getRepository('AppBundle:Separation')
                ->findBy(['image' => $image]);

            if(count($separations) > 0) {
                /** @var Separation $separation */
                $separation = $separations[0];
                if($separation->getSoussouscategorie() !== null) {
                    if($separation->getSoussouscategorie()->getId() === 3) {
                        if (count($obs) > 0) {
                            $image->setValider(100);
                            $em->flush();
                            $ret = 2;
                        }
                    }
                }
            }
        }

        if($ret !== 2){
            $image->setValider(0);
            $em->flush();
        }

        return $ret;

    }

    public function getImageFlaguesById($imageFlagueId, $imageId = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($imageId == null){
            $image = " ";
        }else{
            $image = " AND bsca.image_id <> ".$imageId." ";
        }
        $query = "SELECT bsca.image_id, bsca.libelle
                FROM banque_sous_categorie_autre bsca
                LEFT JOIN image i ON (i.id = bsca.image_id)
                WHERE bsca.image_flague_id = ".$imageFlagueId."
                AND i.supprimer <> 1 
                ".$image."
                GROUP BY bsca.image_id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

}