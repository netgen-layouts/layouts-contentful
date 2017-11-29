<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\ConditionType;

use Exception;
use Netgen\BlockManager\Layout\Resolver\ConditionTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;
use Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry;

class ContentType implements ConditionTypeInterface
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful
     */
    private $contentful;

    public function __construct(
        \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful
    ) {
        $this->contentful = $contentful;
    }

    public function getType()
    {
        return 'contentful_content_type';
    }

    public function getConstraints()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type(array('type' => 'array'))
        );
    }

    public function matches(Request $request, $value)
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        $content_id = $request->attributes->get("_content_id");
        if ($content_id == null)
            return false;

        $cid_array = explode(":", $content_id);
        if (count($cid_array) != 2)
            return false;

        if ($cid_array[0] != ContentfulEntry::class)
            return false;

        try {
            $contentfulEntry = $this->contentful->loadContentfulEntry($cid_array[1]);
        } catch (Exception $e) {
            return false;
        }

        return in_array($contentfulEntry->getContentType()->getId(), $value, true);
    }
}
