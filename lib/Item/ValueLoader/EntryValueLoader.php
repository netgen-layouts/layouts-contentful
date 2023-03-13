<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Item\ValueLoader;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Throwable;

final class EntryValueLoader implements ValueLoaderInterface
{
    public function __construct(private Contentful $contentful)
    {
    }

    public function load($id): ?ContentfulEntry
    {
        try {
            return $this->contentful->loadContentfulEntry((string) $id);
        } catch (Throwable) {
            return null;
        }
    }

    public function loadByRemoteId($remoteId): ?ContentfulEntry
    {
        return $this->load($remoteId);
    }
}
