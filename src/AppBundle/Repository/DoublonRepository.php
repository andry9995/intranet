<?php

namespace AppBundle\Repository;
use AppBundle\Functions\CustomPdoConnection;
use Doctrine\ORM\EntityRepository;

/**
 * DoublonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DoublonRepository extends EntityRepository
{
	public function getNatures($dossier)
    {
		$con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT distinct C.libelle_new AS nom,S.categorie_id AS id 
        FROM  image I, lot L,dossier D ,separation S,categorie C
        WHERE  S.image_id = I.id
        AND S.categorie_id = C.id
        AND I.lot_id = L.id
        AND L.dossier_id = D.id
        AND D.id= :dossier
        AND C.id IN (9,10)
        ORDER BY C.libelle_new  DESC";

        $prep = $pdo->prepare($query);
        $prep->execute(array('dossier' => $dossier));
        
        return  $prep->fetchAll();
    }
    public function getExercices($dossier)
    {
		$con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = "SELECT distinct I.exercice AS exec
        FROM  image I, lot L,dossier D 
        WHERE I.lot_id = L.id
        AND L.dossier_id = D.id
        AND D.id= :dossier
        ORDER BY I.exercice  DESC";

        $prep = $pdo->prepare($query);
        $prep->execute(array('dossier' => $dossier));
        
        return  $prep->fetchAll();
    }
    
    public function getListeTrou($dossier,$categorie,$exercice)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT  SIC.num_facture,T.id as tid,L.date_scan, SIC.rs,T.montant_ttc,SIC.date_facture,I.nom,I.ext_image, SIC.page
        FROM tva_saisie_controle T, image I, lot L,dossier D ,soussouscategorie as SSC,saisie_controle as SIC,separation as S,categorie as C
        WHERE  T.image_id = I.id 
        AND I.lot_id = L.id 
        AND L.dossier_id = D.id 
        AND SIC.image_id = I.id 
        AND SSC.id = T.soussouscategorie_id 
        AND S.image_id = I.id
        AND S.categorie_id = C.id
        AND D.id= ".$dossier."
        AND C.id= ".$categorie;
        if ($exercice >0){
            $query .=" AND I.exercice= ".$exercice;
        }
        $query .=" AND SIC.num_facture <> ''
                   GROUP BY SIC.num_facture";
 
        $prep = $pdo->query($query);
        $data = $prep->fetchAll(\PDO::FETCH_ASSOC); 
        $control_id = uniqid();
        foreach ($data as $k=>$item){
            $stmt = $pdo->prepare("INSERT INTO doublon (sid,tid,rs,montant_ht,num_facture,control_id) 
                                                VALUES (:sid,:tid,:rs,:montant_ht,:num_facture,:control_id)");
            $stmt->bindParam(':rs', $rs);
            $stmt->bindParam(':tid', $tid);
            $stmt->bindParam(':sid', $sid);
            $stmt->bindParam(':montant_ht', $montant_ht);
            $stmt->bindParam(':num_facture', $num_facture);
            $stmt->bindParam(':control_id', $control_id);
            $num_facture = $item['num_facture'];
            $montant_ht =strlen($item['num_facture']);
            $sid =2;
            if (is_numeric($item['num_facture'])){
                $sid=1;
            }
            $rs = preg_replace('/[^0-9]+/', '', $item['num_facture']);
            if ($rs<100000000){
                $rs = intval($rs);
            }
            $tid = $k;
            $stmt->execute();
        }
        //traitement des numeriques
        $query = "SELECT  * FROM doublon as SIC WHERE SIC.control_id ='".$control_id."'  
        AND SIC.sid=1
        ORDER BY SIC.montant_ht,
        CASE WHEN (SIC.num_facture <> '0' AND CAST(SIC.num_facture AS SIGNED) <> 0) THEN CAST(SIC.num_facture AS SIGNED) ELSE 999999999 END";
        $prep = $pdo->query($query);
        $trou = $prep->fetchAll(\PDO::FETCH_ASSOC);

        $num = 0;$onum=0; $r = 0;$datas=array();
        foreach ($trou as $k=>$item){
            $datas[$r]['num_facture']=$item['num_facture'];
            $datas[$r]['tid'] =$item['tid'];
            $datas[$r]['date_scan']= $data[$item['tid']]['date_scan'];
            $datas[$r]['rs']= $data[$item['tid']]['rs'];
            $datas[$r]['montant_ttc']= $data[$item['tid']]['montant_ttc'];
            $datas[$r]['date_facture']= $data[$item['tid']]['date_facture'];
            $datas[$r]['nom']= $data[$item['tid']]['nom'];
            $datas[$r]['ext_image']= $data[$item['tid']]['ext_image'];
            $datas[$r]['page']= $data[$item['tid']]['page'];
            if ($num == 0){
                $num = $item['rs'];
                $datas[$r]['rupture']= '';
            } else {               
                    $datas[$r]['rupture']= $this->getRupture($item['num_facture'],$item['rs'],$num);
                    $num = $item['rs'];
            }
            $r++;
        }
        //traitement des autres
         $query = "SELECT  * FROM doublon WHERE control_id ='".$control_id."'  
         AND sid=2
         ORDER BY montant_ht,num_facture";
         $prep = $pdo->query($query);
         $trou = $prep->fetchAll(\PDO::FETCH_ASSOC);
 
         $num = 0;$onum=0;
         foreach ($trou as $k=>$item){
             $datas[$r]['num_facture']=$item['num_facture'];
             $datas[$r]['tid'] =$item['tid'];
             $datas[$r]['date_scan']= $data[$item['tid']]['date_scan'];
             $datas[$r]['rs']= $data[$item['tid']]['rs'];
             $datas[$r]['montant_ttc']= $data[$item['tid']]['montant_ttc'];
             $datas[$r]['date_facture']= $data[$item['tid']]['date_facture'];
             $datas[$r]['nom']= $data[$item['tid']]['nom'];
             $datas[$r]['ext_image']= $data[$item['tid']]['ext_image'];
             $datas[$r]['page']= $data[$item['tid']]['page'];
             if ($num == 0){
                 $num = $item['rs'];
                 $datas[$r]['rupture']= '';
             } else {               
                $datas[$r]['rupture']= $this->getRupture($item['num_facture'],$item['rs'],$num);
                     $num = $item['rs'];
             }
             $r++;
         }
        

        $query = 'DELETE FROM doublon
        WHERE control_id ="'.$control_id.'"';
        $prep = $pdo->query($query);
        $prep->execute(); 
        return $datas;
    }

	public function getListeDoublon($dossier,$categorie,$exercice,$crit)
    {
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        
        $query = "SELECT T.soussouscategorie_id as sid,T.id as tid,SSC.libelle_new,L.date_scan, SIC.rs,T.montant_ttc,T.tva_taux_id,SIC.date_facture,SIC.num_facture,
        I.status,I.nom,I.ext_image, SIC.page
        FROM tva_saisie_controle T, image I, lot L,dossier D ,soussouscategorie as SSC,saisie_controle as SIC,separation as S,categorie as C
        WHERE  T.image_id = I.id 
        AND I.lot_id = L.id 
        AND L.dossier_id = D.id 
        AND SIC.image_id = I.id 
        AND SSC.id = T.soussouscategorie_id 
        AND S.image_id = I.id
        AND S.categorie_id = C.id
        AND D.id= ".$dossier."
        AND C.id= ".$categorie;
        
        if ($exercice >0){
            $query .=" AND I.exercice= ".$exercice;
        }
        
        $prep = $pdo->query($query);
        
        $data =  $prep->fetchAll(\PDO::FETCH_ASSOC);
        $control_id = uniqid();
        foreach ($data as $item){
            $stmt = $pdo->prepare("INSERT INTO doublon (sid,tid,rs,libelle_new,date_scan,date_facture,num_facture,montant_ht,nom,ext_image,page,control_id) 
                                                VALUES (:sid,:tid,:rs,:libelle_new,:date_scan,:date_facture,:num_facture,:montant_ht,:nom,:ext_image,:page,:control_id)");
            $stmt->bindParam(':sid', $sid);
            $stmt->bindParam(':tid', $tid);
            $stmt->bindParam(':rs', $rs);
            $stmt->bindParam(':libelle_new', $libelle_new);
            $stmt->bindParam(':date_scan', $date_scan);
            $stmt->bindParam(':date_facture', $date_facture);
            $stmt->bindParam(':num_facture', $num_facture);
            $stmt->bindParam(':montant_ht', $montant_ht);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':ext_image', $ext_image);
            $stmt->bindParam(':page', $page);
            $stmt->bindParam(':control_id', $control_id);

            $sid = $item['sid'];
            $tid = $item['tid'];
            $rs = $item['rs'];
            $libelle_new = $item['libelle_new'];
            $date_scan = $item['date_scan'];
            $date_facture = $item['date_facture'];
            $num_facture = $item['num_facture'];
            $montant_ht = $item['montant_ttc'];
            $nom = $item['nom'];
            $ext_image = $item['ext_image'];
            $page = $item['page'];
            $stmt->execute();
        }   
        
        $andWhere =' AND t1.control_id ="'.$control_id.'"';
        if (in_array("1",$crit)){
            $andWhere .=' AND   t1.rs = t2.rs';
        }
        if (in_array("2",$crit)){
            $andWhere .=' AND t1.date_facture = t2.date_facture';
        }  
        if (in_array("3",$crit)){
            $andWhere .=' AND t1.num_facture = t2.num_facture';
        } 
        if (in_array("4",$crit)){
            $andWhere .=' AND t1.montant_ht = t2.montant_ht';
        }   

        $query = "SELECT DISTINCT *
                  FROM doublon t1
                  WHERE EXISTS (
                    SELECT *
                    FROM doublon t2
                    WHERE t1.id <> t2.id
                    ".$andWhere.")
                     ORDER BY rs ASC,date_facture DESC,montant_ht DESC";
         $prep = $pdo->query($query);
        $doublon = $prep->fetchAll(\PDO::FETCH_ASSOC);
        
        $query = 'DELETE FROM doublon
        WHERE control_id ="'.$control_id.'"';
        $prep = $pdo->query($query);
        $prep->execute(); 

        return $doublon;   
    }
    public function getSsid($tid)
    {//soussouscategorie_id
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
        $query = "SELECT s.id ,s.libelle_new
                  FROM tva_saisie_controle t, soussouscategorie s
                  WHERE t.soussouscategorie_id = s.id
                  AND t.id=".$tid;
        $prep = $pdo->query($query);
        $t = $prep->fetch(\PDO::FETCH_ASSOC);
        return $t;
    }    
    
    public function setTvaDoublon($tid,$ssid,$new){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();

        $query = 'DELETE FROM doublon_tva
        WHERE tid ="'.$tid.'"';
        $prep = $pdo->query($query);
        $prep->execute(); 

        $stmt = $pdo->prepare("INSERT INTO doublon_tva (tid,ssid) VALUES (:tid,:ssid)");
        $stmt->bindParam(':tid', $tid);
        $stmt->bindParam(':ssid', $ssid);
        $stmt->execute();

        $query = 'UPDATE tva_saisie_controle set soussouscategorie_id='.$new.'
                  WHERE id ="'.$tid.'"';

        $prep = $pdo->query($query);
        $prep->execute(); 
    } 
    
    public function setRevDoublon($tid){
        $con = new CustomPdoConnection();
        $pdo = $con->connect();
   
        $query = "SELECT ssid
        FROM doublon_tva
        WHERE tid=".$tid;
        $prep = $pdo->query($query);
        $t = $prep->fetch(\PDO::FETCH_ASSOC);
        $ssid = $t['ssid'];

        $this->setTvaDoublon($tid,5,$ssid);
    }
    public function getRupture($fa,$rs,$num){
        $rupt = $rs-$num-1;
        if (preg_match("/2013|2014|2015|2016|2017|2018|2019|2020|\/17/",$rs)){
            if (strlen($rs)==8 && $rs>20100000){
                if($rupt=="99" || $rupt=="8899"){
                    $rupt=0;
                }
            } 
           if(strlen($rs)==5 ||strlen($rs)==6 ||strlen($rs)==7) { 
                if($rupt=="9999" || $rupt=="889" || $rupt=="9"){
                $rupt=0;  
                }  
           } 
        }
        if (preg_match("/\/17|\/18|\/19|\/16|\/15|\/20/",$fa)){  
            if ($rupt=="99"){
                $rupt=0;
            }
        } 
        return $rupt;
    } 
}