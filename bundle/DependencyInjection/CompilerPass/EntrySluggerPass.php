<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\CompilerPass;

use Netgen\BlockManager\Contentful\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EntrySluggerPass implements CompilerPassInterface
{
    private static $serviceName = 'netgen_block_manager.contentful.entry_slugger.configurable';
    private static $tagName = 'netgen_block_manager.contentful.entry_slugger';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::$serviceName)) {
            return;
        }

        $service = $container->findDefinition(self::$serviceName);
        $sluggerServices = $container->findTaggedServiceIds(self::$tagName);

        $sluggers = [];
        foreach ($sluggerServices as $sluggerService => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new RuntimeException('Entry slugger service tags should have an "type" attribute.');
                }

                $sluggers[$tag['type']] = new Reference($sluggerService);
            }
        }

        $service->replaceArgument(1, $sluggers);
    }
}
