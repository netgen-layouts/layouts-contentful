<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Command;

use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class SyncCommand extends ContainerAwareCommand
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;

    /**
     * @var array
     */
    private $contentfulClients;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    public function __construct(
        Contentful $contentful,
        Filesystem $fileSystem,
        array $contentfulClients
    ) {
        $this->contentful = $contentful;
        $this->fileSystem = $fileSystem;
        $this->contentfulClients = $contentfulClients;

        // Parent constructor call is mandatory in commands registered as services
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('contentful:sync')
            ->setDescription('Syncs space and content type cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        if (empty($this->contentfulClients)) {
            $this->io->error('There are no Contentful clients configured.');

            return;
        }

        foreach ($this->contentfulClients as $client) {
            /** @var \Contentful\Delivery\Client $clientService */
            $clientService = $this->getContainer()->get($client['service']);

            $this->contentful->refreshSpaceCache($clientService);
            $this->contentful->refreshContentTypeCache($clientService);

            $syncManager = $clientService->getSynchronizationManager();

            $tokenPath = $this->contentful->getSpaceCachePath($clientService) . '/token';
            if (!$this->fileSystem->exists($tokenPath)) {
                $result = $syncManager->startSync();
            } else {
                $token = file_get_contents($tokenPath);
                $result = $syncManager->continueSync($token);
            }

            $this->buildContentEntries($result->getItems());

            if (!$result->isDone()) {
                $token = $result->getToken();
                $this->fileSystem->dumpFile($tokenPath, $token);
            }
        }
    }

    /**
     * Builds the local content entries from provided remote entries.
     *
     * @param \Contentful\Delivery\EntryInterface[] $entries
     */
    private function buildContentEntries(array $entries)
    {
        foreach ($entries as $remoteEntry) {
            if ($remoteEntry instanceof DynamicEntry) {
                $contentfulEntry = $this->contentful->refreshContentfulEntry($remoteEntry);
                $this->io->writeln(sprintf('Remote entry %s synced.', $contentfulEntry->getId()));
            } elseif ($remoteEntry instanceof DeletedEntry) {
                $this->contentful->deleteContentfulEntry($remoteEntry);
                $this->io->writeln(sprintf('Remote entry %s deleted.', $remoteEntry->getId()));
            } else {
                $this->io->writeln(sprintf('Unexpected entry %s. Not synced.', get_class($remoteEntry)));
            }
        }
    }
}
