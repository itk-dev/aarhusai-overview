<?php

namespace App\Command;

use App\Service\OpenWebUiClientFactory;
use App\Service\OpenWebUiSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        private OpenWebUiClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('site', null, InputOption::VALUE_REQUIRED, 'Sync only the specified site (e.g. "production", "test")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $siteKey = $input->getOption('site');

        if (null !== $siteKey && !\in_array($siteKey, $this->clientFactory->getSiteKeys(), true)) {
            $io->error(sprintf('Unknown site "%s". Configured sites: %s', $siteKey, implode(', ', $this->clientFactory->getSiteKeys())));

            return Command::FAILURE;
        }

        try {
            $results = $this->syncService->syncAll($siteKey);

            foreach ($results as $site => $counts) {
                if (isset($counts['error'])) {
                    $io->warning(sprintf('[%s] Skipped: %s', $site, $counts['error']));
                    continue;
                }
                $io->success(sprintf('[%s] Synced %d models.', $site, $counts['models']));
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Sync failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
