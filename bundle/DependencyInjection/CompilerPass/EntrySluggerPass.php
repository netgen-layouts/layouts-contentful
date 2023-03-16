<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection\CompilerPass;

use Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\DefinitionClassTrait;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class EntrySluggerPass implements CompilerPassInterface
{
    use DefinitionClassTrait;

    private const SERVICE_NAME = 'netgen_layouts.contentful.entry_slugger.configurable';
    private const TAG_NAME = 'netgen_layouts.contentful.entry_slugger';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::SERVICE_NAME)) {
            return;
        }

        $service = $container->findDefinition(self::SERVICE_NAME);
        $sluggerServices = $container->findTaggedServiceIds(self::TAG_NAME);

        $sluggers = [];
        foreach ($sluggerServices as $sluggerService => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['type'])) {
                    $sluggers[$tag['type']] = new ServiceClosureArgument(new Reference($sluggerService));

                    continue 2;
                }
            }

            $sluggerClass = $this->getDefinitionClass($container, $sluggerService);
            if (isset($sluggerClass::$defaultType)) {
                $sluggers[$sluggerClass::$defaultType] = new ServiceClosureArgument(new Reference($sluggerService));
            }
        }

        $service->addArgument(new Definition(ServiceLocator::class, [$sluggers]));
    }
}
