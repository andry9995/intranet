<?php

namespace RevisionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UnController extends Controller
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
        $icategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getCategories();
        $isouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSousCategories();
        $isoussouscategorie = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->getSoussousCategories();
              
		return $this->render('RevisionBundle:Un:index.html.twig', array(
            'clients' => $clients,
            'icategorie' => $icategorie,
            'isouscategorie' => $isouscategorie,
            'isoussouscategorie' => $isoussouscategorie,
        ));   
    }

    public function revAction(Request $request)
    {
        //traitement dossier
        $did = json_decode($request->request->get('did'), true);
        if ($did>0){
            $data = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
            ->getExercices($did);
            return new JsonResponse($data);
        }
        //information images
        $iid = json_decode($request->request->get('iid'), true);
        if ($iid>0){
            $data = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
            ->getInfosImage($iid);
            return new JsonResponse($data);
        }
        //validation modification ifoimage
        $imagid = json_decode($request->request->get('imagid'), true);
        if ($imagid>0){
            $c =  json_decode($request->request->get('c'), true);  
            $sc =  json_decode($request->request->get('sc'), true);
            $ssc =  json_decode($request->request->get('ssc'), true);
            $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->sMaj($imagid,$c,"categorie_id");
            $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->sMaj($imagid,$sc,"souscategorie_id");
            $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')->sMaj($imagid,$ssc,"soussouscategorie_id");
            return new JsonResponse($ssc);    
        }    
        $dossier = json_decode($request->request->get('dossier'), true);
		$exercice = json_decode($request->request->get('exercice'), true);
        $categorie = json_decode($request->request->get('categorie'), true);
        $stati = json_decode($request->request->get('stati'), true);

        $Objdossier = $this->getDoctrine()->getManager()->getRepository('AppBundle:Dossier')
                          ->createQueryBuilder('d')
                          ->where('d.id = :id')
                          ->setParameter('id', $dossier)
                          ->getQuery()
                          ->getOneOrNullResult();  
        $complet = $this->getDoctrine()->getRepository('AppBundle:TbimagePeriode')
                            ->getAnneeMoisExercices($Objdossier,$exercice);                      

        $data['recu'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier,$categorie,$exercice,'');
        $data['saisie'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier,$categorie,$exercice,' AND I.status =4 ');
        $data['impute'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier,$categorie,$exercice,' AND I.status =8 ');
        //$data['instance'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        //->getEnInstance($dossier,$categorie,$exercice);
        $data['instance'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
            ->getRevPieces($dossier,$categorie,$exercice,' AND I.status =1');
        //$data['autres'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        //->getAutres($dossier,$categorie,$exercice);
        $data['autres'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
            ->getRevPieces($dossier,$categorie,$exercice,' AND I.status =2');
        $data['previse'] = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
        ->getRevPieces($dossier,$categorie,$exercice,' AND I.status =9 ');
        $data['parevise'] = $data['recu'] - $data['previse'];


        $g = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                         ->getGenerale($dossier);
        $m = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                         ->getMandataire($dossier);
        
        $data['generale'] ="<tr><td>R.S Société :</td><td>".$g['rs_ste']."</td></tr>";
        $data['generale'] .="<tr><td>Siren|Siret :</td><td>".$g['siren_ste']."</td></tr>";
        $data['generale'] .="<tr><td>Num et rue :</td><td>".$g['num_rue']."</td></tr>";
        $data['generale'] .="<tr><td>Ville :</td><td>".$g['ville']."</td></tr>";
        $data['generale'] .="<tr><td>Code Postale :</td><td>".$g['code_postal']."</td></tr>";
        $data['generale'] .="<tr><td>Pays :</td><td>".$g['pays']."</td></tr>";
        $data['generale'] .="<tr><td>Tél. Société :</td><td>".$g['rs_ste']."</td></tr>";
        
        $data['mandataire'] ="<tr><td>Nom Mandataire :</td><td>".$m['nom']." ".$m['prenom']."</td></tr>";
        $data['mandataire'] .="<tr><td>Tél Mandataire :</td><td>".$m['tel_portable']."</td></tr>";
        $data['mandataire'] .="<tr><td>Mail Mandataire :</td><td>".$m['email']."</td></tr>";
        $ecriture = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                        ->getDateEcriture($dossier,$exercice);
        $dateCloture = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getRevDateCloture($dossier,$exercice);
        $dateCloture =  $dateCloture->format('d-m-Y');
        $datePremier = new \DateTime($g['debut_activite']);
        $datePremier =  $datePremier->format('d-m-Y');
        $te = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                          ->getTenue($dossier);
        $convention = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                          ->getConvention($dossier);              
        $rmanquant  = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                          ->getReleveManq($dossier,$exercice,$complet);    
        $data['rmanquant']="";
        foreach($rmanquant as $m){
            $data['rmanquant'].= "<tr><th>".$m['banque_compte']."</th><td>".$m['manquant']."</td><td></td><td></td></tr>";
        }                  
        $data['rmanquant'].= "<tr><th>Autres</th><td>0</td><td></td><td>13/05/2018</td></tr><tr><th>Lignes à catégoriser</th><td>850</td><td></td><td></td></tr>";

        $t=array();
        $t[0]="";
        $t[1]="Mensuelle";
        $t[2]="Trimestrielle";
        $t[3]="Semestrielle";
        $t[4]="Annuelle";
        $t[5]="Ponctuelle";
        $tenue = "";
        if (intval($te) >0){
            $tenue = $t[$te];
        }
        $instruction = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getInstructions($dossier);
        $data['isaisie']="";$data['idossier']="";                    
        if (isset($instruction[0])){
            $data['isaisie']= $instruction[0]['isaisie'];
            $data['idossier']= $instruction[0]['idossier'];
        }
        
        $fisc = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getRegimeFiscal($dossier);
        $impo = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getRegimeImposition($dossier);
        $rtva = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getRegimeTva($dossier);
        $ttva = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getTypeTva($dossier);                    

        $data['comptable'] ="<tr><td>Dates écritures :</td><td>".$ecriture."</td></tr>";
        $data['comptable'] .="<tr><td>Note de frais :</td><td>Saisie NdF sans justif</td></tr>";
        $data['comptable'] .="<tr><td>Périodicité tenue :</td><td>".$tenue."</td></tr>";
        $data['comptable'] .="<tr><td>Date démarrage 1er exercice :</td><td>".$datePremier."</td></tr>";
        $data['comptable'] .="<tr><td>Date clôture :</td><td>".$dateCloture."</td></tr>";
        $data['comptable'] .="<tr><td>Convention comptable :</td><td>".$convention."</td></tr>";
        $data['comptable'] .="<tr><td>Compte à globaliser :</td><td>607, XXX</td></tr>";

        $data['fiscale'] ="<tr><td>Régime fiscal :</td><td>".$fisc."</td></tr>";
        $data['fiscale'] .="<tr><td>Régime imposition :</td><td>".$impo."</td></tr>";
        $data['fiscale'] .="<tr><td>TVA :</td><td>".$rtva."</td></tr>";
        $data['fiscale'] .="<tr><td>Date echéance :</td><td>".$g['tva_date']."</td></tr>";
        $data['fiscale'] .="<tr><td>Type TVA :</td><td>".$ttva."</td></tr>";
        $data['fiscale'] .="<tr><td>Num Intracom :</td><td>...</td></tr>";
        $images = $this->getDoctrine()->getRepository('AppBundle:ReleveManquant')
                            ->getImages($dossier,$exercice,$stati);
        $data['imaging'] = "";
        foreach ($images as $item){
            switch ($item['status']) {
                case 0:
                    $color='#f8ac59';
                    break;
                case 1:
                    $color='#d1dade';
                    break;
                case 2:
                    $color='#1c84c6';
                    break;
                case 4:
                    $color='#f8d000';
                    break;
                case 8:
                    $color='#23c6c8';
                    break;    
                case 9:
                    $color='#1ab394';
                    break;    
            }          
            $url = 'https://lesexperts.biz/IMAGES/'.str_replace("-", "", $item['date_scan']).'/'.$item['nom'].'.'.$item['ext_image'];
            $data['imaging'] .="<tr rel=".$url." class='set_image' data-id='".$item['id']."'><td bgcolor='".$color."'>".$item['nom']."</td></tr>";
        }                    
        return new JsonResponse($data);
    }

}
