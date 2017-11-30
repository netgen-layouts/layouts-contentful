<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle;

use Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection\ExtensionPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenContentfulBlockManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var \Netgen\Bundle\BlockManagerBundle\DependencyInjection\NetgenBlockManagerExtension $blockManagerExtension */
        $blockManagerExtension = $container->getExtension('netgen_block_manager');
        $blockManagerExtension->addPlugin(new ExtensionPlugin());
    }
}
