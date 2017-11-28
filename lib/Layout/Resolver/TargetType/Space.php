<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\TargetType;

use Exception;
use Netgen\BlockManager\Layout\Resolver\TargetTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;
use Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry;

class Space implements TargetTypeInterface
{
    public function getType()
    {
        return 'contentful_space';
    }

    public function getConstraints()
    {
        return array(
            new Constraints\NotBlank()
        );
    }

    public function provideValue(Request $request)
    {
        $content_id = $request->attributes->get("_content_id");
        if ($content_id == null)
            return null;

        $cid_array = explode(":", $content_id);
        if (count($cid_array) != 2) 
            return null;

        if ($cid_array[0] != ContentfulEntry::class)
            return null;

        $id_array = explode("|", $cid_array[1]);
        if (count($id_array) != 2) 
            return null;
        
        return $id_array[0];
    }
}
