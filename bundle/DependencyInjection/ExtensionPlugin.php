<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection;

use Netgen\Bundle\BlockManagerBundle\DependencyInjection\ExtensionPlugin as BaseExtensionPlugin;

final class ExtensionPlugin extends BaseExtensionPlugin
{
    public function appendConfigurationFiles()
    {
        return array(
            __DIR__ . '/../Resources/config/block_type_groups.yml',
        );
    }
}
