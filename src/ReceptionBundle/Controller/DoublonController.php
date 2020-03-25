<?php

namespace ReceptionBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Entity\Image;
use AppBundle\Functions\CustomPdoConnection;

class DoublonController extends Controller
{
    public function indexAction()
    {
        $user = $this->getUser();

        $repository = $this->getDoctrine()->getRepository('AppBundle:Client');
        $query = $repository->createQueryBuilder('c')->where("c.nom <> ''");
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $query = $query->andWhere('c = :client')->setParameter('client',$user->getClient());
        }
        $query = $query->andWhere('c.status = 1')->orderBy('c.nom', 'ASC')->getQuery();
        $clients = $query->getResult();

        for ($i=0;$i<7;$i++) {
            $exercices[] = 2018 + 1 - $i;
        }	    
		return $this->render('ReceptionBundle:Doublon:index.html.twig', array(
            'clients' => $clients,
            'exercices' => $exercices,
        ));   
    }

    public function dossierAction(Request $request)
    {
        $idata = json_decode($request->request->get('idata'), true);
        $data  = array();
		if(isset($idata['dossier'])){
			$data['natures'] = $this->getDoctrine()->getRepository('AppBundle:Doublon')
				->getNatures($idata['dossier']);
		}
		if(isset($idata['client'])){
			$data['dossiers'] = $this->getDoctrine()->getRepository('AppBundle:Dossier')
				->getDossierClient($idata['client']);
		}	
        return new JsonResponse($data);
    }

    public function trouAction(Request $request)
    {
        $dossier = json_decode($request->request->get('dossier'), true);
		$exercice = json_decode($request->request->get('exercice'), true);
        $categorie = json_decode($request->request->get('categorie'), true);
        $data = $this->getDoctrine()->getRepository('AppBundle:Doublon')
        ->getListeTrou($dossier,$categorie,$exercice);
    
        $txt="";
        foreach ($data as $item){
            $txt .='<tr>';	
            $txt .="<td>".$item['num_facture']."</td>";
            $txt .="<td>".$item['rupture']."</td>";
            $txt .="<td>".$item['date_facture']."</td>";
            $txt .="<td>".$item['date_scan']."</td>";
            $txt .="<td>".$item['rs']."</td>";
            $txt .="<td>".$item['montant_ttc']."</td>";
            $txt .="<td><a href='https://lesexperts.biz/IMAGES/".str_replace("-", "", $item['date_scan'])."/".$item['nom'].".".$item['ext_image']."' target='_blank'>".$item['nom'].".".$item['ext_image']." ".$item['page']."</a></td>";
            $txt .= '</tr>';
        }	
    
        return new JsonResponse($txt);
    }

    public function listeAction(Request $request)
    {
        $dossier = json_decode($request->request->get('dossier'), true);
		$exercice = json_decode($request->request->get('exercice'), true);
        $categorie = json_decode($request->request->get('categorie'), true);
        $critere = json_decode($request->request->get('critere'), true);

        $data = $this->getDoctrine()->getRepository('AppBundle:Doublon')
            ->getListeDoublon($dossier,$categorie,$exercice,$critere);
		
		$txt="";
		foreach ($data as $item){
            $fa ="fa-ban";
            $btn="btn-warning";
            $d ="Normal";
            if($item['libelle_new']=="Doublon"){
                $fa ="fa-check";
                $btn="btn-info";   
                $d="Doublon";
            }
            $txt .='<tr>';	
            $txt .="<td>".$item['rs']."</td>";
            $txt .='<td id="tid'.$item['tid'].'">'.$item['libelle_new'].'</td>';
            $txt .="<td>".$item['date_scan']."</td>";
            $txt .="<td>".$item['date_facture']."</td>";
            $txt .="<td>".$item['num_facture']."</td>";
			$txt .="<td>".$item['montant_ht']."</td>";
            $txt .="<td><a href='https://lesexperts.biz/IMAGES/".str_replace("-", "", $item['date_scan'])."/".$item['nom'].".".$item['ext_image']."' target='_blank'>".$item['nom'].".".$item['ext_image']." ".$item['page']."</a></td>";
            
			$txt .='<td><div class="set_doublon" data-id="'.$item['tid'].'">'.$d.'</div></td>';
			$txt .= '</tr>';
		}	
        
        return new JsonResponse($txt);
    }

    public function setAction(Request $request)
    {
        $tid = json_decode($request->request->get('tid'), true);

        $ssid = $this->getDoctrine()->getRepository('AppBundle:Doublon')
                     ->getSsid($tid);

        $ssid = $ssid['id'];
        
        $i= 0;
        if ($ssid==5){
            $ssid = $this->getDoctrine()->getRepository('AppBundle:Doublon')
            ->setRevDoublon($tid);
        } else {
            $ssid = $this->getDoctrine()->getRepository('AppBundle:Doublon')
                         ->setTvaDoublon($tid,$ssid,5);
            $i=1;             
        }          

        $ssid = $this->getDoctrine()->getRepository('AppBundle:Doublon')
                     ->getSsid($tid);
        $nom = $ssid['libelle_new'];

        $data=array();
        $data['i']=$i;   
        $data['tid']=$tid;
        $data['nom']=$nom;

        return new JsonResponse($data);
    }
}

