<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle;

use Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\CompilerPass;
use Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\ExtensionPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenContentfulBlockManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        /** @var \Netgen\Bundle\BlockManagerBundle\DependencyInjection\NetgenBlockManagerExtension $blockManagerExtension */
        $blockManagerExtension = $container->getExtension('netgen_block_manager');
        $blockManagerExtension->addPlugin(new ExtensionPlugin());

        $container->addCompilerPass(new CompilerPass\ClientsPass());
        $container->addCompilerPass(new CompilerPass\EntrySluggerPass());
    }
}
