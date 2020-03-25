<?php

namespace RevisionBundle\Controller;

use AppBundle\Functions\GoogleCalendar;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AgendaController extends Controller
{
    /**
     * Index Agenda
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $clients = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->getAllClient();
        return $this->render('RevisionBundle:Revision:agenda.html.twig', array(
            'clients' => $clients,
        ));
    }

    /**
     * @param Request $request
     * @param $periode
     * @return JsonResponse
     * @throws \Exception
     */
    public function eventClientAction(Request $request, $periode)
    {
        $clients = $request->query->get('clients');
        $events = $this->getDoctrine()
            ->getRepository('AppBundle:Calendar')
            ->getEventsClient($clients, new \DateTime($periode));
        return new JsonResponse($events);
    }

    public function eventByDateAction(Request $request, $periode, $jqgrid)
    {
        $clients = $request->query->get('clients');
        $nomtache = $request->query->get('nomtache');
        $events = $this->getDoctrine()
            ->getRepository('AppBundle:Calendar')
            ->getEventsByDate($clients, new \DateTime($periode), $nomtache, $jqgrid);
        return new JsonResponse($events);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateEventAction(Request $request)
    {
        $params = json_decode($request->request->get('params'), true);
        $status = intval($request->request->get('status'));
        $tacheStatus = $this->getDoctrine()
            ->getRepository('AppBundle:TacheStatus')
            ->updateByParam($params, $status);

        return new JsonResponse('ok');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function reportEventByDropAction(Request $request)
    {
        $event_id = $request->request->get('event_id');
        $client_id = $request->request->get('client_id');
        $event_date = new \DateTime($request->request->get('event_date'));
        $client = $this->getDoctrine()
            ->getRepository('AppBundle:Client')
            ->find($client_id);
        if ($client) {
            $config = $this->getDoctrine()
                ->getRepository('AppBundle:GoogleCalendarConfig')
                ->findOneBy(array(
                    'client' => $client,
                ));
            if ($config) {
                $gcal = new GoogleCalendar();
                $gcal->setConfig($config);
                $updatedEvent = $gcal->updateDateEvent($event_id, $event_date);
                if ($updatedEvent) {
                    return new JsonResponse(['new_id' => $updatedEvent->getId()]);
                }
            }
        }
        throw new BadRequestHttpException("Erreur serveur");
    }
}
