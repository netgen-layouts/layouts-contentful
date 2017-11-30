<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\TargetType;

use Exception;
use Netgen\BlockManager\Layout\Resolver\TargetTypeInterface;
use Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

final class Entry implements TargetTypeInterface
{
    public function getType()
    {
        return 'contentful_entry';
    }

    public function getConstraints()
    {
        return array(
            new Constraints\NotBlank(),
        );
    }

    public function provideValue(Request $request)
    {
        $id = $request->attributes->get('_content_id');
        if ($id === null) {
            return null;
        }

        $idList = explode(':', $id);
        if (count($idList) !== 2) {
            throw new Exception(
                sprintf(
                    'Item ID %s not valid.',
                    $id
                )
            );
        }

        if ($idList[0] === ContentfulEntry::class) {
            return $idList[1];
        }

        return null;
    }
}
