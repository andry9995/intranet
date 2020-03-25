<?php

namespace TacheBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\GoogleCalendarConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GoogleCalendrierController extends Controller
{
    public function indexAction()
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();
        return $this->render('@Tache/Tache/google-calendrier.html.twig', array(
            'clients' => $clients,
        ));
    }

    public function googleCalendrierClientAction(Client $client)
    {
        $param = $this->getDoctrine()
            ->getRepository('AppBundle:GoogleCalendarConfig')
            ->findOneBy(array(
                'client' => $client
            ));

        return JsonResponse::fromJsonString($this->get('app.serialize_to_json')->serialize($param));
    }

    public function googleCalendrierClientEditAction(Request $request, Client $client)
    {
        $em = $this->getDoctrine()->getManager();
        $param = $this->getDoctrine()
            ->getRepository('AppBundle:GoogleCalendarConfig')
            ->findOneBy(array(
                'client' => $client
            ));
        if (!$param) {
            $param = new GoogleCalendarConfig();
            $param->setClient($client);
            $em->persist($param);
        }

        $param->setIdentifiant($request->request->get('calendrier_id'));
        $param->setColor($request->request->get('calendrier_bg_color'));
        $param->setTextColor($request->request->get('calendrier_text_color'));
        $send_to_google = false;
        if ($request->request->get('send_to_google') == '1') {
            $send_to_google = true;
        }
        $param->setSendToGoogle($send_to_google);

        $em->flush();

        return JsonResponse::fromJsonString($this->get('app.serialize_to_json')->serialize($param));
    }
}
