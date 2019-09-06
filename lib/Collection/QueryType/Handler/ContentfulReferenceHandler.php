<?php

namespace Netgen\Layouts\Contentful\Collection\QueryType\Handler;

use Contentful\Delivery\Query as ContentfulQuery;
use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handler for a query which retrieves the references from Contentful entry.
 */
final class ContentfulReferenceHandler implements QueryTypeHandlerInterface
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

    public function buildParameters(ParameterBuilderInterface $builder) : void
    {
        $builder->add(
            'field_definition_identifier',
            ParameterType\TextLineType::class,
            [
                'required' => true,
            ]
        );
    }

    public function getValues(Query $query, int $offset = 0, ?int $limit = null) : iterable
    {
        $contentenfulReferenceEntries = [];
        foreach ($this->getEntries($query) as $reference) {
            $contentenfulReferenceEntries[] = $this->contentful->loadContentfulEntry($reference->getSpace()->getId() ."|". $reference->getId());
        }

        return $contentenfulReferenceEntries;
    }

    public function getCount(Query $query) : int
    {
        return count($this->getEntries($query));
    }

    public function isContextual(Query $query) : bool
    {
        return true;
    }

    /**
     * Return filtered offset value to use.
     *
     * @param int $offset
     *
     * @return int
     */
    private function getOffset($offset) : int
    {
        if (is_int($offset) && $offset >= 0) {
            return $offset;
        }

        return 0;
    }

    /**
     * Return filtered limit value to use.
     *
     * @param int $limit
     *
     * @return int
     */
    private function getLimit($limit) : int
    {
        if (is_int($limit) && $limit >= 0) {
            return $limit;
        }

        return null;
    }

    /**
     * Gets context entry from current parameters.
     *
     * @param \Netgen\Layouts\API\Values\Collection\Query $query
     *
     * @return \Contentful\Delivery\EntryInterface[]
     */
    private function getEntries(Query $query) : iterable
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $contextEntry = $this->contentful->loadContentfulEntry($currentRequest->attributes->get("_route"));
        $funcName = "get". $query->getParameter('field_definition_identifier')->getValue();

        return $contextEntry->$funcName();
    }
}
