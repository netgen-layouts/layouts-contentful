<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Controller;

/**
 * Contentful topic constants (sent as X-Contentful-Topic header).
 */
enum Topic: string
{
    case EntryPublish = 'ContentManagement.Entry.publish';
    case EntryUnpublish = 'ContentManagement.Entry.unpublish';
    case EntryDelete = 'ContentManagement.Entry.delete';
    case ContentTypePublish = 'ContentManagement.ContentType.publish';
    case ContentTypeUnpublish = 'ContentManagement.ContentType.unpublish';
    case ContentTypeDelete = 'ContentManagement.ContentType.delete';
}
