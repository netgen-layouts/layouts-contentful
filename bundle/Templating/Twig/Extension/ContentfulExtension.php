<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Extension;

use Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Runtime\ContentfulRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContentfulExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'contentful_entry_name',
                [ContentfulRuntime::class, 'contentfulEntryName'],
            ),
            new TwigFunction(
                'contentful_space_name',
                [ContentfulRuntime::class, 'contentfulSpaceName'],
            ),
            new TwigFunction(
                'contentful_content_type_name',
                [ContentfulRuntime::class, 'contentfulContentTypeName'],
            ),
        ];
    }
}
