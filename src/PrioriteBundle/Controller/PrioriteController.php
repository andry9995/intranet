<?php

namespace PrioriteBundle\Controller;

use AppBundle\Entity\PrioriteParam;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PrioriteController extends Controller
{
    public function indexAction()
    {
        return $this->render('PrioriteBundle:Priorite:index.html.twig');
    }

    /**
     * @param $param 0: Tous 1:Jour 2: Color
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function loadParamAction($param)
    {
        $jours = $param_jour = $this->getDoctrine()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'priorite_jour'
            ));
        $colors = $this->getDoctrine()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'priorite_color'
            ));
        $default_color = $this->getDoctrine()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'default_color'
            ));
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();

        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });

        $serializer = new Serializer([$normalizer], [$encoder]);

        $data = [];

        if ($param == 0) {
            $data = [
                'jours' => $jours,
                'colors' => $colors,
                'default_color' => $default_color,
            ];
        } elseif ($param == 1) {
            $data = [
                'jours' => $jours
            ];
        } elseif ($param == 2) {
            $data = [
                'colors' => $colors,
                'default_color' => $default_color,
            ];
        }

        return new JsonResponse($serializer->serialize($data, 'json'));
    }

    public function jourEditAction(Request $request)
    {
        $jours = $request->request->get('jours');
        $em = $this->getDoctrine()
            ->getEntityManager();
        if (is_array($jours)) {
            foreach ($jours as &$jour) {
                $jour['weekday'] = intval($jour['weekday']);
                $jour['checked'] = intval($jour['checked']) === 1 ? true : false;
                $jour['heure'] = intval($jour['heure']);
            }
            $param_jour = $this->getDoctrine()
                ->getRepository('AppBundle:PrioriteParam')
                ->findOneBy(array(
                    'paramName' => 'priorite_jour'
                ));
            if ($param_jour) {
                $param_jour->setParamValue($jours);
                $em->flush();
            } else {
                $param_jour = new PrioriteParam();
                $param_jour
                    ->setParamName('priorite_jour')
                    ->setParamValue($jours);
                $em->persist($param_jour);
                $em->flush();
            }
        }

        $data = [
            'erreur' => false,
        ];
        return new JsonResponse(json_encode($data));
    }

    public function colorEditAction(Request $request)
    {
        $colors = $request->request->get('colors');
        $default_color = $request->request->get('default_color');

        $em = $this->getDoctrine()
            ->getEntityManager();
        $color_def = $this->getDoctrine()
            ->getRepository('AppBundle:PrioriteParam')
            ->findOneBy(array(
                'paramName' => 'default_color'
            ));
        if (!$color_def) {
            $color_def = new PrioriteParam();
            $color_def->setParamName('default_color');
            $em->persist($color_def);
        }
        $color_def->setParamValue([$default_color]);

        if (is_array($colors)) {
            foreach ($colors as &$color) {
                $color['min'] = intval($color['min']);
                $color['max'] = intval($color['max']);
            }

            $param_color = $this->getDoctrine()
                ->getRepository('AppBundle:PrioriteParam')
                ->findOneBy(array(
                    'paramName' => 'priorite_color'
                ));
            if ($param_color) {
                $param_color->setParamValue($colors);
                $em->flush();
            } else {
                $param_color = new PrioriteParam();
                $param_color
                    ->setParamName('priorite_color')
                    ->setParamValue($colors);
                $em->persist($param_color);
            }
        }
        $em->flush();

        $data = [
            'erreur' => false,
        ];
        return new JsonResponse(json_encode($data));
    }
}
