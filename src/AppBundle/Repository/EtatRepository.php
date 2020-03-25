<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Etat;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\EtatCompte;

class EtatRepository extends EntityRepository
{
    //get all Etat parent
    public function getEtatParent($etat,$dossier,$regime,$exercices = null,$mois = null)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.etatFinancier = :etat')
            ->andWhere('e.etat IS NULL');
        if($regime != null)
            $query = $query->andWhere('e.regimeFiscal = :regime')->setParameter('regime',$regime);
        if($dossier != null)
        {
            $this->getEntityManager()->createQuery('DELETE AppBundle:Etat e WHERE e.dossier = :dossier AND e.etatFinancier = :etat')
                ->setParameter('dossier',$dossier)
                ->setParameter('etat',$etat)
                ->execute();
            $query = $query->andWhere('e.dossier = :dossier')->setParameter('dossier', $dossier);
        }

        $query = $query->setParameter('etat',$etat)->orderBy('e.rang');
        $query_clone = clone $query;
        $etats = $query->getQuery()->getResult();

        if($dossier != null && count($etats) == 0)
        {
            $this->newDossier($dossier,$etat);
            $etats = $query_clone->getQuery()->getResult();
        }
        foreach($etats as &$etat)
        {
            //calcule montant
            if($dossier != null && $exercices != null)
                foreach($exercices as $exercice)
                    $this->setMontant($etat,$exercice,$mois);

            $childs = $this->getEtatChild($etat);
            if(count($childs) > 0)
            {
                //calcule montant
                if($dossier != null && $exercices != null)
                    foreach($childs as &$child)
                        foreach($exercices as $exercice)
                            $this->setMontant($child,$exercice,$mois);
                $etat->setChilds($childs);

                foreach($childs as &$child)
                {
                    $childs2 = $this->getEtatChild($child);
                    if(count($childs2) > 0)
                    {
                        //calcule montant
                        if($dossier != null && $exercices != null)
                            foreach($childs2 as &$child2)
                                foreach($exercices as $exercice)
                                    $this->setMontant($child2,$exercice,$mois);
                        $child->setChilds($childs2);

                        foreach($childs2 as &$child2)
                        {
                            $childs3 = $this->getEtatChild($child2);
                            if(count($childs3) > 0)
                            {
                                //calcule montant
                                if($dossier != null && $exercices != null)
                                    foreach($childs3 as &$child3)
                                        foreach($exercices as $exercice)
                                            $this->setMontant($child3,$exercice,$mois);
                                $child2->setChilds($childs3);

                                foreach($childs3 as &$child3)
                                {
                                    $childs4 = $this->getEtatChild($child3);
                                    if(count($childs4) > 0)
                                    {
                                        //calcule montant
                                        if($dossier != null && $exercices != null)
                                            foreach($childs4 as &$child4)
                                                foreach($exercices as $exercice)
                                                    $this->setMontant($child4,$exercice,$mois);
                                        $child3->setChilds($childs4);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $etats;
    }

    //get menu child
    public function getEtatChild(Etat $etat)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.etat = :etat')
            ->setParameter('etat',$etat)
            ->orderBy('e.rang')
            ->getQuery();

        return $query->getResult();
    }

    //nouveau dossier
    public function newDossier(Dossier $dossier,$etat_financier)
    {
        $em = $this->getEntityManager();
        $modeles = $this->createQueryBuilder('e')
                    ->where('e.regimeFiscal = :regime')
                    ->andwhere('e.etatFinancier = :etatFinancier')
                    ->andWhere('e.etat IS NULL')
                    ->setParameter('regime',$dossier->getRegimeFiscal())
                    ->setParameter('etatFinancier',$etat_financier)
                    ->getQuery()->getResult();

        foreach($modeles as $modele)
        {
            $etat = new Etat();
            $etat->setViaCopy($modele)->setDossier($dossier);
            $etat = $this->insertEtat($modele,$etat);

            $childs_modele = $this->getEtatChild($modele);
            if(count($childs_modele) > 0)
            {
                foreach($childs_modele as $child_modele)
                {
                    $etat_child = new Etat();
                    $etat_child->setViaCopy($child_modele)
                        ->setDossier($dossier)
                        ->setEtat($etat);
                    $etat_child = $this->insertEtat($child_modele,$etat_child);

                    $childs_modele_2 = $this->getEtatChild($child_modele);
                    if(count($childs_modele_2) > 0)
                    {
                        foreach($childs_modele_2 as $child_modele_2)
                        {
                            $etat_child_2 = new Etat();
                            $etat_child_2->setViaCopy($child_modele_2)
                                        ->setDossier($dossier)
                                        ->setEtat($etat_child);
                            $this->insertEtat($child_modele_2,$etat_child_2);
                            $childs_modele_3 = $this->getEtatChild($child_modele_2);
                            if(count($childs_modele_3) > 0)
                            {
                                foreach($childs_modele_3 as $child_modele_3)
                                {
                                    $etat_child_3 = new Etat();
                                    $etat_child_3->setViaCopy($child_modele_3)
                                        ->setDossier($dossier)
                                        ->setEtat($etat_child_2);
                                    $this->insertEtat($child_modele_3,$etat_child_3);
                                    $childs_modele_4 = $this->getEtatChild($child_modele_3);
                                    if(count($childs_modele_4) > 0)
                                    {
                                        foreach($childs_modele_4 as $child_modele_4)
                                        {
                                            $etat_child_4 = new Etat();
                                            $etat_child_4->setViaCopy($child_modele_4)
                                                ->setDossier($dossier)
                                                ->setEtat($etat_child_3);
                                            $this->insertEtat($child_modele_4,$etat_child_4);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $em->flush();
    }

    //insertion nouveau etat
    private function insertEtat(Etat $modele, Etat $etat)
    {
        $em = $this->getEntityManager();
        $em->persist($etat);
        $em->flush();

        $this->insertAllCompte($modele, $etat);
        return $etat;
    }

    //insert all compte etat
    private function insertAllCompte(Etat $modele, Etat $etat)
    {
        $comptes_modele = $this->getEntityManager()->getRepository('AppBundle:EtatCompte')->createQueryBuilder('ec')
                ->where('ec.etat = :etat')
                ->setParameter('etat',$modele)
                ->getQuery()->getResult();

        $em = $this->getEntityManager();
        foreach($comptes_modele as $compte_modele)
        {
            $comptes = $this->getEntityManager()->getRepository('AppBundle:Pcc')->createQueryBuilder('pcc')
                    ->where('pcc.compte LIKE :compte_like')
                    ->andWhere('pcc.dossier = :dossier')
                    ->andWhere('pcc.status = 1')
                    ->setParameter('compte_like',$compte_modele->getPcg()->getCompte().'%')
                    ->setParameter('dossier',$etat->getDossier())
                    ->getQuery()->getResult();
            foreach($comptes as $compte)
            {
                $etat_compte = new EtatCompte();
                $etat_compte->setSens($compte_modele->getSens())
                            ->setBrutAmort($compte_modele->getBrutAmort())
                            ->setEtat($etat)
                            ->setPcc($compte);

                if(!$this->getEntityManager()->getRepository('AppBundle:EtatCompte')->inEntityManager($etat_compte))
                    $em->persist($etat_compte);
            }
            $em->flush();
        }
    }

    private function getMontant(Etat $etat,$exercice,$mois,$brut_amort)
    {
        $montant = 0;
        $comptes = $this->getEntityManager()->getRepository('AppBundle:EtatCompte')->getComptesCocher($etat, $brut_amort, false);
        foreach ($comptes as $compte)
        {
            $montant += $this->getEntityManager()->getRepository('AppBundle:Ecriture')->getMontantPcc($compte,$etat->getDossier(),$exercice,$mois);
        }
        return $montant;
    }

    private function setMontant(Etat &$etat,$exercice,$mois)
    {
        $brut = abs($this->getMontant($etat,$exercice,$mois,1));
        $amort = 0;
        if($etat->getEtatFinancier() == 0) $amort = abs($this->getMontant($etat,$exercice,$mois,0));

        $etat->addToBrut($exercice,$brut);
        $etat->addToAmort($exercice,$amort);
    }
}