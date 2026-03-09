<?php

namespace App\Command;

use App\Service\OpenWebUiSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-openwebui',
    description: 'Sync data from OpenWebUI API into the local database',
)]
final class SyncOpenWebUiCommand extends Command
{
    public function __construct(
        private OpenWebUiSyncService $syncService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->syncService->syncAll();
            $io->success(sprintf('Synced %d models, %d users, and %d groups from OpenWebUI.', $result['models'], $result['users'], $result['groups']));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Sync failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
