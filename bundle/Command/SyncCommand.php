<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Command;

use Contentful\Delivery\Resource\DeletedEntry;
use Contentful\Delivery\Resource\Entry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SyncCommand extends Command
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;

        // Parent constructor call is mandatory in commands registered as services
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Syncs space and content type cache');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var \Contentful\Delivery\Client\ClientInterface&\Contentful\Delivery\Client\SynchronizationClientInterface $client */
        foreach ($this->contentful->getClients() as $client) {
            $this->contentful->refreshSpaceCache($client);
            $this->contentful->refreshContentTypeCache($client);

            $syncManager = $client->getSynchronizationManager();

            $result = $syncManager->startSync();
            $this->buildContentEntries($result->getItems());

            while (!$result->isDone()) {
                $result = $syncManager->continueSync($result->getToken());
                $this->buildContentEntries($result->getItems());
            }
        }

        return 0;
    }

    /**
     * Builds the local content entries from provided remote entries.
     *
     * @param \Contentful\Core\Resource\ResourceInterface[] $entries
     */
    private function buildContentEntries(array $entries): void
    {
        foreach ($entries as $remoteEntry) {
            if ($remoteEntry instanceof Entry) {
                $contentfulEntry = $this->contentful->refreshContentfulEntry($remoteEntry);
                $this->io->writeln(sprintf('Remote entry %s synced.', $contentfulEntry->getId()));
            } elseif ($remoteEntry instanceof DeletedEntry) {
                $this->contentful->deleteContentfulEntry($remoteEntry);
                $this->io->writeln(sprintf('Remote entry %s deleted.', $remoteEntry->getId()));
            }
        }
    }
}
