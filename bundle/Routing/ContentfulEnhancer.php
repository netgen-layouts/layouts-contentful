<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Routing;

use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentfulEnhancer implements RouteEnhancerInterface
{

    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(
        \Netgen\BlockManager\Contentful\Service\Contentful $contentful
    ) {
        $this->contentful = $contentful;
    }

    public function enhance(array $defaults, Request $request)
    {
        $defaults["_content"] = $this->contentful->loadContentfulEntry($defaults["_route"]);

        return $defaults;
    }
}
