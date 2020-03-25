<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;

class UtilisateurRepository extends EntityRepository
{
    public function getUserByLogin($login)
    {
        return $this->createQueryBuilder('u')
            ->where('u.login = :login')
            ->setParameter('login',$this->deboost_old($login))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Client|null $client
     * @return Utilisateur[]
     */
    public function getAllUtilisateurs(Client $client = null)
    {
        if (!$client)
            $client = $this->getEntityManager()->getRepository('AppBundle:Client')
                ->find(626);

        $utilisateurs = $this->getEntityManager()
            ->getRepository('AppBundle:Utilisateur')
            ->createQueryBuilder('u')
            ->where('u.supprimer = :supprimer')
            ->andWhere('u.client = :client')
            ->setParameter('supprimer', 0)
            ->setParameter('client', $client)
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom')
            ->getQuery()
            ->getResult();

        return $utilisateurs;
    }
    //crypter
    public static function boost($str)
    {
        $cle = rtrim(ltrim(com_create_guid(),'{'),'}');
        $cle64 = base64_encode($cle);
        $izy = $cle.$str.$cle64;
        $cle64_1 = base64_encode($izy);
        return $cle64_1;
    }
    //decrypter
    public static function deboost($str)
    {
        $cle64_1 = base64_decode($str);
        $cle = substr($cle64_1,0,36);
        $queue = base64_encode($cle);
        $lenth = strlen($queue);
        $rambony = substr($cle64_1,-$lenth);
        if($rambony != $queue) return false;
        $result = str_replace($rambony,'',$cle64_1);
        $result = str_replace($cle,'',$result);
        return $result;
    }

    //crypter old version of php
    function boost_old($str)
    {
        $cle = uniqid();
        $cle64 = base64_encode($cle);
        $izy = $cle.$str.$cle64;
        $cle64_1 = base64_encode($izy);
        return $cle64_1;
    }

    //decrypter old version php
    function deboost_old($str)
    {
        $cle64_1 = base64_decode($str);
        $cle = substr($cle64_1,0,13);
        $queue = base64_encode($cle);
        $lenth = strlen($queue);
        $rambony = substr($cle64_1,-$lenth);
        if($rambony != $queue) return false;
        $result = str_replace($rambony,'',$cle64_1);
        $result = str_replace($cle,'',$result);
        return $result;
    }
}
?>