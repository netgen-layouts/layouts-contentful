<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Exception\NotFoundException;
use Netgen\Layouts\Contentful\Exception\RuntimeException;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Routing\RedirectRouteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            ->setDescription('Show routes and possibility to delete redirects')
            ->addOption('delete', 'd', InputOption::VALUE_REQUIRED, 'Delete all redirects for given Entry ID');
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
                $this->io->writeln('All redirect routes deleted');
            } catch (NotFoundException $e) {
                $this->io->writeln($e->getMessage());
            } catch (\Exception $e) {
                $this->io->writeln($e->getMessage());
            }

            return 0;
        }

        $table = new Table($output);
        $table->setHeaders(['Entry ID', 'URL', 'Status', 'Content type', 'Content name']);

        $routes = $this->entityManager->getRepository(Route::class)->findAll();

        /** @var Route $route */
        foreach ($routes as $route) {
            $contentClass = explode(':', $route->getDefault('_content_id'))[0];
            $contentfulEntryId = $route->getName();
            $status = '200';

            if (is_a($contentClass, RedirectRouteInterface::class, true)) {
                $contentfulEntryId = explode('_', $route->getName())[0];
                $status = '301';
            }

            /** @var ContentfulEntry $content */
            $content = $this->contentful->loadContentfulEntry($contentfulEntryId);

            $table->addRow([$content->getId(), $route->getId(), $route->getStaticPrefix(), $status, $content->getContentType()->getName(), $content->getName()]);
        }
        $table->render();

        return 0;
    }
}
