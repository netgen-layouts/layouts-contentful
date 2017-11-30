<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension;

use Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Runtime\ContentfulRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentfulExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction(
                'contentful_entry_name',
                array(ContentfulRuntime::class, 'contentfulEntryName')
            ),
            new TwigFunction(
                'contentful_space_name',
                array(ContentfulRuntime::class, 'contentfulSpaceName')
            ),
            new TwigFunction(
                'contentful_content_type_name',
                array(ContentfulRuntime::class, 'contentfulContentTypeName')
            ),
        );
    }
}
