<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension;

use Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Runtime\ContentfulRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContentfulExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'contentful_entry_name',
                [ContentfulRuntime::class, 'contentfulEntryName']
            ),
            new TwigFunction(
                'contentful_space_name',
                [ContentfulRuntime::class, 'contentfulSpaceName']
            ),
            new TwigFunction(
                'contentful_content_type_name',
                [ContentfulRuntime::class, 'contentfulContentTypeName']
            ),
        ];
    }
}
