<?php

namespace Netgen\ContentfulBlockManagerBundle\Routing;

use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentfulEnhancer implements RouteEnhancerInterface
{

    /**
     * @var \Netgen\ContentfulBlockManagerBundle\Service\Contentful
     */
    private $contentful;

    public function __construct(
        \Netgen\ContentfulBlockManagerBundle\Service\Contentful $contentful
    ) {
        $this->contentful = $contentful;
    }

    public function enhance(array $defaults, Request $request)
    {
        $defaults["_content"] = $this->contentful->loadContentfulEntry($defaults["_route"]);

        return $defaults;
    }
}
