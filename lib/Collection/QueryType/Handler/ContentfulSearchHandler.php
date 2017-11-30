<?php

namespace Netgen\BlockManager\Contentful\Collection\QueryType\Handler;

use Contentful\Delivery\Query as ContentfulQuery;
use Netgen\BlockManager\API\Values\Collection\Query;
use Netgen\BlockManager\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;
use Netgen\BlockManager\Parameters\ParameterType;

/**
 * Handler for a query which retrieves the entries from Contentful.
 */
final class ContentfulSearchHandler implements QueryTypeHandlerInterface
{
    /**
     * @const int
     */
    const DEFAULT_LIMIT = 25;

    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function buildParameters(ParameterBuilderInterface $builder)
    {
        $builder->add(
            'client',
            ParameterType\ChoiceType::class,
            array(
                'options' => $this->contentful->getClientsAndContentTypesAsChoices(),
            )
        );

        $builder->add(
            'sort_type',
            ParameterType\ChoiceType::class,
            array(
                'required' => false,
                'options' => array(
                    'Created' => 'sys.createdAt',
                    'Updated' => 'sys.updatedAt',
                ),
            )
        );

        $builder->add(
            'sort_direction',
            ParameterType\ChoiceType::class,
            array(
                'required' => true,
                'options' => array(
                    'Descending' => true,
                    'Ascending' => false,
                ),
            )
        );

        $builder->add(
            'limit',
            ParameterType\IntegerType::class,
            array(
                'min' => 0,
            )
        );

        $builder->add(
            'offset',
            ParameterType\IntegerType::class,
            array(
                'min' => 0,
                'groups' => array(self::GROUP_ADVANCED),
            )
        );

        $builder->add(
            'search_text',
            ParameterType\TextLineType::class,
            array(
                'groups' => array(self::GROUP_ADVANCED),
            )
        );
    }

    public function getValues(Query $query, $offset = 0, $limit = null)
    {
        if ($query->getParameter('client')->getValue() === null) {
            return array();
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());

        /** @var \Contentful\Delivery\Client $contentfulService */
        $contentfulService = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntries($offset, $limit, $contentfulService, $this->buildQuery($query));
    }

    public function getCount(Query $query)
    {
        if ($query->getParameter('client')->getValue() === null) {
            return 0;
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());

        /** @var \Contentful\Delivery\Client $contentfulService */
        $contentfulService = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntriesCount($contentfulService, $this->buildQuery($query, true));
    }

    public function getInternalLimit(Query $query)
    {
        $limit = $query->getParameter('limit')->getValue();
        if (!is_int($limit)) {
            return self::DEFAULT_LIMIT;
        }

        return $limit >= 0 ? $limit : self::DEFAULT_LIMIT;
    }

    public function isContextual(Query $query)
    {
        return false;
    }

    /**
     * Builds the query from current parameters.
     *
     * @param \Netgen\BlockManager\API\Values\Collection\Query $query
     * @param bool $buildCountQuery
     *
     * @return \Contentful\Delivery\Query
     */
    private function buildQuery(Query $query, $buildCountQuery = false)
    {
        $contentfulQuery = new ContentfulQuery();

        if ($query->getParameter('search_text')->getValue()) {
            $contentfulQuery->where('query', $query->getParameter('search_text')->getValue());
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());
        if (array_key_exists(1, $optionsArray)) {
            $contentfulQuery->setContentType($optionsArray[1]);
        }

        if (!$buildCountQuery) {
            $offset = $query->getParameter('offset')->getValue();
            $contentfulQuery->setSkip(is_int($offset) && $offset >= 0 ? $offset : 0);
            $contentfulQuery->setLimit($this->getInternalLimit($query));
        }

        $sortType = $query->getParameter('sort_type')->getValue();
        if ($sortType) {
            $contentfulQuery->orderBy($sortType, $query->getParameter('sort_direction')->getValue());
        }

        return $contentfulQuery;
    }
}
