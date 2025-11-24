<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Attribute;

use Attribute;

/**
 * Service tag to autoconfigure entry sluggers.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsEntrySlugger
{
    public function __construct(
        private(set) string $type,
    ) {}
}
