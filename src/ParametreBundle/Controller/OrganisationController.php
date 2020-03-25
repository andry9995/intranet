<?php

namespace ParametreBundle\Controller;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\OrganisationNiveau;
use AppBundle\Entity\OrganisationTitre;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrganisationController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Parametre/Organisation/index.html.twig');
    }

    public function organisationAction()
    {
        return $this->orgListToJson();
    }

    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $parent_id = $request->request->get('pid');
        $posteId = $request->request->get('nom');
        $niveauId = $request->request->get('titre');
        $posteIdOld = $request->request->get('posteOldId');


        $ok = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->updateOrg($posteId, $parent_id, $niveauId);
        /*
        $em = $this->getDoctrine()->getManager();
        $parent_id = $request->request->get('pid');
        $nom = $request->request->get('nom');
        $titre = $request->request->get('titre');
        $org_niveau = $this->getDoctrine()
            ->getRepository('AppBundle:OrganisationNiveau')
            ->findOneBy(array(
                'titre' => $titre
            ));
        if (!$org_niveau) {
            $org_niveau = new OrganisationNiveau();
            $org_niveau->setTitre($titre);
            $em->persist($org_niveau);
            $em->flush();
        }

        $parent = null;
        if ($parent_id != '0') {
            $parent = $this->getDoctrine()
                ->getRepository('AppBundle:Organisation')
                ->find($parent_id);
        }

        $org = new Organisation();
        $org->setOrganisation($parent)
            ->setNom($nom)
            ->setOrganisationNiveau($org_niveau);

        $em->persist($org);
        $em->flush();

        return new JsonResponse($this->organisationToArray($org));*/
        return $this->orgListToJson();
    }

    public function updateAction(Request $request, Organisation $org)
    {
        $em = $this->getDoctrine()->getManager();
        $parent_id = $request->request->get('pid');
        $posteId = $request->request->get('nom');
        $niveauId = $request->request->get('titre');
        $posteIdOld = $request->request->get('posteOldId');
        $ok = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->updateOrg($posteId, $parent_id, $niveauId);
        if ($posteIdOld > 0)
        {
            $ok = $this->getDoctrine()
                ->getRepository('AppBundle:Organisation')
                ->updateOrgOld($posteIdOld);
        }
        /*
        $em = $this->getDoctrine()->getManager();
        $parent_id = $request->request->get('pid');
        $nom = $request->request->get('nom');
        $titre = $request->request->get('titre');
        $org_niveau = $this->getDoctrine()
            ->getRepository('AppBundle:OrganisationNiveau')
            ->findOneBy(array(
                'titre' => $titre
            ));
        if (!$org_niveau) {
            $org_niveau = new OrganisationNiveau();
            $org_niveau->setTitre($titre);
            $em->persist($org_niveau);
            $em->flush();
        }

        $parent = null;
        if ($parent_id != '0') {
            $parent = $this->getDoctrine()
                ->getRepository('AppBundle:Organisation')
                ->find($parent_id);
        }

        $org->setOrganisation($parent)
            ->setNom($nom)
            ->setOrganisationNiveau($org_niveau);

        $em->persist($org);
        $em->flush();*/

        return $this->orgListToJson();
    }

    public function removeAction(Organisation $org)
    {
        $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->updateOrgOld($org->getId());
        /*$em = $this->getDoctrine()->getManager();
        $em->remove($org);
        $em->flush();*/

        return $this->orgListToJson();
    }

    private function organisationToArray(Organisation $org)
    {
        $data = [
            'id' => $org->getId(),
            'text' => $org->getNom(),
            'title' => $org->getOrganisationNiveau()->getTitre(),
            'resp' => 'personne',
            'parent' => $org->getOrganisation() ? $org->getOrganisation()->getId() : null,
            'org_niveau_id' => $org->getOrganisationNiveau()->getId()
        ];
        return $data;
    }

    private function orgListToJson()
    {
        $organisations = $this->getDoctrine()
            ->getRepository('AppBundle:Organisation')
            ->findAll();

        $data = [];
        /** @var Organisation $org */
        foreach ($organisations as $org) {
            $data[] = $this->organisationToArray($org);
        }
        return new JsonResponse($data);
    }
}
