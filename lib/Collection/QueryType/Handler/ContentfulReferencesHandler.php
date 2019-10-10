<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Collection\QueryType\Handler;

use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\Layouts\Contentful\Exception\NotFoundException;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handler for a query which retrieves the references from Contentful entry.
 */
final class ContentfulReferencesHandler implements QueryTypeHandlerInterface
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(Contentful $contentful, RequestStack $requestStack)
    {
        $this->contentful = $contentful;
        $this->requestStack = $requestStack;
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add(
            'field_definition_identifier',
            ParameterType\TextLineType::class,
            [
                'required' => true,
            ]
        );
    }

    public function getValues(Query $query, int $offset = 0, ?int $limit = null): iterable
    {
        $contentenfulReferenceEntries = [];

        try {
            /** @var \Contentful\Delivery\Resource\Entry $entry */
            foreach ($this->getEntries($query) as $entry) {
                $contentenfulReferenceEntries[] = $this->contentful->loadContentfulEntry($entry->getSpace()->getId() . '|' . $entry->getId());
            }
        } catch (NotFoundException $e) {
            return [];
        }

        return $contentenfulReferenceEntries;
    }

    public function getCount(Query $query): int
    {
        return count($this->getEntries($query));
    }

    public function isContextual(Query $query): bool
    {
        return true;
    }

    /**
     * Gets context entry from current parameters.
     */
    private function getEntries(Query $query): array
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return [];
        }

        try {
            $contextEntry = $this->contentful->loadContentfulEntry($currentRequest->attributes->get('_route'));
            $funcName = 'get' . $query->getParameter('field_definition_identifier')->getValue();

            return $contextEntry->{$funcName}();
        } catch (NotFoundException $e) {
            // Do nothing
        }

        return [];
    }
}