<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Command;

use Doctrine\ORM\EntityManager;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\RedirectRoute;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class RoutesCommand extends Command
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    public function __construct(Contentful $contentful, Filesystem $fileSystem, EntityManager $entityManager)
    {
        $this->contentful = $contentful;
        $this->fileSystem = $fileSystem;
        $this->entityManager = $entityManager;

        // Parent constructor call is mandatory in commands registered as services
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Lists routes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['Code', 'URL', 'Content type', 'Content name']);

        $routes = $this->entityManager->getRepository(Route::class)->findAll();

        /** @var Route $route */
        foreach ($routes as $route) {

            $contentClass = explode(':', $route->getDefault('_content_id'))[0];

            if ($contentClass == ContentfulEntry::class) {
                /** @var ContentfulEntry $content */
                $content = $this->contentful->loadContentfulEntry($route->getName());
                $status = "200";

            } elseif ($contentClass == RedirectRoute::class) {
                /** @var ContentfulEntry $content */
                $content = $this->contentful->loadContentfulEntry(explode('_', $route->getName())[0]);
                $status = "301";
            }

            $table->addRow([$status, $route->getStaticPrefix(),$content->getContentType()->getName(),$content->getName() ]);

        }
        $table->render();

        return 0;
    }
}
