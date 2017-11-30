<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Command;

use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class SyncCommand extends ContainerAwareCommand
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var array
     */
    private $contentfulClients;

    public function __construct(Contentful $contentful, array $contentfulClients)
    {
        $this->contentful = $contentful;
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
        if (empty($this->contentfulClients)) {
            $output->writeln('<comment>There are no Contentful clients configured.</comment>');

            return;
        }

        $fs = new Filesystem();

        foreach ($this->contentfulClients as $client) {
            $clientService = $this->getContainer()->get($client['service']);

            $this->contentful->refreshSpaceCache($clientService, $fs);

            $this->contentful->refreshContentTypeCache($clientService, $fs);

            /** @var \Contentful\Delivery\Synchronization\Manager $syncManager */
            $syncManager = $clientService->getSynchronizationManager();

            $tokenPath = $this->contentful->getSpaceCachePath($clientService, $fs) . '/token';
            if (!$fs->exists($tokenPath)) {
                $result = $syncManager->startSync();
            } else {
                $token = file_get_contents($tokenPath);
                $result = $syncManager->continueSync($token);
            }

            $this->buildContentEntries($result->getItems(), $output);

            if (!$result->isDone()) {
                $token = $result->getToken();
                $fs->dumpFile($tokenPath, $token);
            }
        }
    }

    private function buildContentEntries($entries, OutputInterface $output)
    {
        foreach ($entries as $remoteEntry) {
            if ($remoteEntry instanceof DynamicEntry) {
                $contentfulEntry = $this->contentful->refreshContentfulEntry($remoteEntry);
                $output->writeln('<comment>Remote entry ' . $contentfulEntry->getId() . ' synced.</comment>');
            } elseif ($remoteEntry instanceof DeletedEntry) {
                $contentfulEntry = $this->contentful->deleteContentfulEntry($remoteEntry);
                $output->writeln('<comment>Remote entry ' . $contentfulEntry->getId() . ' deleted.</comment>');
            } else {
                $output->writeln('<comment>Unexpected entry ' . get_class($remoteEntry) . '. Not synced.</comment>');
            }
        }
    }
}
