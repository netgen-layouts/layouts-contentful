<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\ConditionType;

use Exception;
use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\BlockManager\Layout\Resolver\ConditionTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

final class ContentType implements ConditionTypeInterface
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getType()
    {
        return 'contentful_content_type';
    }

    public function getConstraints()
    {
        return [
            new Constraints\NotBlank(),
            new Constraints\Type(['type' => 'array']),
        ];
    }

    public function matches(Request $request, $value)
    {
        if (!is_array($value) || empty($value)) {
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
        } catch (Exception $e) {
            return false;
        }

        return in_array($contentfulEntry->getContentType()->getId(), $value, true);
    }
}
