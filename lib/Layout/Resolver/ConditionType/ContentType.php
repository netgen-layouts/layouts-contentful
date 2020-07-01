<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Layout\Resolver\ConditionType;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Layout\Resolver\ConditionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;
use Throwable;
use function count;
use function explode;
use function in_array;
use function is_array;

final class ContentType extends ConditionType
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public static function getType(): string
    {
        return 'contentful_content_type';
    }

    public function getConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
            new Constraints\Type(['type' => 'array']),
        ];
    }

    public function matches(Request $request, $value): bool
    {
        if (!is_array($value) || count($value) === 0) {
            return false;
        }

        $contentId = $request->attributes->get('_content_id');
        if ($contentId === null) {
            return false;
        }

        $contentIds = explode(':', $contentId);
        if (count($contentIds) !== 2) {
            return false;
        }

        if ($contentIds[0] !== ContentfulEntry::class) {
            return false;
        }

        try {
            $contentfulEntry = $this->contentful->loadContentfulEntry($contentIds[1]);
        } catch (Throwable $t) {
            return false;
        }

        return in_array($contentfulEntry->getContentType()->getId(), $value, true);
    }
}
