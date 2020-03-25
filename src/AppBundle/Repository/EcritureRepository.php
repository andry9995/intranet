<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\Image;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Ecriture;
use AppBundle\Controller\Balance;
use AppBundle\Controller\GrandLivre as GrandLivre;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\EtatCompte;
use Foo\Bar\B;
use Symfony\Component\Validator\Constraints\DateTime;

class EcritureRepository extends EntityRepository
{

    //ETAT DE BASE
    public function getBalance($dossier,$exercices,$mois,$avec_solde)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $query = $this->createQueryBuilder('e')
            ->select('e as balance,ROUND(SUM(e.debit),2) as db,round(SUM(e.credit),2) as cr')
            ->leftJoin('e.pcc','pcc')
            ->where('e.pcc IS NOT NULL')
            ->andWhere('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN (:exercice)')
            ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $query = $query->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $query = $query->groupBy('pcc')
            ->addGroupBy('e.exercice')
            ->orderBy('pcc.compte','ASC')
            ->getQuery()->getResult();

        $comptes = array();
        $comptes_str = array();
        $debits = array();
        $credits = array();
        $soldes_debit = array();
        $soldes_credit = array();

        foreach($query as $balance)
        {
            $compte = $balance['balance']->getPcc();
            $num_compte = $compte->getCompte();

            $exercice = $balance['balance']->getExercice();
            $debit = $balance['db'];
            $credit = $balance['cr'];

            $debits[$num_compte][$exercice] = $credits[$num_compte][$exercice] = 0;
            $debits[$num_compte][$exercice] = $debit;
            $credits[$num_compte][$exercice] = $credit;

            $solde = $debit - $credit;

            $soldes_debit[$num_compte][$exercice] = ($solde > 0) ? $solde : 0;
            $soldes_credit[$num_compte][$exercice] = ($solde < 0) ? abs($solde) : 0;

            if(!in_array($num_compte,$comptes_str))
            {
                $comptes_str[] = $num_compte;
                $comptes[$num_compte] = $compte;
            }
        }

        //TIERS
        $query_tiers = $this->createQueryBuilder('e')
            ->leftJoin('e.tiers','t')
            ->leftJoin('e.journalDossier','jd')
            ->leftJoin('jd.journal','j')
            ->select('e as tiers,ROUND(SUM(e.debit),2) as db,ROUND(SUM(e.credit),2) as cr')
            ->where('j.id <> :id_journal')
            ->setParameter('id_journal',1)
            ->andWhere('e.tiers IS NOT NULL')
            ->andWhere('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN(:exercice)')
            ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $query_tiers = $query_tiers->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $query_tiers = $query_tiers->groupBy('t.type')
            ->getQuery()
            ->getResult();
        foreach($query_tiers as $ligne)
        {
            $tiers = $ligne['tiers']->getTiers();
            $compte = $this->getEntityManager()->getRepository('AppBundle:Tiers')
                ->getPcc($tiers);
            $num_compte = $compte->getCompte();
            $exercice = $ligne['tiers']->getExercice();

            if(!in_array($num_compte,$comptes_str))
            {
                $comptes_str[] = $num_compte;
                $comptes[$num_compte] = $compte;

                $debits[$num_compte][$exercice] = round($ligne['db'],2);
                $credits[$num_compte][$exercice] = round($ligne['cr'],2);

                $soldes_debit[$num_compte][$exercice] = $soldes_credit[$num_compte][$exercice] = 0;
            }
        }

        $query_tiers_an = $this->createQueryBuilder('e')
            ->leftJoin('e.tiers','t')
            ->leftJoin('e.journalDossier','jd')
            ->leftJoin('jd.journal','j')
            ->select('e as tiers,ROUND(SUM(e.debit) - SUM(e.credit),2) as solde')
            ->where('j.id = :id_journal')
            ->setParameter('id_journal',1)
            ->andWhere('e.tiers IS NOT NULL')
            ->andWhere('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN(:exercice)')
            ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $query_tiers_an = $query_tiers_an->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $query_tiers_an = $query_tiers_an->groupBy('t.type')
            ->getQuery()
            ->getResult();
        foreach($query_tiers_an as $ligne)
        {
            $tiers = $ligne['tiers']->getTiers();
            $compte = $this->getEntityManager()->getRepository('AppBundle:Tiers')
                ->getPcc($tiers);
            $num_compte = $compte->getCompte();
            $exercice = $ligne['tiers']->getExercice();
            $solde = round($ligne['solde'],2);

            if(!in_array($num_compte,$comptes_str))
            {
                $comptes_str[] = $num_compte;
                $comptes[$num_compte] = $compte;

                $debits[$num_compte][$exercice] = ($solde > 0) ? $solde : 0;
                $credits[$num_compte][$exercice] = ($solde < 0) ? abs($solde) : 0;

                $soldes_debit[$num_compte][$exercice] = $soldes_credit[$num_compte][$exercice] = 0;
            }
            else
            {
                try {
                    if ($solde > 0) $debits[$num_compte][$exercice] += $solde;
                    else $credits[$num_compte][$exercice] += abs($solde);
                }
                catch (\Exception $ex) {

                }
            }
        }

        //solde tiers
        $query_solde_tiers = $this->createQueryBuilder('e')
            ->leftJoin('e.tiers','t')
            ->select('e as tiers,ROUND(SUM(e.debit) - SUM(e.credit),2) as solde')
            ->andWhere('e.tiers IS NOT NULL')
            ->andWhere('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN(:exercice)')
            ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $query_solde_tiers = $query_solde_tiers->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $query_solde_tiers = $query_solde_tiers->groupBy('t')
            ->getQuery()
            ->getResult();

        foreach($query_solde_tiers as $ligne)
        {
            $tiers = $ligne['tiers']->getTiers();
            $compte = $this->getEntityManager()->getRepository('AppBundle:Tiers')
                ->getPcc($tiers);
            $num_compte = $compte->getCompte();
            $exercice = $ligne['tiers']->getExercice();
            $solde = $ligne['solde'];

            try{
                if($solde > 0) $soldes_debit[$num_compte][$exercice] += $solde;
                else $soldes_credit[$num_compte][$exercice] += abs($solde);
            }
            catch (\Exception $ex) {
            }

        }

        $delete_index = array();
        if($avec_solde == 0)
        {
            $index = 0;
            foreach($comptes_str as $compte)
            {
                $count_solde = 0;
                foreach($exercices as $exercice)
                {
                    try
                    {
                        if(isset($debits[$compte][$exercice]))
                        {
                            if (round($debits[$compte][$exercice], 2) == round($credits[$compte][$exercice], 2)) $count_solde++;
                        }
                        else $count_solde++;
                    } catch (\Exception $ex) {
                        $count_solde++;
                    }
                }
                if($count_solde == count($exercices)) $delete_index[] = $index;
                $index++;
            }
        }

        for($i=count($delete_index)-1; $i>=0 ;$i--)
        {
            unset($comptes_str[$delete_index[$i]]);
        }

        sort($comptes_str);

        return new Balance($comptes,$debits,$credits,$soldes_debit,$soldes_credit,$exercices,$comptes_str);
    }
    public function getBalanceTier($dossier,$exercices,$mois,$type,$avec_solde)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $donnees = $this->createQueryBuilder('e')
                        ->select('e as balance,ROUND(SUM(e.debit),2) as db,ROUND(SUM(e.credit),2) as cr')
                        ->leftJoin('e.tiers','tiers')
                        ->leftJoin('e.journalDossier','jd')
                        ->leftJoin('jd.journal','j')
                        ->where('e.dossier = :dossier')
                        ->andWhere('e.tiers IS NOT NULL')
                        ->setParameter('dossier',$dossier)
                        ->andWhere('e.exercice IN (:exercice)')
                        ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $donnees = $donnees->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $donnees = $donnees->andWhere('tiers.type = :type')
                        ->setParameter('type',$type)
                        ->groupBy('tiers')
                        ->addGroupBy('e.exercice')
                        ->orderBy('tiers.compteStr','ASC')
                        ->andWhere('j.id <> :id_journal')->setParameter('id_journal',1)->getQuery()->getResult();

        $donnees_an = $this->createQueryBuilder('e')
                        ->select('e as balance,ROUND(SUM(e.debit),2) as db,ROUND(SUM(e.credit),2) as cr')
                        ->leftJoin('e.tiers','tiers')
                        ->leftJoin('e.journalDossier','jd')
                        ->leftJoin('jd.journal','j')
                        ->where('e.dossier = :dossier')
                        ->andWhere('e.tiers IS NOT NULL')
                        ->setParameter('dossier',$dossier)
                        ->andWhere('e.exercice IN (:exercice)')
                        ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $donnees_an = $donnees_an->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $donnees_an = $donnees_an->andWhere('tiers.type = :type')
                        ->setParameter('type',$type)
                        ->groupBy('tiers')
                        ->addGroupBy('e.exercice')
                        ->orderBy('tiers.compteStr','ASC')
                        ->andWhere('j.id = :id_journal')->setParameter('id_journal',1)->getQuery()->getResult();

        $comptes_str = array();
        $comptes = array();
        $debits = array();
        $credits = array();
        $soldes_debit = array();
        $soldes_credit = array();

        foreach($donnees as $balance)
        {
            $compte = $balance['balance']->getTiers();
            $exercice = $balance['balance']->getExercice();
            $debit = $balance['db'];
            $credit = $balance['cr'];
            $num_compte =$compte->getCompteStr();

            $debits[$num_compte][$exercice] = $debit;
            $credits[$num_compte][$exercice] = $credit;

            $solde = $debit - $credit;
            $soldes_debit[$num_compte][$exercice] = $soldes_credit[$num_compte][$exercice] = 0;
            if($solde > 0)
            {
                $soldes_debit[$num_compte][$exercice] = $solde;
            }
            else
            {
                $soldes_credit[$num_compte][$exercice] = abs($solde);
            }

            if(!in_array($num_compte,$comptes_str))
            {
                $comptes[$num_compte] = $compte;
                $comptes_str[] = $num_compte;
            }
        }
        foreach($donnees_an as $balance_an)
        {
            $compte = $balance_an['balance']->getTiers();
            $exercice = $balance_an['balance']->getExercice();
            $solde = $balance_an['db'] - $balance_an['cr'];
            $num_compte = $compte->getCompteStr();

            $debit = $credit = 0;
            if($solde > 0) $debit = $solde;
            else $credit = abs($solde);

            if(!in_array($num_compte,$comptes_str))
            {
                $debits[$num_compte][$exercice] = $soldes_debit[$num_compte][$exercice] = $debit;
                $credits[$num_compte][$exercice] = $soldes_credit[$num_compte][$exercice] = $credit;

                $comptes_str[] = $num_compte;
                $comptes[$num_compte] = $compte;
            }
            else
            {
                if($solde > 0) $debits[$num_compte][$exercice] += $solde;
                else $credits[$num_compte][$exercice] += abs($solde);

                $solde = $debits[$num_compte][$exercice] - $credits[$num_compte][$exercice];
                $soldes_debit[$num_compte][$exercice] = $soldes_credit[$num_compte][$exercice] = 0;
                if($solde > 0) $soldes_debit[$num_compte][$exercice] = $solde;
                else $soldes_credit[$num_compte][$exercice] = abs($solde);
            }
        }

        $delete_index = array();
        if($avec_solde == 0)
        {

            $index = 0;
            foreach($comptes_str as $compte)
            {
                //$compte = $compte_item->getCompteStr();
                $count_solde = 0;
                foreach($exercices as $exercice)
                    try
                    {
                        if(isset($debits[$compte][$exercice]))
                        {
                            if(round($debits[$compte][$exercice],2) == round($credits[$compte][$exercice],2)) $count_solde ++;
                        }
                        else $count_solde++;
                } catch (\Exception $ex) {
                    $count_solde++;
                }

                if($count_solde == count($exercices)) $delete_index[] = $index;
                $index++;
            }
        }

        for($i=count($delete_index)-1; $i>=0 ;$i--)
        {
            unset($comptes_str[$delete_index[$i]]);
        }

        sort($comptes_str);

        return new Balance($comptes,$debits,$credits,$soldes_debit,$soldes_credit,$exercices,$comptes_str);
    }

    public function getJournaux($dossier,$exercices,$mois,$journal)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $journal = $this->getEntityManager()->getRepository('AppBundle:JournalDossier')
            ->createQueryBuilder('jd')
            ->where('jd.id = :id')
            ->setParameter('id',$journal)
            ->getQuery()
            ->getOneOrNullResult();

        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.journalDossier','jd')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN (:exercice)')
            ->setParameter('exercice',$exercices);

        if(!is_bool($mois))
            $query = $query->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);

        if($journal != null)
            $query = $query->andWhere('e.journalDossier = :journalDossier')
                            ->setParameter('journalDossier',$journal);

        return $query->orderBy('e.dateEcr','ASC')
                     ->addOrderBy('jd.codeStr','ASC')
                     ->getQuery()->getResult();
    }

    public function getGrandLivre($dossier,$exercices,$mois,$avec_solde,$compte = 0)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $pcc = $this->getEntityManager()->getRepository('AppBundle:Pcc')
            ->createQueryBuilder('pcc')
            ->where('pcc.id = :id')
            ->setParameter('id',$compte)
            ->getQuery()
            ->getOneOrNullResult();

        //Non A Nouveau
        $donnees = $this->createQueryBuilder('e')
                        ->leftJoin('e.journalDossier','jd')
                        ->leftJoin('jd.journal','j')
                        ->leftJoin('e.pcc','pcc')
                        ->where('e.dossier = :dossier')
                        ->setParameter('dossier',$dossier)
                        ->andWhere('e.exercice IN (:exercice)')
                        ->setParameter('exercice',$exercices)
                        ->andWhere('e.pcc IS NOT NULL')
                        ->andWhere('j.id <> :id_journal')
                        ->setParameter('id_journal',1)
                        ->orderBy('pcc.compte','ASC')
                        ->orderBy('e.dateEcr','ASC')
                        ->addOrderBy('jd.codeStr','ASC');
        if($avec_solde == 0)
            $donnees = $donnees->andWhere("e.lettrage = ''");
        if(!is_bool($mois))
            $donnees = $donnees->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        if($pcc != null)
            $donnees = $donnees->andWhere('e.pcc = :pcc')
                ->setParameter('pcc',$pcc);
        $donnees = $donnees->getQuery()->getResult();

        //A Nouveau
        $donnees_an = $this->createQueryBuilder('e')
                        ->select('e as gl, SUM(e.debit) as db, SUM(e.credit) as cr')
                        ->leftJoin('e.journalDossier','jd')
                        ->leftJoin('jd.journal','j')
                        ->leftJoin('e.pcc','pcc')
                        ->where('e.dossier = :dossier')
                        ->setParameter('dossier',$dossier)
                        ->andWhere('e.exercice IN (:exercice)')
                        ->setParameter('exercice',$exercices)
                        ->andWhere('e.pcc IS NOT NULL')
                        ->andWhere('j.id = :id_journal')
                        ->setParameter('id_journal',1)
                        ->groupBy('pcc')
                        ->orderBy('pcc.compte','ASC')
                        ->addOrderBy('e.dateEcr','ASC')
                        ->addOrderBy('jd.codeStr','ASC');
        if($avec_solde == 0)
            $donnees_an = $donnees_an->andWhere("e.lettrage = ''");
        if(!is_bool($mois))
            $donnees_an = $donnees_an->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        if($pcc != null)
            $donnees_an = $donnees_an->andWhere('e.pcc = :pcc')
                ->setParameter('pcc',$pcc);
        $donnees_an = $donnees_an->getQuery()->getResult();

        $comptes = array();
        $libelles_comptes = array();
        $ecritures = array();
        $ecritures_an = array();
        $solde_tiers_an = array();

        foreach($donnees_an as $donnee_an)
        {
            $num_compte = $donnee_an['gl']->getPcc()->getCompte();
            if(!in_array($num_compte,$comptes))
            {
                $comptes[] = $num_compte;
                $libelles_comptes[$num_compte] = $donnee_an['gl']->getPcc()->getIntitule();
                $ecritures_an[$num_compte] = array();
            }
            $ecritures_an[$num_compte] = $donnee_an;
        }

        foreach($donnees as $donnee)
        {
            $num_compte = $donnee->getPcc()->getCompte();
            if(!in_array($num_compte,$comptes))
            {
                $comptes[] = $num_compte;
                $libelles_comptes[$num_compte] = $donnee->getPcc()->getIntitule();
                $ecritures[$num_compte] = array();
            }
            $ecritures[$num_compte][] = $donnee;
        }

        if($compte == null) {
            //TIERS
            $query_tiers = $this->createQueryBuilder('e')
                ->leftJoin('e.tiers', 't')
                ->leftJoin('e.journalDossier', 'jd')
                ->leftJoin('jd.journal', 'j')
                ->select('e as tiers,ROUND(SUM(e.debit),2) as debit,ROUND(SUM(e.credit),2) as credit')
                ->where('j.id <> :id_journal')
                ->setParameter('id_journal', 1)
                ->andWhere('e.tiers IS NOT NULL')
                ->andWhere('e.dossier = :dossier')
                ->setParameter('dossier', $dossier)
                ->andWhere('e.exercice IN(:exercice)')
                ->setParameter('exercice', $exercices);
            if ($avec_solde == 0)
                $query_tiers = $query_tiers->andWhere("e.lettrage = ''");
            if (!is_bool($mois))
                $query_tiers = $query_tiers->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                    ->setParameter('mois', $mois);
            $query_tiers = $query_tiers->groupBy('e.tiers')
                ->getQuery()
                ->getResult();
            foreach ($query_tiers as $ligne) {
                $tiers = $ligne['tiers']->getTiers();
                $compte = $this->getEntityManager()->getRepository('AppBundle:Tiers')
                    ->getPcc($tiers);
                $num_compte = $compte->getCompte();

                if (!in_array($num_compte, $comptes)) {
                    $comptes[] = $num_compte;
                    $libelles_comptes[$num_compte] = $compte->getIntitule();
                    $ecritures[$num_compte] = array();
                }
                $ecritures[$num_compte][] = $ligne;
            }

            $query_tiers_an = $this->createQueryBuilder('e')
                ->leftJoin('e.tiers', 't')
                ->leftJoin('e.journalDossier', 'jd')
                ->leftJoin('jd.journal', 'j')
                ->select('e as tiers,ROUND(SUM(e.debit) - SUM(e.credit),2) as solde')
                ->where('j.id = :id_journal')
                ->setParameter('id_journal', 1)
                ->andWhere('e.tiers IS NOT NULL')
                ->andWhere('e.dossier = :dossier')
                ->setParameter('dossier', $dossier)
                ->andWhere('e.exercice IN(:exercice)')
                ->setParameter('exercice', $exercices);
            if ($avec_solde == 0)
                $query_tiers_an = $query_tiers_an->andWhere("e.lettrage = ''");
            if (!is_bool($mois))
                $query_tiers_an = $query_tiers_an->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                    ->setParameter('mois', $mois);
            $query_tiers_an = $query_tiers_an->groupBy('e.tiers')
                ->getQuery()
                ->getResult();
            foreach ($query_tiers_an as $ligne) {
                $num_compte = $ligne['tiers']->getTiers()->getCompteStr();
                $solde_tiers_an[$num_compte] = $ligne;
            }
            //fin tiers
        }

        sort($comptes);

        return new Balance($comptes,$ecritures,$ecritures_an,$libelles_comptes,$solde_tiers_an,null);
    }

    public function getGrandLivreTiers($dossier,$exercices,$mois,$type,$avec_solde,$compte = 0)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $tiers = $this->getEntityManager()->getRepository('AppBundle:Tiers')
            ->createQueryBuilder('tiers')
            ->where('tiers.id = :id')
            ->setParameter('id',$compte)
            ->getQuery()
            ->getOneOrNullResult();

        if($tiers != null) $type = $tiers->getType();

        //return $tiers;

        //Sans A Nouveau
        $donnees = $this->createQueryBuilder('e')
            ->leftJoin('e.journalDossier','jd')
            ->leftJoin('jd.journal','j')
            ->leftJoin('e.tiers','tiers')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN (:exercice)')
            ->setParameter('exercice',$exercices)
            ->andWhere('e.tiers IS NOT NULL')
            ->andWhere('j.id <> :journal_id')
            ->setParameter('journal_id',1)
            ->andWhere('tiers.type = :type')
            ->setParameter('type',$type)
            ->orderBy('tiers.compteStr','ASC')
            ->addOrderBy('e.dateEcr','ASC')
            ->addOrderBy('jd.codeStr','ASC');
        if($avec_solde == 0)
            $donnees = $donnees->andWhere("e.lettrage = ''");
        if(!is_bool($mois))
            $donnees = $donnees->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        if($tiers != null)
            $donnees = $donnees->andWhere('e.tiers = :tiers')
                ->setParameter('tiers',$tiers);
        $donnees = $donnees->getQuery()->getResult();

        //A Nouveau
        $donnees_an = $this->createQueryBuilder('e')
            ->select('e as gl, SUM(e.debit) as db, SUM(e.credit) as cr')
            ->leftJoin('e.journalDossier','jd')
            ->leftJoin('jd.journal','j')
            ->leftJoin('e.tiers','tiers')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN (:exercice)')
            ->setParameter('exercice',$exercices)
            ->andWhere('e.tiers IS NOT NULL')
            ->andWhere('j.id = :journal_id')
            ->setParameter('journal_id',1)
            ->andWhere('tiers.type = :type')
            ->setParameter('type',$type)
            ->groupBy('tiers');
        if($avec_solde == 0)
            $donnees_an = $donnees_an->andWhere("e.lettrage = ''");
        if(!is_bool($mois))
            $donnees_an = $donnees_an->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        if($tiers != null)
            $donnees_an = $donnees_an->andWhere('e.tiers = :tiers')
                ->setParameter('tiers',$tiers);
        $donnees_an = $donnees_an->getQuery()->getResult();

        $comptes = array();
        $libelles_comptes = array();
        $ecritures = array();
        $ecritures_an = array();

        foreach($donnees_an as $donnee_an)
        {
            $num_compte = $donnee_an['gl']->getTiers()->getCompteStr();
            if(!in_array($num_compte,$comptes))
            {
                $comptes[] = $num_compte;
                $libelles_comptes[$num_compte] = $donnee_an['gl']->getTiers()->getIntitule();
            }

            $ecritures_an[$num_compte] = $donnee_an;
        }

        foreach($donnees as $donnee)
        {
            $num_compte = $donnee->getTiers()->getCompteStr();
            if(!in_array($num_compte,$comptes))
            {
                $comptes[] = $num_compte;
                $libelles_comptes[$num_compte] = $donnee->getTiers()->getIntitule();
                $ecritures[$num_compte] = array();
            }

            $ecritures[$num_compte][] = $donnee;
        }
        return new Balance($comptes,$ecritures,$ecritures_an,$libelles_comptes,null,null);
    }

    public function getJournalCentralisateur($dossier,$exercices,$mois)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $date_cloture = $this->getEntityManager()->getRepository('AppBundle:Dossier')->getDateCloture($dossier,$exercices[0]);
        $date_cloture->add(new \DateInterval('P1D'));
        $date_cloture = new \DateTime(($exercices[0] - 1).'-'.$date_cloture->format('m').'-01');

        $donnees = $this->createQueryBuilder('e')
            ->select("e as jnl, SUM(e.debit) as db, SUM(e.credit) as cr, CASE WHEN j.id = 1 THEN '".$date_cloture->format('Ym')."' ELSE DATE_FORMAT(e.dateEcr,'%Y%m') END as ma")
            ->leftJoin('e.journalDossier', 'jd')
            ->leftJoin('jd.journal','j')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->andWhere('e.exercice IN (:exercice)')
            ->setParameter('exercice', $exercices);

        if (!is_bool($mois))
            $donnees = $donnees->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois', $mois);

        $donnees = $donnees->groupBy('ma')
            ->addGroupBy('jd')
            ->orderBy('ma', 'ASC')
            ->addOrderBy('jd.codeStr', 'ASC')
            ->getQuery()->getResult();

        return new Balance($donnees, null, null, null, null, null);
    }

    public function getBalanceAgeeTier($dossier,$exercices,$mois,$type,$periode_agee)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $date_cloture = $this->getEntityManager()->getRepository('AppBundle:Dossier')->getDateCloture($dossier,$exercices[0]);

        $req = $this->createQueryBuilder('e')
            ->select('ROUND(SUM(e.debit) - SUM(e.credit),2) as solde , tiers.intitule , tiers.compteStr')
            ->leftJoin('e.tiers','tiers')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN(:exercice)')
            ->setParameter('exercice',$exercices)
            ->andWhere('e.tiers IS NOT NULL')
            ->andWhere('tiers.type = :type')
            ->setParameter('type',$type)
            ->groupBy('tiers');
        if(!is_bool($mois))
            $req = $req->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);

        $comptes = array();
        $soldes = array();
        $libelles = array();

        $periode_agee[] = 0;
        for($i = 0;$i < count($periode_agee);$i++)
        {
            $req_temp = clone $req;
            if($i == 0)
            {
                $periode_inf = $periode_agee[$i];
                $req_temp = $req_temp->andWhere("DATE_DIFF(:datecloture,e.dateEcr) > :periode_inf")
                    ->setParameter('periode_inf',$periode_inf)
                    ->setParameter('datecloture',$date_cloture)
                    ->getQuery()->getResult();
            }
            else
            {
                $periode_sup = $periode_agee[$i - 1];
                $periode_inf = $periode_agee[$i];
                $req_temp = $req_temp->andWhere('DATE_DIFF(:datecloture,e.dateEcr) <= :periode_sup')
                    ->setParameter('periode_sup',$periode_sup);
                if($i != count($periode_agee) - 1) $req_temp = $req_temp->andWhere('DATE_DIFF(:datecloture,e.dateEcr) > :periode_inf')
                    ->setParameter('periode_inf',$periode_inf);
                $req_temp = $req_temp->setParameter('datecloture',$date_cloture)->getQuery()->getResult();
            }

            foreach($req_temp as $ligne)
            {
                $num_compte = $ligne['compteStr'];
                if(!in_array($num_compte,$comptes))
                {
                    $comptes[] = $num_compte;
                    $libelles[$num_compte] = $ligne['intitule'];
                }
                $soldes[$num_compte][$periode_agee[$i]] = $ligne['solde'];
            }
        }

        $debiteurs = array();
        $crediteurs = array();
        $totals = array();

        $index = 0;
        $delete_index = array();
        foreach($comptes as $compte)
        {
            $solde = 0;
            foreach($periode_agee as $periode)
            {
                if(isset($soldes[$compte][$periode])) $solde += $soldes[$compte][$periode];
                else $soldes[$compte][$periode] = 0;
            }

            if($solde == 0) $delete_index[] = $index;
            elseif($solde > 0)
            {
                $debiteurs[] = $compte;
                $totals[$compte] = $solde;
            }
            else
            {
                $crediteurs[] = $compte;
                $totals[$compte] = $solde;
            }
            $index++;
        }

        for($i=count($delete_index)-1; $i>=0 ;$i--)
        {
            unset($comptes[$delete_index[$i]]);
        }

        sort($debiteurs);
        sort($crediteurs);

        return new Balance($debiteurs,$crediteurs,$libelles,$soldes,$periode_agee,$totals);
    }
    //FIN ETAT DE BASE

    //ETAT FINANCIER
    public function getMontantPcc(EtatCompte $etatCompte,Dossier $dossier,$exercice,$mois)
    {
        $req = $this->createQueryBuilder('e')
            ->select('ROUND(SUM(e.debit) - SUM(e.credit),2) as solde, e')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice = :exercice')
            ->setParameter('exercice',$exercice)
            ->andWhere('e.pcc IS NOT NULL')
            ->andWhere('e.pcc = :pcc')
            ->setParameter('pcc',$etatCompte->getPcc());
        if(!is_bool($mois))
            $req = $req->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);
        $req = $req->getQuery()->getOneOrNullResult();
        $montant = $req['solde'];

        $part_compte = substr($etatCompte->getPcc()->getCompte(),0,5);
        if($montant == 0 && ($part_compte == '41100' || $part_compte == '40100'))
        {
            if($part_compte == '40100') $type = 0;
            else $type = 1;
            //solde tiers
            $query_solde_tiers = $this->createQueryBuilder('e')
                ->leftJoin('e.tiers','t')
                ->select('ROUND(SUM(e.debit) - SUM(e.credit),2) as solde')
                ->andWhere('e.tiers IS NOT NULL')
                ->andWhere('e.dossier = :dossier')
                ->setParameter('dossier',$dossier)
                ->andWhere('e.exercice = :exercice')
                ->setParameter('exercice',$exercice)
                ->andWhere('t.type = :type')
                ->setParameter('type',$type);
            if(!is_bool($mois))
                $query_solde_tiers = $query_solde_tiers->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                    ->setParameter('mois',$mois);
            $query_solde_tiers = $query_solde_tiers->groupBy('t')
                ->getQuery()
                ->getResult();
            $solde_debit = 0;
            $solde_credit = 0;
            foreach($query_solde_tiers as $tier)
            {
                $solde = $tier['solde'];
                if($solde > 0) $solde_debit += $solde;
                else $solde_credit += abs($solde);
            }
            if($etatCompte->getSens() == 1) return $solde_debit;
            elseif($etatCompte->getSens() == 2) return $solde_credit;
            elseif($etatCompte->getSens() == 3) return $solde_debit - $solde_credit;
            else return 0;
        }
        else
        {
            if($etatCompte->getSens() == 1 && $montant > 0) return $montant;
            elseif($etatCompte->getSens() == 2 && $montant < 0) return $montant;
            elseif($etatCompte->getSens() == 3) return $montant;
            else return 0;
        }
    }
    //FIN ETAT FINANCIER

    public function getControle($dossier,$exercices,$mois)
    {
        $resultats = array();
        $erreur = false;

        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $dossier)
            ->getQuery()
            ->getOneOrNullResult();
        $req_1_5 = $this->createQueryBuilder('e')
            ->select('ROUND(SUM(e.debit),2) - ROUND(SUM(e.credit),2) as resultat')
            ->leftJoin('e.pcc','pcc')
            ->where('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('e.exercice IN (:exercice)')
            ->setParameter('exercice',$exercices);
        if(!is_bool($mois))
            $req_1_5 = $req_1_5->andWhere("DATE_FORMAT(e.dateEcr,'%m') IN (:mois)")
                ->setParameter('mois',$mois);

        $req_6_7 = clone $req_1_5;

        $r_1_5 = $req_1_5->andWhere('e.tiers IS NOT NULL OR SUBSTRING(pcc.compte,1,1) < 6')->getQuery()->getOneOrNullResult()['resultat'];
        $r_6_7 = $req_6_7->andWhere('e.tiers IS NULL')->andWhere('SUBSTRING(pcc.compte,1,1) > 5')->getQuery()->getOneOrNullResult()['resultat'];

        if($r_1_5 + $r_6_7 != 0) $erreur = true;

        $resultats['Balance'] = $r_1_5;
        $resultats['Grand livre'] = $r_1_5;

        return new Balance($resultats,$erreur,null,null,null,null);
    }

    public function getDerniereMAJ($exercice,$dossier)
    {
        $dossier = $this->getEntityManager()->getRepository('AppBundle:Dossier')
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $dossier)
            ->getQuery()
            ->getOneOrNullResult();

        $date = $this->createQueryBuilder('e')
            ->leftJoin('e.historiqueUpload','h')
            ->select('MAX(h.dateUpload) as date')
            ->where('e.exercice = :exercice')
            ->setParameter('exercice',$exercice)
            ->andWhere('e.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->groupBy('e.dossier')
            ->getQuery()
            ->getOneOrNullResult();

        return ($date != null) ? (new \DateTime(explode(' ',$date['date'])[0])) : null;
    }

    /**
     * @param $dossierid
     * @param $siren
     * @return array
     */
    public function getEcrituresByDossierSiren($dossierid,  $siren){

        $query = "SELECT E.*, I.nom as image_nom, I.id AS image_id FROM imputation_controle IC 
                        INNER JOIN image I ON I.id = IC.image_id 
                        INNER JOIN lot L ON L.id = I.lot_id
                        INNER JOIN ecriture E on E.image_id = I.id
                        WHERE IC.siret LIKE :siren AND E.dossier_id = :dossierid ORDER BY E.image_id";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute([
            'dossierid' => $dossierid,
            'siren' => '%'.$siren.'%'
        ]);

        $res = $prep->fetchAll();

        return $res;
    }


    public function getEcrituresByDossierTiers($dossierid,  $tiersid){

        $query = "SELECT E.*, I.nom as image_nom, I.id AS image_id FROM imputation_controle IC 
                        INNER JOIN image I ON I.id = IC.image_id 
                        INNER JOIN lot L ON L.id = I.lot_id
                        INNER JOIN ecriture E on E.image_id = I.id
                        WHERE E.image_id IN (SELECT image_id FROM ecriture WHERE tiers_id = :tiersid) 
                        AND E.dossier_id = :dossierid ORDER BY E.image_id";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute([
            'dossierid' => $dossierid,
            'tiersid' => $tiersid
        ]);

        $res = $prep->fetchAll();

        return $res;
    }

    public function getEcrituresByDossierLibelle($dossierid,  $libelle){

        $query = "SELECT E.*, I.nom as image_nom, I.id AS image_id FROM imputation_controle IC 
                        INNER JOIN image I ON I.id = IC.image_id 
                        INNER JOIN lot L ON L.id = I.lot_id
                        INNER JOIN ecriture E on E.image_id = I.id
                        WHERE  E.image_id IN (SELECT image_id FROM ecriture WHERE libelle LIKE :libelle) 
                        AND E.dossier_id = :dossierid";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $prep = $pdo->prepare($query);
        $prep->execute([
            'dossierid' => $dossierid,
            'libelle' => '%'.$libelle.'%'
        ]);

        $res = $prep->fetchAll();

        return $res;
    }


    public function getEcrituresByImage(Image $image, &$typeEcriture){
        /** @var Ecriture[] $ecritures */
        $ecritures = $this->createQueryBuilder('e')
            ->where('e.image = :image')
            ->setParameter('image', $image)
            ->getQuery()
            ->getResult();

        $sommeDebit = 0;
        $sommeCredit = 0;

        $typeEcriture = 0;

        if(count($ecritures) > 0) {
            foreach ($ecritures as $ecriture) {
                $sommeDebit += $ecriture->getDebit();
                $sommeCredit += $ecriture->getCredit();
            }

            $typeEcriture = 1;

//            if (abs($sommeCredit - $sommeDebit) <= 0.1 ) {
//                $typeEcriture = 1;
//                return $ecritures;
//            }
//            else{
//                $typeEcriture = -1;
//            }

        }
        return $ecritures;
    }

}