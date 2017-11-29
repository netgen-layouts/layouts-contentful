<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Command;

use Contentful\Delivery\Synchronization\DeletedEntry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Query;

class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('contentful:sync')
            ->setDescription('Syncing space and content type cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->getContainer()->getParameter('contentful.clients');
        $cacheDir = $this->getContainer()->getParameter('kernel.cache_dir') . "/contentful";

        if (count($info) === 0) {
            $output->writeln('<comment>There are no Contentful clients configured.</comment>');
            return;
        }

        $fs = new Filesystem();

        foreach ($info as $client) {
            $clientService = $this->getContainer()->get($client["service"]);
            $space = $clientService->getSpace();

            $spacePath = $cacheDir . '/' . $space->getId();
            if (!$fs->exists($spacePath)) {
                $fs->mkdir($spacePath);
            }
            $fs->dumpFile($spacePath . '/space.json', json_encode($space));

            $contentTypes = $clientService->getContentTypes(new Query());
            foreach ($contentTypes as $contentType) {
                $fs->dumpFile($spacePath . '/ct-' . $contentType->getId() . '.json', json_encode($contentType));
            }

            /**
             * @var \Contentful\Delivery\Synchronization\Manager $syncManager
             */
            $syncManager = $clientService->getSynchronizationManager();

            if (!$fs->exists($spacePath . '/token')) {
                $result = $syncManager->startSync();
            } else {
                $token = file_get_contents($spacePath . '/token');
                $result = $syncManager->continueSync($token);
            }

            $this->buildContentEntries($result->getItems(), $output);

            if (!$result->isDone()) {
                $token = $result->getToken();
                $fs->dumpFile($spacePath . '/token', $token);
            }

        }
    }

    protected function buildContentEntries($entries, OutputInterface $output) {
        /**
         * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $service
         */
        $service = $this->getContainer()->get("netgen_block_manager.contentful.service");

        foreach ($entries as $remote_entry) {
            if ($remote_entry instanceof DynamicEntry) {
                $contentfulEntry = $service->refreshContentfulEntry($remote_entry);
                $output->writeln('<comment>Remote entry ' . $contentfulEntry->getId() . ' synced.</comment>');
            } elseif ($remote_entry instanceof DeletedEntry) {
                $contentfulEntry = $service->deleteContentfulEntry($remote_entry);
                $output->writeln('<comment>Remote entry ' . $contentfulEntry->getId() . ' deleted.</comment>');
            } else {
                $output->writeln('<comment>Unexpected entry ' . get_class($remote_entry) . '. Not synced.</comment>');
            }
        }
    }
}
