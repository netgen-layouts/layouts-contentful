<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NetgenContentfulBlockManagerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services/block_definitions.yml');
        $loader->load('services/content_browser.yml');
        $loader->load('services/items.yml');
        $loader->load('services/layout_resolver.yml');
        $loader->load('services/services.yml');
        $loader->load('services/templating.yml');
        $loader->load('services/query_types.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $prependConfigs = array(
            'block_definitions.yml' => 'netgen_block_manager',
            'block_type_groups.yml' => 'netgen_block_manager',
            'value_types.yml' => 'netgen_block_manager',
            'query_types.yml' => 'netgen_block_manager',
            'item_types.yml' => 'netgen_content_browser',
            'view/item_view.yml' => 'netgen_block_manager',
            'view/rule_target_view.yml' => 'netgen_block_manager',
            'view/rule_condition_view.yml' => 'netgen_block_manager',
            'view/block_view.yml' => 'netgen_block_manager',
        );

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }
}
