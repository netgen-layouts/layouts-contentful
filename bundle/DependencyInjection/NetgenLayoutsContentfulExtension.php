<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection;

use Netgen\Layouts\Contentful\Attribute;
use Netgen\Layouts\Contentful\Routing\EntrySluggerInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

use function file_get_contents;

final class NetgenLayoutsContentfulExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('netgen_layouts.contentful.entry_slug_type', $config['entry_slug_type']);

        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new DelegatingLoader(
            new LoaderResolver(
                [
                    new GlobFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                ],
            ),
        );

        $loader->load('services/**/*.yaml', 'glob');
        $loader->load('browser/services.yaml');
        $loader->load('default_settings.yaml');

        $this->registerAutoConfiguration($container);
        $this->registerAttributeAutoConfiguration($container);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $prependConfigs = [
            'block_definitions.yaml' => 'netgen_layouts',
            'value_types.yaml' => 'netgen_layouts',
            'query_types.yaml' => 'netgen_layouts',
            'doctrine.yaml' => 'doctrine',
            'view/item_view.yaml' => 'netgen_layouts',
            'view/rule_target_view.yaml' => 'netgen_layouts',
            'view/rule_condition_view.yaml' => 'netgen_layouts',
            'view/block_view.yaml' => 'netgen_layouts',
            'browser/item_types.yaml' => 'netgen_content_browser',
        ];

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse((string) file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }

    /**
     * @param mixed[] $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration($this);
    }

    private function registerAutoConfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(EntrySluggerInterface::class)
            ->addTag('netgen_layouts.contentful.entry_slugger');
    }

    private function registerAttributeAutoConfiguration(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            Attribute\AsEntrySlugger::class,
            static function (ChildDefinition $definition, Attribute\AsEntrySlugger $attribute): void {
                $definition->addTag('netgen_layouts.contentful.entry_slugger', ['type' => $attribute->type]);
            },
        );
    }
}
