<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('netgen_contentful_block_manager');

        $rootNode
            ->children()
                ->arrayNode('entry_slug_type')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default')
                            ->defaultValue('simple')
                        ->end()
                        ->arrayNode('content_type')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
