<?php

namespace ParametreBundle\Controller;

use AppBundle\Entity\ResponsableClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AffectationDossierController extends Controller
{
    public function indexAction()
    {
        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getAllResponsable();
            

        $responsables = $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->getAllResponsable();

        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();
        
        $clis = array();$cli = array();    
        foreach($clients as $client){
            $cli['nom'] =$client->getNom();
            $cli['id'] =$client->getId();
            $cli['bold']= $this->getDoctrine()->getRepository('AppBundle:ResponsableClient')
                               ->isAffecte($client->getId()); 
            if($cli['bold']){
                $clias[]= $cli;
            } else {
                $clis[]=$cli;
            }
        }

        $respas = array();$resps = array();    
        foreach($operateurs as $respi){
            //print_r($respi);
            //die();
            $resp['nom'] =$respi->nom;
            $resp['prenom'] =$respi->prenom;
            $resp['id'] =$respi->id;
            $resp['bold']= $this->getDoctrine()->getRepository('AppBundle:ResponsableClient')
                               ->isAffecteR($respi->id); 
            if($resp['bold']){
                $respas[]= $resp;
            } else {
                $resps[]=$resp;
            }
        }
       
        return $this->render('@Parametre/AffectationDossier/affectation-dossier.html.twig', array(
            'operateurs' => $operateurs,
            'responsables' => $responsables,
            'clients' => $clis,
            'clientas' => $clias,
            'resps' => $resps,
            'respas' => $respas,
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editResponsableAction(Request $request)
    {
        $responsables = json_decode($request->request->get('responsables'), true);
        $responsables = $this->getResponsable($responsables);
        $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->saveResponsable($responsables);

        $responsables = $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableScriptura')
            ->getAllResponsable();

        return new JsonResponse($responsables);
    }

    public function clientParUtilisateurAction()
    {

    }

    private function getResponsable($items, $sup = null) {
        $ids = [];
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $ids[] = [
                    'id' => $item['id'],
                    'sup' => $sup
                    ];
                if (isset($item['children'])) {
                    $ids = array_merge($ids, $this->getResponsable($item['children'], $item['id']));
                }
            }
        }
        return $ids;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function clientParResponsableAction(Request $request)
    {
		if($request->request->get('idresp')){
			$listRespCli = $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableClient')
            ->getClientIds($request->request->get('idresp'));
			return new JsonResponse($listRespCli);
		}
	}

	/**
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editClientAction(Request $request)
    {
        //$idata = json_decode($request->request->get('idata'), true);
		/*$this->getDoctrine()
            ->getRepository('AppBundle:ResponsableClient')
            ->removeResps($idata['responsable']);*/

        $idata = json_decode($request->request->get('idata'), true);
        $tousresp = $this->getDoctrine()->getRepository('AppBundle:ResponsableScriptura')
            ->getParentIds($idata['responsable']);

        foreach ($idata['clients'] as $cli) {
            foreach ($tousresp as $r) {
                $r = intval($r);
                $op = $this->getDoctrine()
                             ->getRepository('AppBundle:Operateur')
                             ->find($r);
            
                $client = $this->getDoctrine()
                             ->getRepository('AppBundle:Client')
                             ->find($cli);
                $resp = new ResponsableClient();
                $resp->setResponsable($op);
                $resp->setClient($client);
                $em = $this->getDoctrine()->getManager();
                $em->persist($resp);
                $em->flush();
            }
        }

        $this->getDoctrine()
            ->getRepository('AppBundle:ResponsableClient')
            ->removeResps();

        return new JsonResponse(array());	 
    }

    public function supClientAction(Request $request)
    {
        $idata = json_decode($request->request->get('idata'), true);
        $tousresp = $this->getDoctrine()->getRepository('AppBundle:ResponsableScriptura')
            ->getParentIds($idata['responsable']);

            foreach ($tousresp as $r) {
                $this->getDoctrine()
                    ->getRepository('AppBundle:ResponsableClient')
                    ->removeResCli($idata['clients'],$r);
            }

        return new JsonResponse(array());
    }
}
