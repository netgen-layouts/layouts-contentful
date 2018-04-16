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
            'search_text',
            ParameterType\TextLineType::class,
            array(
                'groups' => array(self::GROUP_ADVANCED),
            )
        );
    }

    public function getValues(Query $query, $offset = 0, $limit = null)
    {
        if ($query->getParameter('client')->getValue() === null || $limit === 0) {
            return array();
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());

        $client = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntries(
            $this->getOffset($offset),
            $this->getLimit($limit),
            $client,
            $this->buildQuery($query)
        );
    }

    public function getCount(Query $query)
    {
        if ($query->getParameter('client')->getValue() === null) {
            return 0;
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());

        $client = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntriesCount($client, $this->buildQuery($query));
    }

    public function isContextual(Query $query)
    {
        return false;
    }

    /**
     * Return filtered offset value to use.
     *
     * @param int $offset
     *
     * @return int
     */
    private function getOffset($offset)
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
    private function getLimit($limit)
    {
        if (is_int($limit) && $limit >= 0) {
            return $limit;
        }
    }

    /**
     * Builds the query from current parameters.
     *
     * @param \Netgen\BlockManager\API\Values\Collection\Query $query
     *
     * @return \Contentful\Delivery\Query
     */
    private function buildQuery(Query $query)
    {
        $contentfulQuery = new ContentfulQuery();

        if ($query->getParameter('search_text')->getValue()) {
            $contentfulQuery->where('query', $query->getParameter('search_text')->getValue());
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());
        if (array_key_exists(1, $optionsArray)) {
            $contentfulQuery->setContentType($optionsArray[1]);
        }

        $sortType = $query->getParameter('sort_type')->getValue();
        if ($sortType) {
            $contentfulQuery->orderBy($sortType, $query->getParameter('sort_direction')->getValue());
        }

        return $contentfulQuery;
    }
}
