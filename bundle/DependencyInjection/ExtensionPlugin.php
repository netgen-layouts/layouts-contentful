<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection;

use Netgen\Bundle\BlockManagerBundle\DependencyInjection\ExtensionPlugin as BaseExtensionPlugin;

final class ExtensionPlugin extends BaseExtensionPlugin
{
    public function appendConfigurationFiles(): array
    {
        return [
            __DIR__ . '/../Resources/config/block_type_groups.yml',
        ];
    }
}
