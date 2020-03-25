<?php

namespace ParametreBundle\Controller;

use AppBundle\Entity\EtapeTraitement;
use AppBundle\Entity\OrganisationNiveau;
use AppBundle\Entity\Processus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrganisationParametreController extends Controller
{
    public function indexAction()
    {
        $titres = $this->getDoctrine()
            ->getRepository('AppBundle:OrganisationNiveau')
            ->getAll();
        $etapes = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getReel();

        $infos = $this->getDoctrine()
            ->getRepository('AppBundle:Processus')
            ->getAll();

        $processus = $infos["processus"];
        $details = $infos["process"];
        $rows = array();
        foreach ($processus as $process ) {
            $rowD = array();
            foreach ($details as $detail ) {
                if ($detail->processus_id == $process->id) {
                    $rowD[] = array(
                        'id' => $detail->id,
                        'nom' => $detail->nom,
                        'rang' => $detail->rang,
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
                'details' => $rowD
            );
        }

        return $this->render('@Parametre/Organisation/parametre.html.twig', array(
            'titres' => $titres,
            'etapes' => $etapes,
            'processus' => $rows,
        ));
    }

    public function reorderTitreAction(Request $request)
    {
        $items = json_decode($request->request->get('items'), true);
        $em = $this->getDoctrine()->getManager();
        foreach($items as $item) {
            $id = $item['id'];
            $rang = $item['rang'];
            $is_poste = $item['is_poste'] == '1' ? true : false;

            /** @var OrganisationNiveau $niveau */
            $niveau = $this->getDoctrine()
                ->getRepository('AppBundle:OrganisationNiveau')
                ->find($id);
            if ($niveau) {
                $niveau->setRang($rang)
                    ->setIsPoste($is_poste);

            }
        }
        $em->flush();

        return new JsonResponse($items);
    }

    public function posteParEtapeAction(EtapeTraitement $etape)
    {
        $listes = $this->getDoctrine()
            ->getRepository('AppBundle:EtapeTraitement')
            ->getPostesListeByEtape($etape);

        return new JsonResponse($listes);
    }

    public function posteParEtapeUpdateAction(Request $request, EtapeTraitement $etape)
    {
        $em = $this->getDoctrine()->getManager();
        $postes = json_decode($request->request->get('postes'));
        $etape->setPostes($postes);
        $em->flush();
        return new JsonResponse(['erreur' => false]);
    }
}
