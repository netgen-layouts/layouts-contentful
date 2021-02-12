<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;
use function explode;
use function is_a;

final class ContentfulEnhancer implements RouteEnhancerInterface
{
    private Contentful $contentful;

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

        if (is_a($contentClass, ContentfulEntry::class, true)) {
            $defaults['_content'] = $this->contentful->loadContentfulEntry($defaults['_route']);
        }

        return $defaults;
    }
}
