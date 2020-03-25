<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 08/10/2018
 * Time: 09:31
 */

namespace AppBundle\Functions;


use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Twig\TwigEngine;

class TacheHandler
{
    private $em;
    private $twig;

    public function __construct(EntityManager $entityManager, TwigEngine $twig)
    {
        $this->em = $entityManager;
        $this->twig = $twig;
    }

    private function getClients($client = 0)
    {
        $cl = $this->em->getRepository('AppBundle:Client')->find($client);
        $clients = $this->em
            ->getRepository('AppBundle:TacheEntity')
            ->getClientsHavingTache($cl);

        return $clients;
    }

    public function setDates($client = 0)
    {
        $periode = new \DateTime();
        $periode->setTime(0,0,0);

        return $this->em->getRepository('AppBundle:Calendar')
            ->getTachesClients($this->getClients($client),$periode);
    }
}