<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 27/08/2018
 * Time: 08:39
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Banque;
use AppBundle\Entity\BanqueCompte;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Image;
use AppBundle\Entity\Releve;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;

class ReleveRepository extends EntityRepository
{
    /**
     * @param $dossier
     * @param $montant
     * @param \DateTime $datePiece
     * @param $intervalle
     * @return array
     */
    public function getRelevesByPiece($dossier, $montant,\DateTime $datePiece, $intervalle){

        $periode_du = $datePiece->modify('-'.$intervalle.'days');
        $periode_au = $datePiece->modify('+'.$intervalle.'days');

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT r.id as releve_id, i.id as image_id, i.nom as image_nom, r.id as releve_id,b.nom, r.date_releve, i.nom,
                        b.nom as banque_nom, r.libelle, r.debit FROM releve r
                        INNER JOIN image i ON i.id = r.image_id
                        INNER JOIN banque_compte bc ON bc.id = r.banque_compte_id
                        INNER JOIN banque b ON b.id = bc.banque_id
                        INNER JOIN lot l ON l.id = i.lot_id
                        WHERE  l.dossier_id = :dossier_id
                        AND r.date_releve >= :periode_du
                        AND r.date_releve <= :periode_au
                        AND r.debit = :montant
                        AND r.operateur_id IS NULL";


        $prep = $pdo->prepare($query);
        $prep->execute(
            [
                'dossier_id' => $dossier,
                'montant' => $montant,
                'periode_du' => $periode_du->format('Y-m-d'),
                'periode_au' => $periode_au->format('Y-m-d')
            ]
        );

        $res = $prep->fetchAll();

        return $res;
    }


    /**
     * @param BanqueCompte $banqueCompte
     * @param $exercice
     * @param null $datescan
     * @return array
     */
    public function getRelevesByBanqueCompte(BanqueCompte $banqueCompte , $exercice, $datescan = null)
    {
        $releve = $this->getEntityManager()
            ->getRepository('AppBundle:Souscategorie')
            ->find(10);

        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.image', 'image')
            ->innerJoin('image.lot', 'lot')
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.image = image')
            ->where('image.exercice = :exercice')
            ->andWhere('r.banqueCompte = :banquecompte')
            ->andWhere('image.supprimer = 0')
            ->andWhere('r.operateur IS NULL')
            ->andWhere('sep.souscategorie = :souscategorie')
            ->setParameter('banquecompte', $banqueCompte)
            ->setParameter('souscategorie', $releve)
            ->setParameter('exercice', $exercice);

        if($datescan !== null) {
            $qb = $qb->andWhere('lot.dateScan = :datescan')
                ->setParameter('datescan', $datescan);
        }

        $qb = $qb->select('r')
            ->addOrderBy('r.dateReleve');

        return $qb->getQuery()
            ->getResult();
    }


    public function getDoublonRelevesByBanqueCompte(BanqueCompte $banqueCompte , $exercice)
    {
        $releve = $this->getEntityManager()
            ->getRepository('AppBundle:Souscategorie')
            ->find(10);

        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.image', 'image')
            ->innerJoin('image.lot', 'lot')
            ->innerJoin('AppBundle\Entity\Separation', 'sep', 'WITH', 'sep.image = image')
            ->where('image.exercice = :exercice')
            ->andWhere('r.banqueCompte = :banquecompte')
            ->andWhere('image.supprimer = 0')
            ->andWhere('r.operateur IS NOT NULL')
            ->andWhere('sep.souscategorie = :souscategorie')
            ->setParameter('banquecompte', $banqueCompte)
            ->setParameter('souscategorie', $releve)
            ->setParameter('exercice', $exercice);

        $qb->select('r')
            ->addOrderBy('r.dateReleve');

        return $qb->getQuery()
            ->getResult();
    }

    public function getRelevesByImage(Image $image, $order = 'ASC'){
        return $this->createQueryBuilder('r')
            ->where('r.image = :image')
            ->andWhere('r.operateur IS NULL')
            ->setParameter('image', $image)
            ->orderBy('r.dateReleve', $order)
            ->getQuery()
            ->getResult();
    }

    public function checkMonantInReleve(BanqueCompte $banqueCompte, $montant, $exercice,\DateTime $dateReleve = null){

        $releves = [];

        try {

            $dateMax = null;
            $dateMin = null;

            if($dateReleve !== null) {
                $dateMax = clone $dateReleve;
                $dateMin = clone $dateReleve;

                $dateMax->add(new \DateInterval('P60D'));
                $dateMin->sub(new \DateInterval('P60D'));
            }

            $qb = $this->createQueryBuilder('r')
                ->innerJoin('r.image', 'image')
                ->where('r.banqueCompte = :banquecompte')
                ->andWhere('r.debit = :montant OR r.credit = :montant')
                ->andWhere('r.operateur IS NULL')
                ->andWhere('image.exercice = :exercice')
                ->setParameter('montant', $montant)
                ->setParameter('banquecompte', $banqueCompte)
                ->setParameter('exercice', $exercice)
            ;

            if($dateReleve !== null) {
                $qb->
                andWhere('r.dateReleve >= :dateMin')
                    ->andWhere('r.dateReleve <= :dateMax')
                    ->setParameter('dateMin', $dateMin)
                    ->setParameter('dateMax', $dateMax);
            }


            $releves = $qb
                ->getQuery()
                ->getResult();

        } catch (\Exception $e) {
            $x = $e->getMessage();
        }

        return $releves;


    }


    public function getRelevesByImageDate(Image $image, \DateTime $dateReleve){

        $releves = $this->createQueryBuilder('r')
            ->where('r.image = :image')
            ->andWhere('r.dateReleve >= :datereleve')
            ->andWhere('r.operateur IS NULL')
            ->setParameter('image', $image)
            ->setParameter('datereleve', $dateReleve)
            ->getQuery()
            ->getResult();

        return $releves;
    }


    public function searchReleve(BanqueCompte $banqueCompte, $exerice, array $keys){
        $reg = '';
//        foreach ($keys as $key){
//            if($reg === ''){
//                $reg .= '^'.$key;
//            }
//            else{
//                $reg .= '|^'.$key;
//            }
//        }

        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.image', 'image')
            ->where('r.banqueCompte = :banquecompte')
            ->andWhere('r.operateur IS NULL')
            ->andWhere('image.exercice = :exercice')
            ->setParameter('banquecompte', $banqueCompte)
            ->setParameter('exercice', $exerice);


        if(count($keys) > 0){
            $or = '';
            $i = 0;
            foreach ($keys as $key) {
                if ($or === '') {
                    $or = 'r.libelle like :k'.$i;
                }
                else{
                    $or .= ' OR r.libelle like :k'.$i;
                }
            }
            $qb = $qb->andWhere($or);
            $i = 0;
            foreach ($keys as $key){
                $qb = $qb->setParameter('k'.$i, '%'.$key.'%');
            }
        }



        $res = $qb->select('r')
            ->getQuery()
            ->getResult();

        return $res;
    }

    public function checkMonthReleve(BanqueCompte $banqueCompte, $exercice, \DateTime $dateReleve = null)
    {

        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.image', 'i')
            ->where('r.banqueCompte = :banquecompte')
            ->setParameter('banquecompte', $banqueCompte)
            ->andWhere('i.exercice = :exerice')
            ->setParameter('exerice', $exercice);

        if ($dateReleve !== null) {
            $dateReleve->setDate($dateReleve->format('Y'), $dateReleve->format('m'), 1);

            $dateMin = clone $dateReleve;
            $dateMax = clone  $dateReleve;

            $dateMax->modify('first day of next month');

            $qb = $qb->andWhere('r.dateReleve >= :dateMin')
                ->andWhere('r.dateReleve <= :dateMax')
                ->setParameter('dateMin', $dateMin)
                ->setParameter('dateMax', $dateMax);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function checkRib(BanqueCompte $banquecompte){
        $releves =  $this->createQueryBuilder('r')
            ->where('r.banqueCompte = :banquecompte')
            ->setParameter('banquecompte', $banquecompte)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if(count($releves) > 0){
            return true;
        }

        $em = $this->getEntityManager();
        $banquecompte->setObASaisir(null);
        try {
            $em->flush();
        } catch (OptimisticLockException $e) {
        }

        return false;
    }

    public function validerImageByReleve(Image $image){
        $ret = 0;

        $scs = $this->getEntityManager()
            ->getRepository('AppBundle:SaisieControle')
            ->findBy(['image' => $image]);

        if(count($scs) > 0){
            $sc = $scs[0];
            $soldeDebut = $sc->getSoldeDebut();
            $soldeFin = $sc->getSoldeFin();

            /** @var Releve[] $releves */
            $releves = $this->createQueryBuilder('r')
                ->where('r.image = :image')
                ->andWhere('r.operateur IS NULL')
                ->setParameter('image', $image)
                ->getQuery()
                ->getResult();

            $mouvement = 0;

            if(count($releves) > 0){
                $ret = 1;

                foreach ($releves as $releve){
                    $mouvement += (float)$releve->getCredit() - (float)$releve->getDebit();
                }

                $ecart = $soldeFin - ($soldeDebut + $mouvement);

                if(abs($ecart) < 0.01){
                    $em = $this->getEntityManager();
                    $image->setValider(100);
                    $em->flush();
                    $ret = 2;
                }
            }
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
            $image = " AND r.image_id <> ".$imageId." ";
        }
        $query = "SELECT r.libelle, r.date_releve, i.nom, r.image_id, r.credit, r.debit
                FROM releve r
                LEFT JOIN image i ON (i.id = r.image_id)
                WHERE r.image_flague_id = ".$imageFlagueId."
                AND r.operateur_id IS NULL
                AND i.supprimer <> 1 
                ".$image."
                GROUP BY r.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getReleveWithImageFlague($clientId, $dossierId, $exercice)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "select r.debit - r.credit as montant
                from image i
                left join releve r on (r.image_id = i.id)
                inner join lot l on (l.id = i.lot_id)
                inner join dossier d on (l.dossier_id = d.id)
                inner join site s on (s.id = d.site_id)
                inner join client c on (c.id = s.client_id)
                inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
                inner join banque b on (b.id = bc.banque_id)
                left join regime_tva rtva on (d.regime_tva_id = rtva.id)
                where i.exercice = ".$exercice." and i.supprimer = 0 
                and r.image_flague_id is null
                and c.status = 1
                and r.operateur_id is null
                and i.decouper = 0
                and c.id = ".$clientId."
                and d.id = ".$dossierId."
                group by r.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getReleveImageNonValideTous($dossierId, $exercice, $debPeriode = null, $finPeriode = null, $detail = false, $souscategorieLib = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }
        $souscategoLib = " ";
        if($souscategorieLib != -1 && $souscategorieLib != null) 
            $souscategoLib = " and scat.libelle_new = '".$souscategorieLib."' ";

        if(!$detail){
            $query = "select (r.credit - r.debit) as montant, i.nom, i.id, r.libelle, r.id as relId, cat.id as cat_id, scat.id as scat_id, scat.libelle_new ";
        }else{
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, scat.libelle_new, sep.categorie_id, i.valider, (r.credit - r.debit) as montant, -1 as prioriteImageId, scat.id, sscat.id ";
        }

        $query .= " from image i
                left join releve r on (r.image_id = i.id)
                inner join lot l on (l.id = i.lot_id)
                inner join dossier d on (l.dossier_id = d.id)
                inner join site s on (s.id = d.site_id)
                inner join client c on (c.id = s.client_id)
                inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
                inner join banque b on (b.id = bc.banque_id)
                inner join separation sep on (sep.image_id = i.id)
                inner join categorie cat on (cat.id = sep.categorie_id)
                left join souscategorie scat on scat.id= sep.souscategorie_id
                left join soussouscategorie sscat on sscat.id=sep.soussouscategorie_id
                left join saisie_controle sc on (sc.image_id = i.id) 
                left join imputation_controle ic on (i.id = ic.image_id) 
                where i.exercice = ".$exercice." and i.supprimer = 0 
                and r.image_flague_id is null
                and c.status = 1
                and r.flaguer = 1
                and cat.id <> 16
                and r.operateur_id is null
                ".$queryScan."
                ".$souscategoLib."
                and i.decouper = 0
                and d.id = ".$dossierId."
                group by i.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getReleveImageNonValideBanque($dossierId, $exercice, $debPeriode = null, $finPeriode = null, $detail = false, $souscategorieLib = null)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        if($debPeriode === null && $finPeriode === null){
            $queryScan = " ";
        }else{
            $queryScan = " and l.date_scan >= '".$debPeriode."'";
            $queryScan .= " and l.date_scan <= '".$finPeriode."'";
        }

        $souscategoLib = " ";
        if($souscategorieLib != -1 && $souscategorieLib != null) 
            $souscategoLib = " and scat.libelle_new = '".$souscategorieLib."' ";

        if(!$detail){
            $query = "select (r.credit - r.debit) as montant, i.nom, i.id, r.libelle, r.id as relId, cat.id as cat_id, scat.id as scat_id, scat.libelle_new, d.id as dossier_id, i.exercice ";
        }else{
            $query = "SELECT distinct(i.id) as imageId, i.nom as image, date_format(l.date_scan,'%d-%m-%Y') as date_scan, date_format(sc.date_facture,'%d-%m-%Y') as date_piece, ic.rs, i.imputation, ic.type_piece_id, scat.libelle_new, sep.categorie_id, i.valider, (r.credit - r.debit) as montant, -1 as prioriteImageId, scat.id, sscat.id, d.id as dossier_id, i.exercice ";
        }
        $query .= " from image i
                left join releve r on (r.image_id = i.id)
                inner join lot l on (l.id = i.lot_id)
                inner join dossier d on (l.dossier_id = d.id)
                inner join site s on (s.id = d.site_id)
                inner join client c on (c.id = s.client_id)
                inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
                inner join banque b on (b.id = bc.banque_id)
                inner join separation sep on (sep.image_id = i.id)
                inner join categorie cat on (cat.id = sep.categorie_id)
                left join souscategorie scat on scat.id= sep.souscategorie_id
                left join saisie_controle sc on (sc.image_id = i.id) 
                left join imputation_controle ic on (i.id = ic.image_id) 
                left join soussouscategorie sscat on sscat.id=sep.soussouscategorie_id
                where i.exercice = ".$exercice." and i.supprimer = 0 
                and r.image_flague_id is null
                and cat.id = 16
                and c.status = 1
                and r.flaguer = 1
                and r.operateur_id is null
                ".$queryScan."
                ".$souscategoLib."
                and i.decouper = 0
                and d.id = ".$dossierId."
                group by i.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function getReleveImageNonValide($dossierId, $exercice)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "select (r.credit - r.debit) as montant, i.nom, i.id, r.libelle, r.id as relId
                from image i
                left join releve r on (r.image_id = i.id)
                inner join lot l on (l.id = i.lot_id)
                inner join dossier d on (l.dossier_id = d.id)
                inner join site s on (s.id = d.site_id)
                inner join client c on (c.id = s.client_id)
                inner join banque_compte bc on (bc.dossier_id = d.id and bc.id = r.banque_compte_id)
                inner join banque b on (b.id = bc.banque_id)
                where i.exercice = ".$exercice." and i.supprimer = 0 
                and r.image_flague_id is null
                and c.status = 1
                and r.flaguer = 1
                and r.operateur_id is null
                and i.decouper = 0
                and d.id = ".$dossierId."
                group by r.id";
        $prep = $pdo->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }
}