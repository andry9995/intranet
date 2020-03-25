<?php

namespace GammeBundle\Controller;

use AppBundle\Entity\Gamme;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class GammeController extends Controller
{
    public function indexAction()
    {
        return $this->render('GammeBundle:Gamme:index.html.twig');
    }

    public function listeAction()
    {
        $gammes = $this->getDoctrine()
            ->getRepository('AppBundle:Gamme')
            ->getAll();
        $procedures = $this->getDoctrine()
            ->getRepository('AppBundle:ProcedureIntranet')
            ->getAllProcedure();
        $data = [
            'gammes' => $gammes,
            'procedures' => $procedures,
        ];

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        return JsonResponse::fromJsonString($serializer->serialize($data, 'json'));
    }

    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        if ($id != 0) {
            $gamme = $this->getDoctrine()
                ->getRepository('AppBundle:Gamme')
                ->find($id);
            if (!$gamme) {
                $gamme = new Gamme();
            }
        } else {
            $gamme = new Gamme();
        }
        $em->persist($gamme);

        $gamme->setNom($request->request->get('nom'))
            ->setProcedures(json_decode($request->request->get('procedures')));

        $em->flush();

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer(array($normalizer), array($encoder));
        return JsonResponse::fromJsonString($serializer->serialize($gamme, 'json'));
    }

    public function deleteAction(Gamme $gamme)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($gamme);
        $em->flush();

        return new JsonResponse(['erreur' => false]);
    }
}
