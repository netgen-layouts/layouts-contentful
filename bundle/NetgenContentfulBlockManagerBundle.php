<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle;

use Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\CompilerPass\EntrySluggerPass;
use Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\CompilerPass\ClientsPass;
use Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\ExtensionPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenContentfulBlockManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var \Netgen\Bundle\BlockManagerBundle\DependencyInjection\NetgenBlockManagerExtension $blockManagerExtension */
        $blockManagerExtension = $container->getExtension('netgen_block_manager');
        $blockManagerExtension->addPlugin(new ExtensionPlugin());

        $container->addCompilerPass(new ClientsPass());
        $container->addCompilerPass(new EntrySluggerPass());

    }
}
