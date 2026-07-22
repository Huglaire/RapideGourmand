<?php

namespace App\Command;

use App\Document\OrderStatistic;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\StatisticsService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:rebuild-statistics',
    description: 'Reconstruit les statistiques MongoDB à partir des commandes MySQL.',
)]
class RebuildStatisticsCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly StatisticsService $statisticsService,
        private readonly DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {

        // Supprime toutes les statistiques existantes
        $this->documentManager
            ->getDocumentCollection(OrderStatistic::class)
            ->deleteMany([]);

        $output->writeln('<info>Anciennes statistiques supprimées.</info>');

        // Récupère toutes les commandes enregistrées
        $orders = $this->orderRepository->findAll();

        $count = 0;

        foreach ($orders as $order) {

            // Ignore les commandes annulées
            if ($order->getStatus() === Order::STATUS_CANCELLED) {
                continue;
            }

            // Génère la statistique MongoDB associée à la commande
            $this->statisticsService->recordOrder($order);

            ++$count;
        }

        // Affiche le résultat de la reconstruction
        $output->writeln(sprintf(
            '<info>%d statistique(s) reconstruite(s).</info>',
            $count,
        ));

        return Command::SUCCESS;
    }
}