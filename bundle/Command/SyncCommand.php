<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Command;

use Contentful\Delivery\Synchronization\DeletedEntry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Contentful\Delivery\DynamicEntry;

class SyncCommand extends ContainerAwareCommand
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful
     */
    private $contentful;

    public function __construct(        
        \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful
    )
    {
        parent::__construct();
        $this->contentful = $contentful;
    }

    protected function configure()
    {
        $this
            ->setName('contentful:sync')
            ->setDescription('Syncing space and content type cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->getContainer()->getParameter('contentful.clients');

        if (count($info) === 0) {
            $output->writeln('<comment>There are no Contentful clients configured.</comment>');
            return;
        }

        $fs = new Filesystem();

        foreach ($info as $client) {
            $clientService = $this->getContainer()->get($client["service"]);

            $this->contentful->refreshSpaceCache($clientService, $fs);

            $this->contentful->refreshContentTypeCache($clientService, $fs);

            /**
             * @var \Contentful\Delivery\Synchronization\Manager $syncManager
             */
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

    protected function buildContentEntries($entries, OutputInterface $output) {
        foreach ($entries as $remote_entry) {
            if ($remote_entry instanceof DynamicEntry) {
                $contentfulEntry = $this->contentful->refreshContentfulEntry($remote_entry);
                $output->writeln('<comment>Remote entry ' . $contentfulEntry->getId() . ' synced.</comment>');
            } elseif ($remote_entry instanceof DeletedEntry) {
                $contentfulEntry = $this->contentful->deleteContentfulEntry($remote_entry);
                $output->writeln('<comment>Remote entry ' . $contentfulEntry->getId() . ' deleted.</comment>');
            } else {
                $output->writeln('<comment>Unexpected entry ' . get_class($remote_entry) . '. Not synced.</comment>');
            }
        }
    }
}
