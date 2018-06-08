<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Routing\EntrySlugger;

abstract class Slugger
{
    /**
     * Filters the provided string as a slug.
     *
     * @param string $string
     *
     * @return string
     */
    public function filterSlug($string)
    {
        return mb_strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
}
