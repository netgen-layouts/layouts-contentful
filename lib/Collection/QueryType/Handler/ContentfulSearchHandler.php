<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Collection\QueryType\Handler;

use Contentful\Delivery\Query as ContentfulQuery;
use Netgen\BlockManager\API\Values\Collection\Query;
use Netgen\BlockManager\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;
use Netgen\BlockManager\Parameters\ParameterType;
use Netgen\Layouts\Contentful\Service\Contentful;

/**
 * Handler for a query which retrieves the entries from Contentful.
 */
final class ContentfulSearchHandler implements QueryTypeHandlerInterface
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add(
            'client',
            ParameterType\ChoiceType::class,
            [
                'options' => $this->contentful->getClientsAndContentTypesAsChoices(),
            ]
        );

        $builder->add(
            'sort_type',
            ParameterType\ChoiceType::class,
            [
                'required' => false,
                'options' => [
                    'Created' => 'sys.createdAt',
                    'Updated' => 'sys.updatedAt',
                ],
            ]
        );

        $builder->add(
            'sort_direction',
            ParameterType\ChoiceType::class,
            [
                'required' => true,
                'options' => [
                    'Descending' => true,
                    'Ascending' => false,
                ],
            ]
        );

        $builder->add(
            'search_text',
            ParameterType\TextLineType::class,
            [
                'groups' => [self::GROUP_ADVANCED],
            ]
        );
    }

    public function getValues(Query $query, int $offset = 0, ?int $limit = null): iterable
    {
        if ($limit === 0 || $query->getParameter('client')->getValue() === null) {
            return [];
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

    public function getCount(Query $query): int
    {
        if ($query->getParameter('client')->getValue() === null) {
            return 0;
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());

        $client = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntriesCount($client, $this->buildQuery($query));
    }

    public function isContextual(Query $query): bool
    {
        return false;
    }

    /**
     * Return filtered offset value to use.
     */
    private function getOffset(int $offset): int
    {
        return $offset >= 0 ? $offset : 0;
    }

    /**
     * Return filtered limit value to use.
     */
    private function getLimit(?int $limit = null): ?int
    {
        if (is_int($limit) && $limit >= 0) {
            return $limit;
        }

        return null;
    }

    /**
     * Builds the query from current parameters.
     */
    private function buildQuery(Query $query): ContentfulQuery
    {
        $contentfulQuery = new ContentfulQuery();

        if (trim($query->getParameter('search_text')->getValue() ?? '') !== '') {
            $contentfulQuery->where('query', $query->getParameter('search_text')->getValue());
        }

        $optionsArray = explode('|', $query->getParameter('client')->getValue());
        if (array_key_exists(1, $optionsArray)) {
            $contentfulQuery->setContentType($optionsArray[1]);
        }

        $sortType = $query->getParameter('sort_type')->getValue();
        if ($sortType !== null) {
            $contentfulQuery->orderBy($sortType, $query->getParameter('sort_direction')->getValue());
        }

        return $contentfulQuery;
    }
}
