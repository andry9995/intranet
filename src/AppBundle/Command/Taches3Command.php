<?php
/**
 * Created by PhpStorm.
 * User: SITRAKA
 * Date: 18/01/2019
 * Time: 10:53
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Client;

class Taches3Command  extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:tache3_command')
            ->setDescription('Calcul des taches V3')
            ->setHelp('Cette commande permet de Calculer les dates des taches version 3')
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
            ->get('tache3.calculer');
        /** @var Client[] $clients */
        //$events = $tacheHandler->calculer($clientId);

        $output->writeln([
            "",
            var_dump($tacheHandler->calculer($clientId)),
            //count($events).' taches',
            "",
        ]);

        $output->writeln([
            "Fin",
            "=============================================",
            "",
        ]);
    }
}