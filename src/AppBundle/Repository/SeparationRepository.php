<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 17/12/2018
 * Time: 11:22
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\Image;
use AppBundle\Entity\Separation;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class SeparationRepository extends EntityRepository
{
    public function getSeparationByImage(Image $image){
        /** @var Separation[] $res */
        $res = $this->createQueryBuilder('s')
            ->where('s.image = :image')
            ->setParameter('image', $image)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if(count($res) > 0){
            return $res[0];
        }
        return null;
    }

    public function countSouscategorie(Dossier $dossier, $exercice, $valide = false)
    {

        $query = "SELECT count(*) AS nbre, 0 AS valide, sep.souscategorie_id
                        FROM separation sep 
                        INNER JOIN image i ON i.id = sep.image_id 
                        INNER JOIN lot l ON l.id = i.lot_id AND sep.categorie_id = 16 AND l.dossier_id = :dossierid
                        AND i.exercice = :exercice AND sep.categorie_id = 16 AND sep.souscategorie_id <> 1 AND i.saisie1 > 0 
                        AND i.supprimer = 0 AND i.decouper = 0 ";
        if($valide)
            $query .=" AND i.valider = 100 ";

        $query .= " GROUP BY souscategorie_id";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute([
            'dossierid'=> $dossier->getId(),
            'exercice' => $exercice
        ]);

        return $prep->fetchAll();
    }

    public function countSoussouscategorieCarte(Dossier $dossier, $exercice, $valide = false)
    {

        $query = "SELECT count(*) AS nbre, 0 AS valide, sep.souscategorie_id, sep.soussouscategorie_id 
                        FROM separation sep 
                        INNER JOIN image i ON i.id = sep.image_id 
                        INNER JOIN lot l ON l.id = i.lot_id AND sep.categorie_id = 16 AND l.dossier_id = :dossierid 
                        AND i.exercice = :exercice AND sep.categorie_id = 16 and sep.souscategorie_id = 1 AND i.saisie1 > 0";

        if($valide)
            $query .= " AND i.valider = 100";

        $query .=" GROUP BY souscategorie_id, soussouscategorie_id";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute([
            'dossierid'=> $dossier->getId(),
            'exercice' => $exercice
        ]);

        return $prep->fetchAll();
    }

    public function countSoussouscategorieCheque(Dossier $dossier, $exercice, $valide = false)
    {

        $query = "SELECT count(*) AS nbre, 0 AS valide, sep.souscategorie_id, sep.soussouscategorie_id 
                        FROM separation sep 
                        INNER JOIN image i ON i.id = sep.image_id 
                        INNER JOIN lot l ON l.id = i.lot_id AND sep.categorie_id = 16 AND l.dossier_id = :dossierid 
                        AND i.exercice = :exercice AND sep.categorie_id = 16 and i.supprimer =0 and i.decouper = 0 
                        and sep.souscategorie_id = 153 AND i.saisie1 > 0";

        if($valide)
            $query .= " AND i.valider = 100";

        $query .= " GROUP BY souscategorie_id, soussouscategorie_id";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);

        $prep->execute([
            'dossierid'=> $dossier->getId(),
            'exercice' => $exercice
        ]);

        return $prep->fetchAll();
    }
}