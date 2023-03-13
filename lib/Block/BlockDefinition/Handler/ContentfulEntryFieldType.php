<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

enum ContentfulEntryFieldType: string
{
    case ARRAY = 'array';

    case ASSET = 'asset';

    case ASSETS = 'assets';

    case BOOLEAN = 'boolean';

    case DATETIME = 'datetime';

    case DOUBLE = 'double';

    case ENTRIES = 'entries';

    case ENTRY = 'entry';

    case GEOLOCATION = 'geolocation';

    case INTEGER = 'integer';

    case JSON = 'json';

    case OBJECT = 'object';

    case RICHTEXT = 'richtext';

    case STRING = 'string';
}
