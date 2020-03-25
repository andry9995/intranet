<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 06/10/2016
 * Time: 10:53
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Dossier;
use AppBundle\Entity\Pcc;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class PccRepository extends EntityRepository
{
    /**
     * @param array $pcgs
     * @param Dossier $dossier
     * @param array $pcgsOut
     * @param bool $compteAttente
     * @return array
     */
    public function getPCCByPCG(array $pcgs = array(),Dossier $dossier,$pcgsOut = array(),$compteAttente = false)
    {
        $regex = '';

        foreach ($pcgs as $key => $pcg) $regex .= '^'.$pcg->getCompte().'|';
        $regex .= '^xxxxxxx' . (($compteAttente) ? '|^4710' : '');
        $reg = '^[0-9]+$';

        if($regex == '') return array();

        $results = $this->createQueryBuilder('pcc')
            ->where('pcc.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('REGEXP(pcc.compte, :reg) = true')
            ->setParameter('reg',$reg)
            ->andWhere('REGEXP(pcc.compte, :regex) = true')
            ->setParameter('regex',$regex);

        if(count($pcgsOut) > 0)
        {
            $regexOut = '';
            for($i = 0; $i < count($pcgsOut);$i++)
            {
                $regexOut .= '^'.$pcgsOut[$i]->getCompte();
                if($i != count($pcgsOut) - 1) $regexOut .= '|';
            }

            $results = $results->andWhere('REGEXP(pcc.compte, :regexOut) = false')
                ->setParameter('regexOut',$regexOut);
        }

        return $results
            ->orderBy('pcc.compte')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id',$id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Dossier $dossier
     * @return array
     */
    public function getPccs(Dossier $dossier)
    {
        return $this->createQueryBuilder('p')
            ->where('p.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere("p.compte <> ''")
            ->orderBy('p.compte')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Dossier $dossier
     * @return array
     */
    public function getPccsTiers(Dossier $dossier,$type)
    {
        $compte = ($type == 0) ? '401' : '411';

        return $this->createQueryBuilder('p')
            ->where('p.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('p.compte LIKE :compte')
            ->setParameter('compte',$compte.'%')
            ->orderBy('p.compte')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Dossier $dossier
     * @param $type
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPccTier(Dossier $dossier,$type)
    {
        return $this->createQueryBuilder('p')
            ->where('p.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('p.collectifTiers = :type')
            ->setParameter('type',$type)
            ->orderBy('p.compte')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Dossier $dossier
     * @param $pcc
     * @param $type
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setTier(Dossier $dossier,$pcc, $type)
    {
        $oldPcc = $this->createQueryBuilder('p')
            ->where('p.collectifTiers = :type')
            ->setParameter('type',$type)
            ->andWhere('p.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->getQuery()
            ->getOneOrNullResult();

        if($oldPcc != null) $oldPcc->setCollectifTiers(-1);
        if($pcc != null) $pcc->setCollectifTiers($type);
        $tiers = $this->getEntityManager()->getRepository('AppBundle:Tiers')->getTiers($dossier,$type);
        foreach ($tiers as $tier) $tier->setPcc($pcc);
        $em = $this->getEntityManager();
        $em->flush();
        return 1;
    }

    /**
     * @param Dossier $dossier
     * @param $type
     * @return int
     */
    public function getPccTierOfDossier(Dossier $dossier,$type)
    {
        $startCompte = 'xxxx';

        if($type == 0) $startCompte = '401';
        elseif($type == 1) $startCompte = '411';

        $temps = $this->createQueryBuilder('p')
            ->where('p.dossier = :dossier')
            ->setParameter('dossier',$dossier)
            ->andWhere('p.compte LIKE :compte')
            ->setParameter('compte',$startCompte.'%')
            ->orderBy('p.compte','ASC')
            ->getQuery()
            ->getResult();

        if(count($temps) > 1) return $temps[0];
        else return 0;
    }

    public function getPccByDossierLike(Dossier $dossier, $like, $collectifTiers = null){

        $qb = $this->createQueryBuilder('p')
            ->join('p.dossier', 'd')
            ->where('d = :dossier')
            ->andWhere('p.compte like :like')
            ->setParameter('dossier', $dossier)
            ->setParameter('like', $like.'%')
        ;

        if($collectifTiers !== null){
            $qb->andWhere('p.collectifTiers = :collectifTiers')
                ->setParameter('collectifTiers', $collectifTiers);
        }
        return $qb
            ->orderBy('p.compte')
            ->getQuery()->getResult();
    }

    public function getPccByDossierCompte(Dossier $dossier, $compte){
        $pccs = $this->createQueryBuilder('p')
            ->where('p.dossier = :dossier')
            ->andWhere('p.compte = :compte')
            ->setParameter('dossier', $dossier)
            ->setParameter('compte', $compte)
            ->getQuery()
            ->getResult();

        if(count($pccs) > 0){
            return $pccs[0];
        }
        return null;
    }

    public function getPccTiersByDossierArrayLikes(Dossier $dossier, array $likes,array $creates, $operateur){

        $regex = '^'. implode('|^', $likes);
        $reg = '^[0-9]+$';

        $pccs =[];

        if(count($likes) > 0) {

            $pccs = $this->createQueryBuilder('pcc')
                ->where('pcc.dossier = :dossier')
                ->andWhere('REGEXP(pcc.compte, :reg) = true')
                ->andWhere('REGEXP(pcc.compte, :regex) = true')
                ->setParameter('dossier', $dossier)
                ->setParameter('reg', $reg)
                ->setParameter('regex', $regex)
                ->orderBy('pcc.compte', 'ASC')
                ->getQuery()
                ->getResult();
        }

        $tiers = [];

        /** @var Pcc[] $pccs */
        foreach ($pccs as $pcc){

            if($pcc->getCollectifTiers() !== -1) {
                $tmpTiers = $this->getEntityManager()
                    ->getRepository('AppBundle:Tiers')
                    ->findBy(['pcc' => $pcc], ['compteStr' => 'ASC']);

                foreach ($tmpTiers as $tmp){
                    $tiers[] = $tmp;
                }
            }
        }


        if(count($creates) > 0) {

            foreach ($creates as $create) {
                $attentes = $this->createQueryBuilder('pa')
                    ->where('pa.compte like :attente')
                    ->andWhere('pa.dossier = :dossier')
                    ->setParameter('attente', $create . '%')
                    ->setParameter('dossier', $dossier)
                    ->getQuery()
                    ->getResult();

                if (count($attentes) === 0) {
//                $size = $this->getMaxPcc($dossier);

                    $compte = $create;

//                while (strlen($compte) < $size->longueur) {
//                    $compte = $compte . '0';
//                }

                    $pcc = new Pcc();
                    $pcc->setCompte($compte);
                    $pcc->setDossier($dossier);
                    $pcc->setCollectifTiers(-1);
                    $pcc->setStatus(1);
                    $pcc->setOperateur($operateur);
                    $pcc->setIntitule('COMPTE D\'ATTENTE');

                    $em = $this->getEntityManager();

                    $em->persist($pcc);
                    try {
                        $em->flush();
                        $em->refresh($pcc);

                        $pccs[] = $pcc;

                    } catch (\Exception $e) {

                    }
                } else {
                    foreach ($attentes as $attente) {
                        $pccs [] = $attente;
                    }
                }
            }
        }

        return ['tiers' => $tiers, 'pccs'=> $pccs];

    }


    public function getMaxPcc(Dossier $dossier){
        $query = " SELECT MAX(LENGTH(compte)) as longueur FROM pcc where dossier_id = :dossier";

        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $prep = $pdo->prepare($query);


        $prep->execute(['dossier' => $dossier->getId()]);

        $res = $prep->fetchAll();

        if(count($res) > 0){
            return $res[0];
        }

        return null;
    }

    /**
     * @param Dossier $dossier
     * @param $type
     */
    public function getCompteAttente(Dossier $dossier, $type = 0){

        $like = '471600';
        if($type === 1){
            $like = '471700';
        }
        $attentes = $this->getPccByDossierLike($dossier, $like);

        if(count($attentes) > 0){
            return $attentes[0];
        }

        $attente = new Pcc();
        $attente->setDossier($dossier);
        $attente->setCompte($like);
        $attente->setIntitule('COMPTE D\'ATTENTE');

        $em = $this->getEntityManager();
        $em->persist($attente);
        $em->flush();

        return $attente;
    }


}