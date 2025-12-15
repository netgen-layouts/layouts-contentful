<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Layout\Resolver\TargetType;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Exception\NotFoundException;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Layout\Resolver\TargetType;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

use function count;
use function explode;
use function sprintf;

final class Entry extends TargetType implements ValueObjectProviderInterface
{
    public function __construct(
        private Contentful $contentful,
    ) {}

    public static function getType(): string
    {
        return 'contentful_entry';
    }

    public function getConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
        ];
    }

    public function provideValue(Request $request): ?string
    {
        if (!$request->attributes->has('_content_id')) {
            return null;
        }

        $id = $request->attributes->getString('_content_id');
        $idList = explode(':', $id);
        if (count($idList) !== 2) {
            throw new NotFoundException(
                sprintf(
                    'Item ID %s not valid.',
                    $id,
                ),
            );
        }

        if ($idList[0] === ContentfulEntry::class) {
            return $idList[1];
        }

        return null;
    }

    public function getValueObject(mixed $value): ?ContentfulEntry
    {
        try {
            return $this->contentful->loadContentfulEntry((string) $value);
        } catch (NotFoundException) {
            return null;
        }
    }
}
