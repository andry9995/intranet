<?php

namespace RevisionBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Dossier;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    public function indexAction()
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();
		$date_now = new \DateTime();
        $current_year = intval($date_now->format('Y'));
        $exercices = array();

        for ($i=0;$i<7;$i++) {
            $exercices[] = $current_year + 2 - $i;
        }	
        return $this->render('RevisionBundle:Revision:dashboard.html.twig', array(
            'clients' => $clients,
			'exercices' => $exercices,
        ));
    }
	public function lotAction(Request $request)
	{
		$iid = json_decode($request->request->get('iid'), true);
		$exercice = json_decode($request->request->get('exercice'), true);
        if ($iid>0){
            $data = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
            ->getLots($iid,$exercice);
			$lot ="";
			foreach($data['lots'] as $v){
				$lot .="<tr>";
				$dateScan = new \DateTime($v['date_scan']);
				$dateScan =  $dateScan->format('d/m/Y');
				$lot .="<td>".$dateScan."</td>";
				$lot .="<td>".$v['nbimage']."</td>";
				$stat ="Reçu";
				if ($v['status']==4){
					$stat ="Révisé";
				}
				if ($v['status']==3){
					$stat ="Imputé";
				}
				if ($v['status']==2){
					$stat ="Saisi";
				}
				$lot .="<td>".$stat."</td>";
				$lot .="<td></td>";
				$lot .="<td>".$data['resp']."</td>";
				$lot .="<td></td>";
				$lot .="<td></td>";
				$lot .="<td>".'<input type="text">'."</td>";
				if ($v['priorite']>0){
					$prior = $v['priorite'];
				} else {
					$prior="100";
				}
				if ($prior=="100"){
					$prior ='<span class="badge badge-primary prior">NORM</span>';
				}
				if ($prior=="1"){
					$prior ='<span class="badge badge-warning prior">URG</span>';
				}
				if ($prior=="2"){
					$prior ='<span class="badge badge-danger prior">T. URG</span>';
				}
				$lot .="<td>".$prior."</td>";
				$lot .="</tr>";
			}
			
            return new JsonResponse($lot);
        }
	}	
    /**
     * @param Client $client
     * @param $dossier
     * @return JsonResponse
     * @throws \Exception
     */
    public function listeAction(Client $client, $dossier,$exercice)
    {
		$dossiers = [];
        if ($dossier != '0') {
            $dossiers[] = $this->getDoctrine()->getRepository('AppBundle:Dossier')
                ->find($dossier);
        } else {
            $dossiers = $this->getDoctrine()->getRepository('AppBundle:Dossier')
                ->getDossierByClient($client);
        }
        $data = [];$d=array();
        /** @var Dossier $dossier */
        foreach ($dossiers as $dossier) {
            $tache = $this->getDoctrine()->getRepository('AppBundle:PrioriteLot')
                ->getPrioriteDossier($dossier);
		$resp = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
				->getMandataire($dossier->getId());
		$resp= $resp['nom']."" .$resp['prenom']." ".$resp['email'];		
		$pri = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
				->getPriorite($dossier->getId());	
		$d['recu'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier->getId(),'',$exercice,'');
        $d['saisie'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier->getId(),'',$exercice,' AND I.ctrl_saisie >=2 ');
        $d['impute'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier->getId(),'',$exercice,' AND I.ctrl_imputation >=2 ');
        $d['instance'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getEnInstance($dossier->getId(),'',$exercice);
        $d['autres'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getAutres($dossier->getId(),'',$exercice);
        $d['previse'] = $d['impute'];
        $d['parevise'] = $d['recu'] - $d['impute'];
		
		$stock ='  <div class="ibox-content no-padding">
					<ul class="list-group">
                        <li class="list-group-item">
                            <span class="badge badge-warning" id="precu">'.$d['recu'].'</span>
                            Reçues
                        </li>
                        <li class="list-group-item ">
                            <span class="badge badge-warning" id="psaisie">'.$d['saisie'].'</span>
                            Saisies
                        </li>
                        <li class="list-group-item">
                            <span class="badge badge-info" id="pimpute">'.$d['impute'].'</span>
                            Imputées
                        </li>
                        <li class="list-group-item">
                            <span class="badge badge-success" id="pinstance">'.$d['instance'].'</span>
                            En Instance
                        </li>
                        <li class="list-group-item">
                            <span class="badge" id="pautres">'.$d['autres'].'</span>
                            Autres
                        </li>
                        <li class="list-group-item">
                            <span class="badge badge-primary" id="previse">'.$d['previse'].'</span>
                            Revisées
                        </li>
                        <li class="list-group-item">
                            <span class="badge badge-danger" id="parevise">'.$d['parevise'].'</span>
                            A reviser
                        </li>
                    </ul>
					</div>';
			$stocka = $d['parevise'];		
			$complet = $this->getDoctrine()->getRepository('AppBundle:TbimagePeriode')->getAnneeMoisExercices($dossier,$exercice); 
			$rmanquant  = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getReleveManq($dossier->getId(),$exercice,$complet);    
			$rmanqa=array();
			$rmanq='<div class="ibox-content no-padding">
					<ul class="list-group">';
			foreach($rmanquant as $m){
				$span ="badge-info";
				$rmanqa[]=$m['manquant'];
				$rmanq.='<li class="list-group-item">';
				if ($m['manquant']=="Incomplet"){
					$span ="badge-warning";
				}
				if ($m['manquant']=="Abscence Totale"){
					$span ="badge-danger";
				}				
                $rmanq.='<span class="badge '. $span.'">'.$m['manquant'].'</span>'.$m['banque_compte'].'</li>';
			} 
			$rmanq .= '</ul>
					</div>';
			$rmanqa= implode(";",$rmanqa); 			
            $data[] = [
                'id' => $dossier->getId(),
                'client' => $dossier->getSite()->getClient()->getNom(),
                'dossier' => $dossier->getNom(),
                'tache' => $tache,
				'stock'=>$stock,
				'stocka'=>$stocka,
				'resp'=>$resp,
				'priorite'=>$pri,
				'rmanqa'=>$rmanqa,
				'rmanq'=>$rmanq,
            ];
        }

        return new JsonResponse($data);
    }
}
