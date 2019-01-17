<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Item\ValueLoader;

use Netgen\BlockManager\Item\ValueLoaderInterface;
use Netgen\Layouts\Contentful\Service\Contentful;
use Throwable;

final class EntryValueLoader implements ValueLoaderInterface
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function load($id)
    {
        try {
            return $this->contentful->loadContentfulEntry((string) $id);
        } catch (Throwable $t) {
            return null;
        }
    }

    public function loadByRemoteId($remoteId)
    {
        return $this->load($remoteId);
    }
}
