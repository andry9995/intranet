<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 01/08/2018
 * Time: 09:19
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Client;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

class CategorieRepository extends EntityRepository
{
    ///
    ///
    /**
     * @param Client $clients
     * @param $exercice
     * @return array
     */
    public function getNbEtapeCategorieByClient(Client $clients, $exercice)
    {
        /*$req = $this->createQueryBuilder('r')
            ->select('r')
            ->innerJoin('AppBundle:ImputationControle', 'ic', 'WITH', 'ic.image = r.image')
            ->leftJoin('ic.soussouscategorie', 'ssc')
            ->innerJoin('r.image','i')
            ->innerJoin('i.lot','l')
            ->innerJoin('r.banqueCompte','bc')
            ->innerJoin('bc.banque','b')
            ->addSelect('i')
            ->where('l.dossier = :dossier')
            ->setParameter('dossier', $dossier)
            ->andWhere('i.exercice = :exercice')
            ->setParameter('exercice',$exercice)
            ->andWhere('(r.eclate = 0 OR r.eclate = 2)')
            ->andWhere('r.banqueCompte IS NOT NULL')
            ->andWhere('ic.soussouscategorie IS NOT NULL')
            ->andWhere('(ssc.libelle != :doublon AND ssc.libelleNew != :doublon) OR ssc.libelle IS NULL')
            //->andWhere('r.imageFlague IS NOT NULL')
            ->setParameter('doublon', 'doublon');*/
        $con = new CustomPdoConnection();
        $pdo = $con->connect();


        $query = "SELECT c.id,
                  c.code,
	              c.libelle_new,  
                    (SELECT count(p.id)   FROM process_client_categorie p 
                        JOIN process_client_categorie_etape p1 ON p.id=p1.process_client_categ_id  WHERE 
                        p.client_id= :client_id AND p.actif=1 AND p.exercice= :exercice 
                        AND p.categorie_id=c.id 
                        GROUP BY p.categorie_id) as nb
                        FROM 
                            categorie c WHERE c.actif=1";

        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'exercice' => $exercice,
            'client_id' => $clients->getId(),
        ));

        return $prep->fetchAll();

    }

    public function getCategoriesByCode($codes){
        return $this->createQueryBuilder('c')
            ->where('c.code IN (:ids)')
            ->setParameter('ids', array_values($codes))
            ->getQuery()
            ->getResult();
    }

    public function getAllCategories(){
        return $this->createQueryBuilder('c')
            ->where('c.actif = 1')
            ->orderBy('c.libelleNew')
            ->getQuery()
            ->getResult();
    }
}