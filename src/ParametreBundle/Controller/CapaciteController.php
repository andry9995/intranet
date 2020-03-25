<?php

namespace ParametreBundle\Controller;

use AppBundle\Entity\Operateur;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Poste;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CapaciteController extends Controller
{

    public function indexAction()
    {
        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getAllPoste();
        /** @var Organisation $sans_poste */
        $sans_poste = new Organisation();
        $sans_poste->setNom('Pas de Poste');
        $postes[] = $sans_poste;

        $operateurs = $this->getDoctrine()
            ->getRepository('AppBundle:Operateur')
            ->getAllOperateurGroupByPoste();

        return $this->render('@Parametre/Organisation/Personnel/capacite.html.twig', [
            'postes' => $postes,
            'operateurs' => $operateurs,
        ]);
    }

    public function updateCapaciteAction(Request $request)
    {
        $postes = json_decode($request->get('postes'), true);
        $em = $this->getDoctrine()->getManager();

        foreach ($postes as $item) {
            /** @var \AppBundle\Entity\Organisation $poste */
            $poste = $this->getDoctrine()
                ->getRepository('AppBundle:Organisation')
                ->find($item['id']);
            if ($poste) {
                $poste->setCapacite(intval($item['capacite']));
            }
        }
        $em->flush();
        $data = [
            'erreur' => FALSE,
        ];
        return new JsonResponse($data);
    }

    public function updatePosteAction(Request $request, Operateur $operateur)
    {
        $em = $this->getDoctrine()->getManager();
        $poste_id = intval($request->request->get('poste'));
        $poste = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->find($poste_id);
        if ($poste) {
            $operateur->setOrganisation($poste);
        } else {
            $operateur->setOrganisation(NULL);
        }
        $em->flush();

        $data = [
            'erreur' => FALSE,
        ];
        return new JsonResponse($data);
    }

    public function capaciteParOperateurAction(Operateur $operateur)
    {
        $data = [
            'operateur_id' => $operateur->getId(),
            'nom' => mb_strtoupper($operateur->getPrenom() . ' ' . $operateur->getNom(), 'UTF-8'),
            'poste_id' => $operateur->getOrganisation() ? $operateur->getOrganisation()->getId() : 0,
            'poste' => $operateur->getOrganisation() ? mb_strtoupper($operateur->getOrganisation()->getNom(), 'UTF-8') : 'Pas de poste',
            'capacite' => $operateur->getOrganisation() ? $operateur->getOrganisation()->getCapacite() : '-',
            'coefficient' => $operateur->getCoeff(),
        ];
        return new JsonResponse($data);
    }

    public function capaciteParOperateurUpdateAction(Request $request, Operateur $operateur)
    {
        $em = $this->getDoctrine()->getManager();
        $coeff = floatval($request->request->get('coeff'));
        $coeff = $coeff != 0 ? $coeff : 1;
        $operateur->setCoeff($coeff);
        $em->flush();
        $data = [
            'erreur' => false
        ];
        return new JsonResponse($data);
    }

    public function menuListePosteAction()
    {
        $postes = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->getAllPoste();
        // "edit": {name: "Edit", icon: "edit"},
        $menus = [];
        /** @var \AppBundle\Entity\Organisation $poste */
        foreach ($postes as $poste) {
            $menus[$poste->getNom()]['_' . $poste->getId() . '_'] = [
                'name' => $poste->getNom(),
                'icon' => '',
            ];
        }
        ksort($menus);
        $sorted_menus = [];
        foreach ($menus as $key => $value) {
            foreach ($value as $key2 => $menu) {
                $sorted_menus[$key2] = $menu;
            }
        }

        $sorted_menus['__'] = [
            'name' => 'Pas de poste',
            'icon' => '',
        ];

        return new JsonResponse($sorted_menus);
    }
}
