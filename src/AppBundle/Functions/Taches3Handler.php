<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 18/01/2019
 * Time: 10:28
 */

namespace AppBundle\Functions;


use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Twig\TwigEngine;

class Taches3Handler
{
    private $em;
    private $twig;

    public function __construct(EntityManager $entityManager, TwigEngine $twig)
    {
        $this->em = $entityManager;
        $this->twig = $twig;
    }

    public function getClients($client = 0)
    {
        $cl = $this->em->getRepository('AppBundle:Client')->find($client);
        $clients = $this->em
            ->getRepository('AppBundle:TachesEntity')->getClientsHasTaches($cl);

        return $clients;
    }

    public function calculer($client = 0)
    {
        $periode = new \DateTime();
        $periode->setTime(0,0,0);

        $periode_1 = clone  $periode;
        $periode_1->sub(new \DateInterval('P1Y'));
        $periode_2 = clone $periode;
        $periode_2->add(new \DateInterval('P1Y'));

        /** @var \DateTime[] $dates */
        //$dates = [$periode_2];
        $dates = [$periode,$periode_2,$periode_1];
        $clients = $this->getClients($client);

        $results = [];
        foreach ($dates as $date)
        {
            $res = $this->em->getRepository('AppBundle:Calendar')
                ->taches3Events($clients,$date);
            $results = array_merge($results,$res);
        }

        return $results;
    }
}