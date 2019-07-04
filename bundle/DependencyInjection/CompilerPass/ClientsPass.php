<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection\CompilerPass;

use Netgen\Layouts\Contentful\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ClientsPass implements CompilerPassInterface
{
    private const SERVICE_NAME = 'netgen_layouts.contentful.service';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::SERVICE_NAME)) {
            return;
        }

        $contentfulService = $container->findDefinition(self::SERVICE_NAME);
        $clientServices = array_keys($container->findTaggedServiceIds('contentful.delivery.client'));

        $clients = [];
        foreach ($clientServices as $clientService) {
            $clientName = str_replace('contentful.delivery.', '', $clientService);
            $lastPosition = mb_strrpos($clientName, '_client');
            if ($lastPosition === false) {
                continue;
            }

            $clientName = mb_substr($clientName, 0, $lastPosition);

            $clients[$clientName] = new Reference($clientService);
        }

        if (count($clients) === 0) {
            throw new RuntimeException('At least one Contentful client needs to be configured');
        }

        $contentfulService->replaceArgument(0, $clients);
    }
}
