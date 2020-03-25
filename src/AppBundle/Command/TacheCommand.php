<?php

namespace AppBundle\Command;

use AppBundle\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TacheCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:tache_command')
            ->setDescription('Calcul des taches')
            ->setHelp('Cette commande permet de Calculer les dates des taches')
            ->addOption(
                'client',
                null,
                InputOption::VALUE_REQUIRED,
                'Calculer les dates des taches',
                0
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            "Debut de calcul: ici",
            "=============================================",
            "",
        ]);

        $clientId = $input->getOption('client');

        $tacheHandler = $this->getContainer()
            ->get('tache.calculer');
        /** @var Client[] $clients */
        $events = $tacheHandler->setDates($clientId);

        $output->writeln([
            "",
            count($events).' taches',
            "",
        ]);

        $output->writeln([
            "Fin",
            "=============================================",
            "",
        ]);
    }
}
