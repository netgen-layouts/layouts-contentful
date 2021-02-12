<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Layout\Resolver\TargetType;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Layout\Resolver\TargetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;
use function count;
use function explode;

final class Space extends TargetType
{
    public static function getType(): string
    {
        return 'contentful_space';
    }

    public function getConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
        ];
    }

    public function provideValue(Request $request): ?string
    {
        $contentId = $request->attributes->get('_content_id');
        if ($contentId === null) {
            return null;
        }

        $contentIds = explode(':', $contentId);
        if (count($contentIds) !== 2) {
            return null;
        }

        if ($contentIds[0] !== ContentfulEntry::class) {
            return null;
        }

        $contentIds = explode('|', $contentIds[1]);
        if (count($contentIds) !== 2) {
            return null;
        }

        return $contentIds[0];
    }
}
