<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

enum ContentfulEntryFieldType: string
{
    case Array = 'array';

    case Asset = 'asset';

    case Assets = 'assets';

    case Boolean = 'boolean';

    case DateTime = 'datetime';

    case Double = 'double';

    case Entries = 'entries';

    case Entry = 'entry';

    case GeoLocation = 'geolocation';

    case Integer = 'integer';

    case Json = 'json';

    case Object = 'object';

    case RichText = 'richtext';

    case String = 'string';
}
