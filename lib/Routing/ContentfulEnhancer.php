<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ContentfulEnhancer implements RouteEnhancerInterface
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    /**
     * @param mixed[] $defaults
     *
     * @return mixed[]
     */
    public function enhance(array $defaults, Request $request): array
    {
        $contentClass = explode(':', $defaults['_content_id'])[0];

        if ($contentClass === ContentfulEntry::class) {
            $defaults['_content'] = $this->contentful->loadContentfulEntry($defaults['_route']);
        }

        return $defaults;
    }
}
