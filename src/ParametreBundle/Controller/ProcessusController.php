<?php
/**
 * Created by PhpStorm.
 * User: BEN
 * Date: 13/03/2019
 * Time: 09:50
 */

namespace ParametreBundle\Controller;

use AppBundle\Entity\Processus;
use AppBundle\Entity\ProcessusParOrganisation;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProcessusController extends Controller
{
    function indexAction()
    {
        $newRang = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getNewRang();
        return $this->render('ParametreBundle:Organisation:new_processus.html.twig', array(
            'rang' => $newRang,
            'type' => 0,
        ));
    }


    function reloadRelationProcessusAction()
    {
        return $this->render('ParametreBundle:Organisation:processus_poste_menu.html.twig');

    }




    function listeRelationProcessusAction()
    {
        $infos = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getAll();

        $processus = $infos["processus"];
        $process = $infos["process"];


        $processByOrg = $this->getDoctrine()
            ->getRepository('AppBundle:ProcessusParOrganisation')
            ->getAll();

        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getAll();

        $menus = $this->getDoctrine()
            ->getRepository('AppBundle:MenuIntranet')
            ->getAll();

        $MenuByOrg = $this->getDoctrine()
            ->getRepository('AppBundle:MenuIntranetPoste')
            ->getAll();

        $menuChoisis = $this->getDoctrine()
            ->getRepository('AppBundle:ProcessusMenuIntranet')
            ->getAll();

        $rows = array();
        foreach ($processus as $processu ) {
            $rowProcess = array();
            foreach ($process as $detail ) {
                if ($detail->processus_id == $processu->id) {
                    $orgNom = array();
                    $orgId = array();
                    $idx = 0;
                    foreach ($processByOrg as $pOrg)
                    {
                        if ($pOrg->processus_id == $detail->id)
                        {
                            $orgNom[$idx] = $pOrg->nom;
                            $orgId[$idx] = $pOrg->organisation_id;
                            $idx += 1;
                        }
                    }
                    $rowProcess[] = array(
                        'id' => $detail->id,
                        'nom' => $detail->nom,
                        'rang' => $detail->rang,
                        'org_nom' =>$orgNom,
                        'org_id'=>$orgId,
                        'unite_oeuvre_id' => ($detail->unite_oeuvre_id == null)?0:$detail->unite_oeuvre_id,
                        'unite_oeuvre_nom' => $detail->nomUnite,
                        'temps_trait' => ($detail->temps_trait == null)?0:$detail->temps_trait,
                        'process_ant_id' => ($detail->process_ant_id == null)?0:$detail->process_ant_id,
                        'process_ant_nom' => $detail->processAnt,
                        'process_post_id' => ($detail->process_post_id == null)?0:$detail->process_post_id,
                        'process_post_nom' => $detail->processPost,
                        'description' => $detail->description,
                    );
                }
            }
            $rows[$processu->id] = array(
                'parent_nom' => $processu->nom,
                'parent_rang' => $processu->rang,
                'details' => $rowProcess,
                'nb_ligne' => count($rowProcess),
            );
        }

        return $this->render('ParametreBundle:Organisation:processus_poste_menu.html.twig', array(
            'Processus' => $rows,
            'Organisations' => $postes,
            'ProcessByOrg' => $processByOrg,
            'menuChoisis' => $menuChoisis,
            'menusParProcessus' => $menuChoisis,
        ));
    }

    function rangProcessAction(Request $request)
    {
        $processusId = $request->request->get('processusId');
        $newRang = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getNewRangProcess($processusId);
        $processus = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getProcessus();
        $unite = $this->getDoctrine()
            ->getRepository('AppBundle:UniteOeuvre')
            ->getAll();

        $process = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getProcess();
        return $this->render('ParametreBundle:Organisation:new_processus.html.twig', array(
            'rang' => $newRang,
            'type' => 1,
            'ProcessusId' => $processusId,
            'Processus' => $processus,
            'UniteOeuvres' => $unite,
            'Process' => $process,
        ));
    }

    function editAction(Request $request)
    {
        $nom = $request->request->get('nom');
        $rang = $request->request->get('rang');
        $processusId = $request->request->get('processusId');
        return $this->render('ParametreBundle:Organisation:new_processus.html.twig', array(
            'rang' => $rang,
            'type' => 2,
            'nom' => $nom,
            'processusId' => $processusId,

        ));
    }



    function reloadProcessusAction()
    {
        $infos = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getAll();

        $processus = $infos["processus"];
        $details = $infos["process"];

        $processByOrg = $this->getDoctrine()
            ->getRepository('AppBundle:ProcessusParOrganisation')
            ->getAll();

        $rows = array();
        foreach ($processus as $process ) {
            $rowD = array();
            foreach ($details as $detail ) {
                if ($detail->processus_id == $process->id) {
                    $orgNom = '';
                    $orgId = 0;
                    foreach ($processByOrg as $pOrg)
                    {
                        if ($pOrg->processus_id == $detail->id)
                        {
                            $orgNom = $pOrg->nom;
                            $orgId = $pOrg->organisation_id;
                        }
                    }
                    $rowD[] = array(
                        'id' => $detail->id,
                        'nom' => $detail->nom,
                        'rang' => $detail->rang,
                        'org_nom' =>$orgNom,
                        'org_id'=>$orgId,
                        'unite_oeuvre_id' => ($detail->unite_oeuvre_id == null)?0:$detail->unite_oeuvre_id,
                        'unite_oeuvre_nom' => $detail->nomUnite,
                        'temps_trait' => ($detail->temps_trait == null)?0:$detail->temps_trait,
                        'process_ant_id' => ($detail->process_ant_id == null)?0:$detail->process_ant_id,
                        'process_ant_nom' => $detail->processAnt,
                        'process_post_id' => ($detail->process_post_id == null)?0:$detail->process_post_id,
                        'process_post_nom' => $detail->processPost,
                        'description' => $detail->description,
                    );
                }
            }
            $rows[$process->id] = array(
                'parent_nom' => $process->nom,
                'parent_rang' => $process->rang,
                'details' => $rowD,
                'nb_ligne' => count($rowD)
            );
        }

        return $this->render('@Parametre/Organisation/processusReload.html.twig', array(
            'processus' => $rows,
        ));
    }

    function deleteProcessAction(Request $request)
    {
        $processId = $request->request->get('processId');
        $process = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->deleteProcess($processId);

        return $this->reloadProcessusAction();
    }

    function deleteProcessusAction(Request $request)
    {
        try
        {
            $processusId = $request->request->get('processusId');
            $process = $this->getDoctrine()
                ->getRepository('AppBundle:Processus')
                ->deleteProcessus($processusId);
            return $this->reloadProcessusAction();
        } catch ( \Exception $ex) {
            return 'error';
        }
    }

    function editProcessAction(Request $request)
    {
        $nom = $request->request->get('nom');
        $rang = $request->request->get('rang');
        $processusId = $request->request->get('processusId');
        $processId = $request->request->get('processId');

        $processus = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getProcessus();
        $process = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getProcess();
        $processInfos = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getInfosProcessById($processId);
        $unites = $this->getDoctrine()
            ->getRepository('AppBundle:UniteOeuvre')
            ->getAll();
        $unite = '';
        $uniteId = 0;
        $description = '';
        $processAnt = '';
        $processAntId = '';
        $processPost = '';
        $processPostId = 0;
        $temps = 0;

        foreach ($processInfos as $infos)
        {
            $unite = $infos->nomUnite;
            $uniteId = ($infos->unite_oeuvre_id==null)? 0:$infos->unite_oeuvre_id;
            $description = $infos->description;
            $processAnt = $infos->processAnt;
            $processAntId = ($infos->process_ant_id == null) ? 0:$infos->process_ant_id;
            $processPost = $infos->processPost;
            $processPostId = ($infos->process_post_id ==null) ? 0:$infos->process_post_id;
            $temps = ($infos->temps_trait == null) ? 0:$infos->temps_trait;
        }

        return $this->render('ParametreBundle:Organisation:new_processus.html.twig', array(
            'rang' => $rang,
            'type' => 3,
            'nom' => $nom,
            'ProcessusId' => $processusId,
            'processId' => $processId,
            'Processus' => $processus,
            'Process' => $process,
            'description' => $description,
            'ProcessAntId'=> $processAntId,
            'ProcessPostId' => $processPostId,
            'temps_trait' => $temps,
            'uniteOeuvreId' => $uniteId,
            'UniteOeuvres' => $unites,
        ));
    }

    function editProcessusAction(Request $request)
    {
        $rang = $request->request->get('rang');
        $nom = $request->request->get('nom');
        $processusId = $request->request->get('processusId');


        $processus = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->edit($processusId, $rang, $nom);

        return $this->reloadProcessusAction();
    }

    function saveEditProcessAction(Request $request)
    {
        $rang = $request->request->get('rang');
        $nom = $request->request->get('nom');
        $processusId = $request->request->get('processusId');
        $processId = $request->request->get('processId');
        $unite = $request->request->get('unite');
        $temps = $request->request->get('temps');
        $processAnt = $request->request->get('processAntId');
        $processPost = $request->request->get('processPostId');
        $description = $request->request->get('description');

        $processus = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->editProcess($processusId, $processId, $rang, $nom, $unite, $temps, $processAnt, $processPost, $description);

        return $this->reloadProcessusAction();
    }

    function saveRelationProcPosteAction(Request $request)
    {
        $tableau = $request->request->get('tableau');
        $relation = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->saveRelation($tableau);
        return $this->render('parametre_organisation_processus_poste_menus_liste');
    }


    function saveAction(Request $request)
    {
        $rang = $request->request->get('rang');
        $nom = $request->request->get('nom');
        $processus = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->save($rang, $nom);

        return $this->reloadProcessusAction();

    }

    function reloadAllProcessAction()
    {
        return $this->reloadProcessusAction();
    }

    function saveProcessAction(Request $request)
    {
        $rang = $request->request->get('rang');
        $nom = $request->request->get('nom');
        $processusId = $request->request->get('processusId');
        $unite = $request->request->get('unite');
        $temps = $request->request->get('temps');
        $processAnt = $request->request->get('processAntId');
        $processPost = $request->request->get('processPostId');
        $description = $request->request->get('description');

        $processus = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->saveProcess($processusId, $rang, $nom, $unite, $temps, $processAnt, $processPost, $description);

        return $this->reloadProcessusAction();

    }



}