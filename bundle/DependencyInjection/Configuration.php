<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class Configuration implements ConfigurationInterface
{
    public function __construct(
        private ExtensionInterface $extension,
    ) {}

    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder<'array'>
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->extension->getAlias());
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('entry_slug_type')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->stringNode('default')
                            ->defaultValue('simple')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('content_type')
                            ->useAttributeAsKey('name')
                            ->stringPrototype()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
