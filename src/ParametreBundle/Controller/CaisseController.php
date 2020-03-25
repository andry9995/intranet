<?php
/**
 * Created by PhpStorm.
 * User: Maharo
 * Date: 17/07/2018
 * Time: 13:51
 */

namespace ParametreBundle\Controller;


use AppBundle\Entity\CaisseNature;
use AppBundle\Entity\CaisseType;
use AppBundle\Entity\Pcc;
use AppBundle\Entity\TdCaisseBilanPcc;
use AppBundle\Entity\TdCaisseResultatPcc;
use AppBundle\Entity\TdCaisseResultatPcg;
use AppBundle\Entity\TdTvaPcc;
use AppBundle\Entity\TdTvaPcg;
use AppBundle\Entity\TvaTaux;
use AppBundle\Entity\TvaTauxDossier;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CaisseController extends Controller
{
    public function indexAction(){
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();

        return $this->render('@Parametre/Caisse/index.html.twig', array(
            'clients' => $clients
        ));

    }

    public function tdContrePartieAction($typecaisse ,$dossierid){

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        if(null === $dossier){
            $res = '<option value="-1"></option>';
            return new Response($res);
        }

        $pccCp = null;


        if((int)$typecaisse === 0) {

            /** @var TdCaisseBilanPcc $tdVenteComptoirCp */
            try {
                $tdVenteComptoirCp = $this->getDoctrine()
                    ->getRepository('AppBundle:TdCaisseBilanPcc')
                    ->getTdCaisseBilanPccByDossier($dossier, 0);
            } catch (NonUniqueResultException $e) {

            }


            if ($tdVenteComptoirCp !== null) {
                $pccCp = $tdVenteComptoirCp->getPcc();
            }
        }
        else {
            $tdCaisseBilan = $this->getDoctrine()
                ->getRepository('AppBundle:TdCaisseBilanPcc')
                ->getTdCaisseBilanPccByDossier($dossier, 1);

            if ($tdCaisseBilan !== null) {
                $pccCp = $tdCaisseBilan->getPcc();
            }
        }


        $tdBilanPcgs = $this->getDoctrine()
            ->getRepository('AppBundle:TdCaisseBilanPcg')
            ->findAll();

        /** @var Pcc[] $cps */
        $cps =[];
        foreach ($tdBilanPcgs  as $tdTvaPcg){
            $tmps = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, $tdTvaPcg->getPcg()->getCompte());

            foreach ($tmps as $tmp){
                $cps[] = $tmp;
            }
        }

        $res = '';
        foreach ($cps as $cp){

            if($pccCp === $cp){
                $res .= '<option value="' . $cp->getId() . '" selected>' . $cp->getCompte() . '</option>';
            }
            else {
                $res .= '<option value="' . $cp->getId() . '">' . $cp->getCompte() . '</option>';
            }
        }

        return new Response($res);
    }
    public function tdContrePartieEditAction(Request $request, $typecaisse){

        $post = $request->request;

        $dossierid = $post->get('dossierid');
        $pccid = $post->get('pcc');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $pcc = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->find($pccid);


        if(null === $dossier && null == $pcc){
            $res = ['type' => 'error', 'message' => 'dossier et/ou pcc introuvable'];
            return new JsonResponse($res);
        }

        if((int)$typecaisse == 0) {
            /** @var TdCaisseBilanPcc $tdVenteComptoirCp */
            try {
                $tdVenteComptoirCp = $this->getDoctrine()
                    ->getRepository('AppBundle:TdCaisseBilanPcc')
                    ->getTdCaisseBilanPccByDossier($dossier, 0);
            } catch (NonUniqueResultException $e) {

            }

            $em = $this->getDoctrine()->getManager();
            if ($tdVenteComptoirCp === null) {
                $tdVenteComptoirCp = new TdCaisseBilanPcc();
                $tdVenteComptoirCp->setDossier($dossier);
                $tdVenteComptoirCp->setPcc($pcc);
                $tdVenteComptoirCp->setTypeCaisse(0);

                $em->persist($tdVenteComptoirCp);
                $em->flush();

                $res = ['type' => 'success', 'action' => 'add', 'message' => 'ajout effectué'];
            } else {
                $tdVenteComptoirCp->setPcc($pcc);
                $em->flush();

                $res = ['type' => 'success', 'action' => 'update', 'message' => 'mise à jour effectuée'];
            }
        }
        else{
            /** @var TdCaisseBilanPcc $tdCaisseBilan */
            try {
                $tdCaisseBilan= $this->getDoctrine()
                    ->getRepository('AppBundle:TdCaisseBilanPcc')
                    ->getTdCaisseBilanPccByDossier($dossier,1);
            } catch (NonUniqueResultException $e) {

            }

            $em = $this->getDoctrine()->getManager();
            if ($tdCaisseBilan === null) {
                $tdCaisseBilan = new TdCaisseBilanPcc();
                $tdCaisseBilan->setDossier($dossier);
                $tdCaisseBilan->setPcc($pcc);
                $tdCaisseBilan->setTypeCaisse(1);

                $em->persist($tdCaisseBilan);
                $em->flush();

                $res = ['type' => 'success', 'action' => 'add', 'message' => 'ajout effectué'];
            } else {
                $tdCaisseBilan->setPcc($pcc);
                $em->flush();

                $res = ['type' => 'success', 'action' => 'update', 'message' => 'mise à jour effectuée'];
            }
        }

        return new JsonResponse($res);
    }

    public function tdPccAction($dossierid){

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $tdCaisseResultatPcgs = $this->getDoctrine()
            ->getRepository('AppBundle:TdCaisseResultatPcg')
            ->findAll();

        $pccTemp = [];
        foreach ($tdCaisseResultatPcgs as $tdCaisseBilanPcg){
            $pccTemp[] = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->getPccByDossierLike($dossier, $tdCaisseBilanPcg->getPcg()->getCompte());
        }

        $comptes = [];
        foreach ($pccTemp as $pcc){
            $comptes[] =$pcc[0];
        }

        $rows = [];

        /** @var Pcc $pcc */
        foreach ($comptes as $pcc) {

            $nature = '';
            $type = '';

            /** @var TdCaisseResultatPcc $td */
            $td = $this->getDoctrine()
                ->getRepository('AppBundle:TdCaisseResultatPcc')
                ->getTdCaisseResultatPccByPcc($pcc);

            if(null !== $td){
                if($td->getCaisseNature() !== null){
                    $nature = $td->getCaisseNature()->getLibelle();
                }

                if($td->getCaisseType() !== null){
                    $type = $td->getCaisseType()->getLibelle();
                }
            }

            $rows[] = [
                'id' => $pcc->getId(),
                'cell' => [
                    $pcc->getCompte(),
                    $nature,
                    $type,
                    '<i class="fa fa-save icon-action js-db-action" title="Enregistrer"></i>'
                ]
            ];
        }

        return new JsonResponse(['rows' => $rows]);
    }
    public function tdPccEditAction(Request $request){

        $post = $request->request;

        $pccId = $post->get('id');
        $pcc = $this->getDoctrine()
            ->getRepository('AppBundle:Pcc')
            ->find($pccId);

        $typeId = $post->get('db-type');
        $caisseType = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseType')
            ->find($typeId);

        $natureId = $post->get('db-nature');
        $caisseNature = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->find($natureId);

        $tdPcc = $this->getDoctrine()
            ->getRepository('AppBundle:TdCaisseResultatPcc')
            ->getTdCaisseResultatPccByDossierNature($pcc->getDossier(), $caisseNature, $caisseType);

        $isInTd = true;
        if(null === $tdPcc){
            $isInTd = false;
        }

        if($isInTd){
            return new JsonResponse(['type'=>'warning', 'message' => 'already exist']);
        }


        try {

            $em = $this->getDoctrine()->getManager();


            if ($caisseNature === null && $caisseType === null && $tdPcc !== null) {
                $em->remove($tdPcc);
                $em->flush();

                return new JsonResponse(['type' => 'Success', 'message' => 'delete']);
            }

            if (null !== $tdPcc) {
                $tdPcc->setCaisseNature($caisseNature);
                $tdPcc->setCaisseType($caisseType);
                $em->flush();

                return new JsonResponse(['type' => 'Success', 'message' => 'update']);
            }

            $tdPcc = new TdCaisseResultatPcc();
            $tdPcc->setPcc($pcc);
            $tdPcc->setCaisseNature($caisseNature);
            $tdPcc->setCaisseType($caisseType);
            $em->persist($tdPcc);
            $em->flush();

            return new JsonResponse(['type' => 'Success', 'message' => 'insert']);


        }
        catch (Exception $e){
            return new JsonResponse(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function tdTypeAction($dossierid){
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        /** @var CaisseType[] $caisseTypes */
       $caisseTypes = $this->getDoctrine()
           ->getRepository('AppBundle:CaisseType')
           ->getCaisseTypeByDossier($dossier);


        $rows = [];
        foreach ($caisseTypes as $caisseType) {

            $rows[] = [
                'id' => $caisseType->getId(),
                'cell' => [
                    $caisseType->getLibelle(),
                    $caisseType->getCode(),
                    '<i class="fa fa-save icon-action js-db-type-action" title="Enregistrer"></i>'
                ]
            ];

        }

        return new JsonResponse(['rows' => $rows]);

    }
    public function tdTypeEditAction(Request $request, $dossierid){

        $post = $request->request;

        $typeId = $post->get('id');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        if($dossier === null){
            return new JsonResponse(['type' => 'error', 'message' => 'dossier introuvable']);
        }


        $typeLib = $post->get('db-type-lib');
        if($typeLib === ''){
            $typeLib = null;
        }
        $typeCode = $post->get('db-type-code');
        if($typeCode === ''){
            $typeCode = null;
        }

        $em = $this->getDoctrine()->getManager();

        try {

            if ($typeId !== 'new_row') {
                $type = $this->getDoctrine()
                    ->getRepository('AppBundle:CaisseType')
                    ->find($typeId);

                if ($type !== null) {
                    $type->setLibelle($typeLib);
                    $type->setCode($typeCode);

                    $em->flush();

                    return new JsonResponse(['type' => 'success', 'message' => 'update']);
                }

                return new JsonResponse(['type' => 'error', 'message' => 'nature introuvable']);
            }

            $type = new CaisseType();
            $type->setDossier($dossier);
            $type->setLibelle($typeLib);
            $type->setCode($typeCode);

            $em->persist($type);
            $em->flush();

            return new JsonResponse(['type' => 'success', 'message' => 'insert']);
        }
        catch (Exception $e){
            return new JsonResponse(['type' => 'error', 'message' => $e->getMessage()]);
        }



    }

    public function tdResutatAction($dossierid){
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        /** @var CaisseNature[] $caisseNatures */
        $caisseNatures = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->getCaisseNature(1);


        $rows = [];
        foreach ($caisseNatures as $caisseNature) {

            /** @var TdCaisseResultatPcc $tdCaisseResultatPcc */
            $tdCaisseResultatPcc = $this->getDoctrine()
                ->getRepository('AppBundle:TdCaisseResultatPcc')
                ->getTdCaisseResultatPccByDossierNature($dossier, $caisseNature, null);


            $pcc = '';
            if($tdCaisseResultatPcc !== null){
                $pcc = $tdCaisseResultatPcc->getPcc()->getCompte();
            }

            $rows[] = [
                'id' => $caisseNature->getId(),
                'cell' => [
                    $caisseNature->getLibelle(),
                    $pcc,
                    '<i class="fa fa-save icon-action js-db-type-action" title="Enregistrer"></i>'
                ]
            ];

        }

        return new JsonResponse(['rows' => $rows]);

    }
    public function tdResultatEditAction(Request $request, $dossierid){

        $post = $request->request;

        $natureId = $post->get('id');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $caisseNature = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->find($natureId);

        if($dossier === null){
            return new JsonResponse(['type' => 'error', 'message' => 'dossier introuvable']);
        }


        $pccid = $post->get('db-resultat-pcc');
        $pcc = null;
        if($pccid !== ''){
            $pcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($pccid);
        }


        /** @var TdCaisseResultatPcc $td */
        $td = $this->getDoctrine()
            ->getRepository('AppBundle:TdCaisseResultatPcc')
            ->getTdCaisseResultatPccByDossierNature($dossier, $caisseNature, null);

        $em = $this->getDoctrine()->getManager();

        try {

            if($td !== null){
                if($pcc !== null){
                    $td->setPcc($pcc);
                    $em->flush();

                    return new JsonResponse(['type' => 'success', 'message' => 'update']);
                }

                $em->remove($td);
                $em->flush();
                return new JsonResponse(['type' => 'success', 'message' => 'delete']);
            }

            if($pcc != null){
                $td = new TdCaisseResultatPcc();
                $td->setPcc($pcc);
                $td->setCaisseNature($caisseNature);
                $em->persist($td);
                $em->flush();
                return new JsonResponse(['type' => 'success', 'message' => 'insert']);
            }



            return new JsonResponse(['type' => 'error', 'message' => 'pcc introuvabe']);
        }
        catch (Exception $e){
            return new JsonResponse(['type' => 'error', 'message' => $e->getMessage()]);
        }



    }

    public function tdtvaCaisseAction($dossierid, $typecaisse){
        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $tvaTauxs = $this->getDoctrine()->getRepository('AppBundle:TvaTaux')
            ->getTvaTaux();


        $rows = [];
        /** @var TvaTaux $tvaTaux */
        foreach ($tvaTauxs as $tvaTaux) {

            /** @var TdTvaPcc $tdTvaPcc */
            $tdTvaPcc = $this->getDoctrine()
                ->getRepository('AppBundle:TdTvaPcc')
                ->getTdByDossierTauxType($dossier, $tvaTaux, (int)$typecaisse);


            $pcc = '';
            if($tdTvaPcc !== null){
                $pcc = $tdTvaPcc->getPcc()->getCompte();
            }


            if((int) $typecaisse === 1){
                $i = '<i class="fa fa-save icon-action js-db-tva-caisse-action" title="Enregistrer"></i>';
            }
            else{
                $i = '<i class="fa fa-save icon-action js-db-tva-action" title="Enregistrer"></i>';
            }

            $rows[] = [
                'id' => $tvaTaux->getId(),
                'cell' => [
                    $tvaTaux->getTaux(),
                    $pcc,
                    $i
                ]
            ];

        }

        return new JsonResponse(['rows' => $rows]);

    }
    public function tdtvaCaisseEditAction(Request $request, $typecaisse, $dossierid){

        $post = $request->request;

        $tvaTauxId = $post->get('id');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $tvaTaux = $this->getDoctrine()
            ->getRepository('AppBundle:TvaTaux')
            ->find($tvaTauxId);

        if($dossier === null){
            return new JsonResponse(['type' => 'error', 'message' => 'dossier introuvable']);
        }


        $pccid = $post->get('db-tva-caisse-pcc');
        $pcc = null;
        if($pccid !== ''){
            $pcc = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->find($pccid);
        }


        /** @var TdTvaPcc $td */
        $td = $this->getDoctrine()
            ->getRepository('AppBundle:TdTvaPcc')
            ->getTdByDossierTauxType($dossier, $tvaTaux, $typecaisse);

        $em = $this->getDoctrine()->getManager();

        try {

            if($td !== null){
                if($pcc !== null){
                    $td->setPcc($pcc);
                    $em->flush();

                    return new JsonResponse(['type' => 'success', 'message' => 'update']);
                }

                $em->remove($td);
                $em->flush();
                return new JsonResponse(['type' => 'success', 'message' => 'delete']);
            }

            if($pcc != null){
                $td = new TdTvaPcc();
                $td->setPcc($pcc);
                $td->setTvaTaux($tvaTaux);
                $td->setTypeCaisse($typecaisse);
                $em->persist($td);
                $em->flush();
                return new JsonResponse(['type' => 'success', 'message' => 'insert']);
            }



            return new JsonResponse(['type' => 'error', 'message' => 'pcc introuvabe']);
        }
        catch (Exception $e){
            return new JsonResponse(['type' => 'error', 'message' => $e->getMessage()]);
        }



    }

    public function caisseTypeAction(Request $request){

        $dossierid = $request->query->get('dossierid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        /** @var CaisseType[] $types */
        $types = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseType')
            ->getCaisseTypeByDossier($dossier);

        $select = '<select> <option value="-1"></option>';

        foreach ($types as $type){
            $select .= '<option value="'.$type->getId().'">'.$type->getLibelle().'</option>';
        }

        $select .= '</select>';

        return new Response($select);
    }
    public function caisseNatureAction(){

        /** @var CaisseNature[] $types */
        $types = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->getCaisseNature(0);

        $select = '<select> <option value="-1"></option>';

        foreach ($types as $type){
            $select .= '<option value="'.$type->getId().'">'.$type->getLibelle().'</option>';
        }

        $select .= '</select>';

        return new Response($select);
    }

    public function pccBilanResultatAction(Request $request){

        $dossierid = $request->query->get('dossierid');

        $caissseNatureid = $request->query->get('caissenatureid');

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        $caisseNature = $this->getDoctrine()
            ->getRepository('AppBundle:CaisseNature')
            ->find($caissseNatureid);


        /** @var TdCaisseResultatPcg[] $tdCaisseResultatPcg */
        $tdCaisseResultatPcgs = $this->getDoctrine()
            ->getRepository('AppBundle:TdCaisseResultatPcg')
            ->getTdCaisseResultatPcgByNature($caisseNature);

        /** @var Pcc[] $pccs */
        $pccs = [];

        /** @var TdCaisseResultatPcg $tdCaisseResultatPcg */
        foreach ($tdCaisseResultatPcgs as $tdCaisseResultatPcg){
            $tmps = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, $tdCaisseResultatPcg->getCompte());

            foreach ($tmps as $tmp){
                $pccs[] = $tmp;
            }
        }


        $select = '<select> <option value="-1"></option>';
        foreach ($pccs as $pcc){
            $select .= '<option value="'.$pcc->getId().' %">'.$pcc->getCompte().'</option>';
        }
        $select .='</select>';

        return new Response($select);
    }
    public function pccTvaAction(Request $request, $typecaisse){

        $dossierid = $request->query->get('dossierid');

        $tdTvaCaissePcgs = $this->getDoctrine()
            ->getRepository('AppBundle:TdTvaPcg')
            ->getTdTvaPcgByTypeCaisse((int)$typecaisse);

        $dossier = $this->getDoctrine()
            ->getRepository('AppBundle:Dossier')
            ->find($dossierid);

        /** @var Pcc[] $pccs */
        $pccs = [];

        /** @var TdTvaPcg $tdTvaCaissePcg */
        foreach ($tdTvaCaissePcgs as $tdTvaCaissePcg){
            $tmps = $this->getDoctrine()
                ->getRepository('AppBundle:Pcc')
                ->getPccByDossierLike($dossier, $tdTvaCaissePcg->getCompte());

            foreach ($tmps as $tmp){
                $pccs[] = $tmp;
            }
        }

        $select = '<select> <option value="-1"></option>';
        foreach ($pccs as $pcc){
            $select .= '<option value="'.$pcc->getId().' %">'.$pcc->getCompte().'</option>';
        }
        $select .='</select>';

        return new Response($select);
    }
}