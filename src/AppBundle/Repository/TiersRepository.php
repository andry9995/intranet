<?php
namespace AppBundle\Repository;
use AppBundle\Entity\Categorie;
use AppBundle\Entity\Dossier;
use Doctrine\ORM\EntityRepository;


class TiersRepository extends EntityRepository
{
    public function getPcc($tiers)
    {
        $part_compte = 'xxxx';
        if($tiers->getType() == 0) $part_compte = '40100%';
        if($tiers->getType() == 1) $part_compte = '41100%';

        return $this->getEntityManager()->getRepository('AppBundle:Pcc')
            ->createQueryBuilder('pcc')
            ->where('pcc.dossier = :dossier')
            ->setParameter('dossier',$tiers->getDossier())
            ->andWhere('pcc.compte LIKE :part_compte')
            ->setParameter('part_compte',$part_compte)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTiersByLibelle(Dossier $dossier, $libelle){
        $tiers = $this->createQueryBuilder('t')
            ->where('t.dossier = :dossier')
            ->andWhere('t.compteStr = :libelle')
            ->setParameter('dossier', $dossier)
            ->setParameter('libelle', $libelle)
            ->getQuery()
            ->getResult();

        if(count($tiers) > 0){
            return $tiers[0];
        }
        return null;
    }

    /**
     * @param Dossier $dossier
     * @param Categorie|null $categorie
     * @return array
     */
    public function getTiersByCategorie(Dossier $dossier, Categorie $categorie = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.dossier = :dossier')
            ->setParameter('dossier', $dossier);;

        $typeTiers = null;
        if ($categorie !== null) {
            if($categorie->getCode() === 'CODE_CLIENT'){
                $typeTiers = 1;
            }
            elseif ($categorie->getCode() === 'CODE_FRNS'){
                $typeTiers = 0;
            }
            elseif ($categorie->getCode() === 'CODE_NDF'){
                $typeTiers = 3;
            }
        }

        if ($typeTiers !== null)
            $qb = $qb->andWhere('t.type = :typeTiers')
                ->setParameter('typeTiers', $typeTiers)
                ->orderBy('t.compteStr', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }
}