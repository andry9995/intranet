<?php

namespace PilotageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Entity\Image;
use AppBundle\Functions\CustomPdoConnection;
class PilotageController extends Controller
{
    public function indexAction()
    {
        return $this->render('PilotageBundle:Pilotage:index.html.twig');
    }
    
            
    
    
    public function dossierAction(Request $request)
    {  
                       
                $param = array();
                $param['client'] = $request->request->get('client');
                $param['dossier'] = $request->request->get('dossier');
                $param['exercice'] = $request->request->get('exercice');
                $param['periode'] = $request->request ->get('periode');
                $param['site'] = $request->request ->get('site');

                // Test value of periode
                switch ($param['periode']) {
                    case 1:
                        // Aujourd'hui
                        $param['cas'] = 1;
                        $periodNow = new \DateTime();
                        $param['aujourdhui'] = $periodNow->format('Y-m-d');

                        break;
                    case 2:
                        // Depuis une semaine
                        $param['cas'] = 2;
                        $periodeNow = new \DateTime();
                        $now = clone $periodeNow;
                        $oneWeek = date_modify($periodeNow, "-7 days");
                        $param['dateDeb'] = $oneWeek->format('Y-m-d');
                        $param['dateFin'] = $now->format('Y-m-d');

                        break;
                    case 3:
                        // Depuis un mois
                        $param['cas'] = 3;
                        $periodeNow = new \DateTime();
                        $now = clone $periodeNow;
                        $oneMonth = date_modify($periodeNow, "-1 months");
                        $param['dateDeb'] = $oneMonth->format('Y-m-d');
                        $param['dateFin'] = $now->format('Y-m-d');

                        break;
                    case 4:
                        // Tous les exercixe
                        $param['cas'] = 4;

                        break;
                    case 5:
                        // Fourchette date debut et date fin
                        $param['cas'] = 5;
                        $debPeriode = $request->request
                            ->get('perioddeb');
                        $finPeriode = $request->request
                            ->get('periodfin');
                        if( (isset($debPeriode) && !is_null($debPeriode)) && (isset($finPeriode) && !is_null($finPeriode)) ) {
                            $param['dateDeb'] = $debPeriode;
                            $param['dateFin'] = $finPeriode;
                        }
                        break;  
                }
                
                         
                
         // image recu 
                    
         if(isset($param['dateDeb']) && isset($param['dateFin']))
                        {
                            $d1 = mktime(0, 0, 0, intval(substr($param['dateDeb'], 5, 2)), intval(substr($param['dateDeb'], 8, 2)), intval(substr($param['dateDeb'], 0, 4)));  
                            $d2 = mktime(0, 0, 0, intval(substr($param['dateFin'], 5, 2)), intval(substr($param['dateFin'], 8, 2)), intval(substr($param['dateFin'], 0, 4)));
                            $param['ouvrable'] = $this->nbJourOuvrable($d1,$d2);

                    }
                    else {
                        
                         $param['ouvrable'] = 1;
                    }
        
        
        $situationImage['recu'] = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getNombreImage($param,1);
        $situationImage['separe'] = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getNombreImage($param,2);
        $situationImage['saisies'] = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getNombreImage($param,3);
        $situationImage['imputes'] = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getNombreImage($param,4);
        $situationImage['instance'] = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getNombreImage($param,5);
        $situationImage['client_active'] = $this->getDoctrine()
                    ->getRepository('AppBundle:Image')
                    ->getNombreImage($param,6);
        
                            if ($situationImage['separe'] == 0) {
                                   $situationImage['moyen_recu']=0;
                              }
                                     else{
            $situationImage['moyen_recu'] = round($situationImage['separe'] / $param['ouvrable']); 
                                      }
                               if($situationImage['saisies'] == 0){
                                     $situationImage['moyen_separe'] = 0; 
                                }
                              else{
            $situationImage['moyen_separe'] = round($situationImage['saisies'] / $param['ouvrable']); 
                                }
                            if($situationImage['imputes'] == 0){
                                      $situationImage['moyen_saisi'] = 0; 
                                }
                            else{ 
            $situationImage['moyen_saisi'] = round($situationImage['imputes'] / $param['ouvrable']); 
                                }
                     //moyen general           
               $situationImage['moyen_general'] = round( ($situationImage['moyen_recu'] + $situationImage['moyen_separe'] + $situationImage['moyen_saisi'])/4) ;  
                                
        
                   
                        
                    

        
        return new JsonResponse($situationImage);

    }
    
   
        
    public function statusImageAction()
    {
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
        $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $query = $query->andWhere('c = :client')->setParameter('client',$user->getClient());

        }
        $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
        $clients = $query->getResult();
        $exercices = $this->getExercices(7,2);

        return $this->render('PilotageBundle:Pilotage:statusImage.html.twig', [
                'clients' => $clients,
                'exercices' => $exercices,
            ]);
    }
      public function getExercices($nbAnnee = 6,$debut = 1)
    {
        $date_now = new \DateTime();
        $current_year = intval($date_now->format('Y'));
        $exercices = array();

        for ($i=0;$i<$nbAnnee;$i++) {
            $exercices[] = $current_year + $debut - $i;
        }

        return $exercices;
    }
 public function LundiPaques($annee=NULL)
    {
	$annee=($annee==NULL) ? date("Y") : $annee;
	
	$G = $annee%19;
	$C = floor($annee/100);
	$C_4 = floor($C/4);
	$E = floor((8*$C + 13)/25);
	$H = (19*$G + $C - $C_4 - $E + 15)%30;

	if($H==29){
        $H=28;
	}elseif($H==28 && $G>10){
        $H=27;
	}
	
    $K = floor($H/28);
	$P = floor(29/($H+1));
	$Q = floor((21-$G)/11);
	$I = ($K*$P*$Q - 1)*$K + $H;
	$B = floor($annee/4) + $annee;
	$J1 = $B + $I + 2 + $C_4 - $C;
	$J2 = $J1%7;
	$R = 28 + $I - $J2; 
	$mois = $R>30 ? 4 : 3;
	$Jour = $mois==3 ? $R : $R-31;

	return mktime(0,0,0,$mois,$Jour+1,$annee);
}

//Ascension est 38 jours après paques. 3283200 secondes = 38 jours;
public function Ascension($LundiPaques)
{
	return $LundiPaques + 3283200;
}

//Lundi de Pentecôte 	20 Mai 	9 Juin 	25 Mai
//Pentecôte est 11 jours après Ascension. 950400 secondes = 11 jours;
public function LundiPentecote($Ascension)
{
	return $Ascension + 950400;
}

// mettre les variables en mktime(0, 0, 0, Mois, Jour, Année)
public function nbJourOuvrable($dateStart,$dateStop)
{
	
    $opendays = 0;
	$interval = 86400; // 1 Jour en Seconde
	
	$dateFerie = array();
	
	$Y = date("Y",$dateStart);
	$LundiPaques = $this->LundiPaques($Y);
	$Ascension = $this->Ascension($LundiPaques);
	$LundiPentecote = $this->LundiPentecote($Ascension);
	
	$dateFerie[0] = mktime(0, 0, 0, 1, 1, $Y); //Jour de l'an {1 Janvier}
	$dateFerie[1] = mktime(0, 0, 0, 5, 1, $Y); //Fête du Travail {1 Mai}
	$dateFerie[2] = mktime(0, 0, 0, 5, 8, $Y); // 8 Mai 1945 
	$dateFerie[3] = mktime(0, 0, 0, 7, 14, $Y); // Fête Nationale {14 Juillet}
	$dateFerie[4] = mktime(0, 0, 0, 8, 15, $Y); // Assomption {15 Aout}
	$dateFerie[5] = mktime(0, 0, 0, 11, 1, $Y); // La Toussaint {1 Novembre}
	$dateFerie[6] = mktime(0, 0, 0, 11, 11, $Y); // Armistice {11 Novembre}
	$dateFerie[7] = mktime(0, 0, 0, 12, 25, $Y); // Noël {25 Décembre}
	
	$mSimple = false;
	// *  Si $dateStart,$dateStop faut partie de la même année alors les dates spéciales seront de la même année
	// ** Sinon, Il faut calculer chaque date en function de son année (Si elle change)
	if(date("Y",$dateStart) == date("Y",$dateStop)){
		$mSimple = true;
	}
	
	//$LundiPaques = LundiPaques($Y)
	for ($i=$dateStart; $i <= $dateStop; $i=$i+$interval) {
		
		if(date("N",$i) > 5){continue;}; //Supprime le 6=Samedi et 7=Dimanche
		
		if($mSimple){
			// *
			if($i == $LundiPaques){continue;}
			if($i == $Ascension){continue;}
			if($i == $LundiPentecote){continue;}
			
		}else{
			// **
			$Yi = date("Y",$i);
			
			if($Yi != $Y){
				
				$Y = $Yi;
				$LundiPaques = $this->LundiPaques($Y);
				$Ascension = $this->Ascension($LundiPaques);
		 		$LundiPentecote = $this->LundiPentecote($Ascension);
				
				$dateFerie[0] = mktime(0, 0, 0, 1, 1, $Y); //Jour de l'an {1 Janvier}
				$dateFerie[1] = mktime(0, 0, 0, 5, 1, $Y); //Fête du Travail {1 Mai}
				$dateFerie[2] = mktime(0, 0, 0, 5, 8, $Y); // 8 Mai 1945 
				$dateFerie[3] = mktime(0, 0, 0, 7, 14, $Y); // Fête Nationale {14 Juillet}
				$dateFerie[4] = mktime(0, 0, 0, 8, 15, $Y); // Assomption {15 Aout}
				$dateFerie[5] = mktime(0, 0, 0, 11, 1, $Y); // La Toussaint {1 Novembre}
				$dateFerie[6] = mktime(0, 0, 0, 11, 11, $Y); // Armistice {11 Novembre}
				$dateFerie[7] = mktime(0, 0, 0, 12, 25, $Y); // Noël {25 Décembre}
				
			}
			
			if($i == $LundiPaques){continue;}
			if($i == $Ascension){continue;}
			if($i == $LundiPentecote){continue;}
				
		}
		
		//Jours feriés
		if($i == $dateFerie[0]){continue;}
		if($i == $dateFerie[1]){continue;}
		if($i == $dateFerie[2]){continue;}
		if($i == $dateFerie[3]){continue;}
		if($i == $dateFerie[4]){continue;}
		if($i == $dateFerie[5]){continue;}
		if($i == $dateFerie[6]){continue;}
		if($i == $dateFerie[7]){continue;}
		
		//Si, On est passé à travers les filtres, c'est un jour ouvrable !
		//echo "<br>";
		//echo $i  .date(" {N} D, d-m-y",$i);
		
		$opendays++;
	}
	return $opendays;
}
    
    
    
    
}
