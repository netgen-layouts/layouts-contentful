<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Collection\QueryType\Handler;

use Contentful\Delivery\Query as ContentfulQuery;
use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\Collection\QueryType\QueryTypeHandlerInterface;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;

use function array_key_exists;
use function explode;
use function is_int;
use function max;
use function mb_trim;

/**
 * Handler for a query which retrieves the entries from Contentful.
 */
final class ContentfulSearchHandler implements QueryTypeHandlerInterface
{
    public function __construct(
        private Contentful $contentful,
    ) {}

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add(
            'client',
            ParameterType\ChoiceType::class,
            [
                'options' => [
                    ...(function (): iterable {
                        foreach ($this->contentful->getClients() as $clientName => $client) {
                            yield $client->getSpace()->getName() => $clientName;

                            /** @var \Contentful\Delivery\Resource\ContentType $contentType */
                            foreach ($client->getContentTypes()->getItems() as $contentType) {
                                yield '>  ' . $contentType->getName() => $clientName . '|' . $contentType->getId();
                            }
                        }
                    })(),
                ],
            ],
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
            ],
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
            ],
        );

        $builder->add(
            'search_text',
            ParameterType\TextLineType::class,
            [
                'groups' => [self::GROUP_ADVANCED],
            ],
        );
    }

    public function getValues(Query $query, int $offset = 0, ?int $limit = null): iterable
    {
        if ($limit === 0 || $query->getParameter('client')->value === null) {
            return [];
        }

        $optionsArray = explode('|', $query->getParameter('client')->value);

        $client = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntries(
            $this->getOffset($offset),
            $this->getLimit($limit),
            $client,
            $this->buildQuery($query),
        );
    }

    public function getCount(Query $query): int
    {
        if ($query->getParameter('client')->value === null) {
            return 0;
        }

        $optionsArray = explode('|', $query->getParameter('client')->value);

        $client = $this->contentful->getClientByName($optionsArray[0]);

        return $this->contentful->getContentfulEntriesCount($client, $this->buildQuery($query));
    }

    public function isContextual(Query $query): false
    {
        return false;
    }

    /**
     * Return filtered offset value to use.
     */
    private function getOffset(int $offset): int
    {
        return max(0, $offset);
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

        if (mb_trim($query->getParameter('search_text')->value ?? '') !== '') {
            $contentfulQuery->where('query', $query->getParameter('search_text')->value);
        }

        $optionsArray = explode('|', $query->getParameter('client')->value);
        if (array_key_exists(1, $optionsArray)) {
            $contentfulQuery->setContentType($optionsArray[1]);
        }

        $sortType = $query->getParameter('sort_type')->value;
        if ($sortType !== null) {
            $contentfulQuery->orderBy($sortType, $query->getParameter('sort_direction')->value);
        }

        return $contentfulQuery;
    }
}
