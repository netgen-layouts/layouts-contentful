<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\CompilerPass;

use Netgen\BlockManager\Contentful\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ClientsPass implements CompilerPassInterface
{
    private const SERVICE_NAME = 'netgen_block_manager.contentful.service';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::SERVICE_NAME)) {
            return;
        }

        if (!$container->hasParameter('contentful.clients')) {
            return;
        }

        $contentfulService = $container->findDefinition(self::SERVICE_NAME);
        $contentfulClients = $container->getParameter('contentful.clients');

        if (empty($contentfulClients)) {
            throw new RuntimeException('At least one Contentful client needs to be configured');
        }

        foreach ($contentfulClients as $name => $client) {
            $contentfulClients[$name]['service'] = new Reference($contentfulClients[$name]['service']);
        }

        $contentfulService->replaceArgument(0, $contentfulClients);
    }
}
