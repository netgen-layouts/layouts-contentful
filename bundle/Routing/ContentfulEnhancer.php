<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Routing;

use Netgen\BlockManager\Contentful\Service\Contentful;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ContentfulEnhancer implements RouteEnhancerInterface
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function enhance(array $defaults, Request $request)
    {
        $defaults['_content'] = $this->contentful->loadContentfulEntry($defaults['_route']);

        return $defaults;
    }
}
