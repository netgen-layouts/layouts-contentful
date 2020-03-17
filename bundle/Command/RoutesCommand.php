<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Netgen\Layouts\Contentful\Exception\NotFoundException;
use Netgen\Layouts\Contentful\Exception\RuntimeException;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Routing\RedirectRouteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;

final class RoutesCommand extends Command
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    public function __construct(Contentful $contentful, EntityManagerInterface $entityManager)
    {
        $this->contentful = $contentful;
        $this->entityManager = $entityManager;

        // Parent constructor call is mandatory in commands registered as services
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Shows content routes and allows deleting redirects')
            ->addOption('delete-redirects', 'dr', InputOption::VALUE_REQUIRED, 'Delete all redirects for given Entry ID');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('delete') !== null) {
            $entryId = $input->getOption('delete');
            if (!is_string($entryId)) {
                throw new RuntimeException('Redirects can only be deleted for a single entry per command run.');
            }

            try {
                $contentfulEntry = $this->contentful->loadContentfulEntry($entryId);
                $this->contentful->deleteRedirects($contentfulEntry);

                $this->io->success('All redirect routes deleted');

                return 0;
            } catch (NotFoundException $e) {
                $this->io->error($e->getMessage());
            }

            return 1;
        }

        /** @var \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route[] $routes */
        $routes = $this->entityManager->getRepository(Route::class)->findAll();

        if (count($routes) === 0) {
            $this->io->warning('No routes available!');

            return 1;
        }

        $tableHeaders = ['Entry ID', 'Route ID', 'URL', 'Status', 'Content type', 'Content name'];
        $tableRows = [];

        foreach ($routes as $route) {
            $entryId = $route->getName();
            $status = Response::HTTP_OK;

            $contentClass = explode(':', $route->getDefault('_content_id') ?? '')[0];
            if (is_a($contentClass, RedirectRouteInterface::class, true)) {
                $entryId = explode('_', $route->getName())[0];
                $status = Response::HTTP_MOVED_PERMANENTLY;
            }

            $entry = $this->contentful->loadContentfulEntry($entryId);

            $tableRows[] = [
                $entry->getId(),
                $route->getId(),
                $route->getStaticPrefix(),
                $status,
                $entry->getContentType()->getName(),
                $entry->getName(),
            ];
        }

        $this->io->table($tableHeaders, $tableRows);

        return 0;
    }
}
