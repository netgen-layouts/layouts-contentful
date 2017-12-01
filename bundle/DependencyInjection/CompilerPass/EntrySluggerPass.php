<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\CompilerPass;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EntrySluggerPass implements CompilerPassInterface
{
    const SERVICE_NAME = 'netgen_block_manager.contentful.slugger.configurable';
    const TAG_NAME = 'netgen_block_manager.contentful.entry_slugger';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::SERVICE_NAME)) {
            return;
        }

        $service = $container->findDefinition(self::SERVICE_NAME);
        $sluggers = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($sluggers as $sluggerService => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new Exception('Entry slugger service tags should have an "type" attribute.');
                }

                $sluggers[$tag['type']] = new Reference($sluggerService);
            }
        }

        $service->replaceArgument(1, $sluggers);
    }
}
