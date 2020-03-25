<?php
/**
 * Created by PhpStorm.
 * User: TEFY
 * Date: 10/06/2016
 * Time: 11:02
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Operateur;
use Doctrine\ORM\EntityRepository;
use AppBundle\Functions\CustomPdoConnection;


class UserApplicationRepository extends EntityRepository
{
    public function getUserApp(Operateur $operateur)
    {
        $now = new \DateTime();
        $qb = $this->getEntityManager()
            ->getRepository('AppBundle:UserApplication')
            ->createQueryBuilder('a');
        $user_app = $qb
            ->select()
            ->where('a.operateur = :operateur')
            ->andWhere('a.dateJour = :date_jour')
            ->setParameter('operateur', $operateur)
            ->setParameter('date_jour', $now->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();

        return $user_app;
    }

    public function lancerGestionNature($etapeTraitId, $operateurId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id FROM user_application WHERE operateur_id=:userId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId' => $operateurId,
        ));
        $prep->fetch();
        $retour = false;
        if ($prep->rowCount() > 0) {
            $query = "UPDATE user_application SET etape_traitement_id=:etapeId WHERE operateur_id=:userId";
            $prep = $pdo->prepare($query);
            $prep->execute(array(
                'etapeId' => $etapeTraitId,
                'userId' => $operateurId,
            ));
            $retour = true;
        }
        else
            $retour = false;
        return $retour;
    }

    public function lancerCategorieNature($operateurId)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT id FROM user_application WHERE operateur_id=:userId";
        $prep = $pdo->prepare($query);
        $prep->execute(array(
            'userId' => $operateurId,
        ));
        $prep->fetch();
        $retour = false;
        if ($prep->rowCount() > 0) {
            $query = "UPDATE user_application SET parametre='".$operateurId.'|1'."', etape_traitement_id = 25 WHERE operateur_id=".$operateurId;
            $prep = $pdo->prepare($query);
            $prep->execute();
            $retour = true;
        }
        else
            $retour = false;
        return $retour;
    }
}