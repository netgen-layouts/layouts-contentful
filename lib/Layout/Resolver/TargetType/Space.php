<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\TargetType;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Layout\Resolver\TargetTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

final class Space implements TargetTypeInterface
{
    public function getType()
    {
        return 'contentful_space';
    }

    public function getConstraints()
    {
        return [
            new Constraints\NotBlank(),
        ];
    }

    public function provideValue(Request $request)
    {
        $contentId = $request->attributes->get('_content_id');
        if ($contentId === null) {
            return;
        }

        $contentIds = explode(':', $contentId);
        if (count($contentIds) !== 2) {
            return;
        }

        if ($contentIds[0] !== ContentfulEntry::class) {
            return;
        }

        $contentIds = explode('|', $contentIds[1]);
        if (count($contentIds) !== 2) {
            return;
        }

        return $contentIds[0];
    }
}
