<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle;

use Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection\CompilerPass;
use Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection\ExtensionPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenLayoutsContentfulBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        /** @var \Netgen\Bundle\LayoutsBundle\DependencyInjection\NetgenLayoutsExtension $layoutsExtension */
        $layoutsExtension = $container->getExtension('netgen_layouts');
        $layoutsExtension->addPlugin(new ExtensionPlugin());

        $container->addCompilerPass(new CompilerPass\ClientsPass());
        $container->addCompilerPass(new CompilerPass\EntrySluggerPass());
    }
}
