<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Item\ValueLoader;

use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\BlockManager\Item\ValueLoaderInterface;
use Throwable;

final class EntryValueLoader implements ValueLoaderInterface
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function load($id)
    {
        try {
            return $this->contentful->loadContentfulEntry($id);
        } catch (Throwable $t) {
            return null;
        }
    }

    public function loadByRemoteId($remoteId)
    {
        return $this->load($remoteId);
    }
}
