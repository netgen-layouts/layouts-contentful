<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Layout\Resolver\TargetType;

use Contentful\Delivery\Resource\Space as ContentfulSpace;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Layout\Resolver\TargetType;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

use function count;
use function explode;

final class Space extends TargetType implements ValueObjectProviderInterface
{
    public function __construct(
        private Contentful $contentful,
    ) {}

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
        $contentIds = explode(':', $request->attributes->getString('_content_id'));
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

    public function getValueObject(mixed $value): ?ContentfulSpace
    {
        return $this->contentful->getClientBySpaceId($value)?->getSpace();
    }
}
