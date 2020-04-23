<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing\EntrySlugger;

use function html_entity_decode;
use function htmlentities;
use function mb_strtolower;
use function preg_replace;
use function trim;
use const ENT_QUOTES;

trait FilterSlugTrait
{
    /**
     * Filters the provided string as a slug.
     */
    private function filterSlug(string $string): string
    {
        return mb_strtolower(trim((string) preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode((string) preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
}
