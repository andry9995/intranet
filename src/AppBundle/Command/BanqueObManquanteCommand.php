<?php

/**
 * BanqueObManquanteCommand
 *
 * @package Intranet
 *
 * @author Scriptura
 * @copyright Scriptura (c) 2019
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BanqueObManquanteCommand extends ContainerAwareCommand
{
    
    /**
     * get entity manager
     */
    public function emRepository($entity)
    {
        $repository = 'AppBundle:' . $entity;

        return $this
                    ->getContainer()
                    ->get('doctrine')
                    ->getEntityManager()
                    ->getRepository($repository)
        ;
    }

    /**
     * configuration de la commande
     */
    protected function configure()
    {
        $this
            ->setName('banque:ob-manquante')
            ->setDescription('Vérification des opérations bancaires manquantes')
        ;
    }

    /**
     * Execution de la commande
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $obManquanteRepository = $this->emRepository('BanqueObManquante');

        $obManquanteRepository->cronJob($output);
 
    }

}
