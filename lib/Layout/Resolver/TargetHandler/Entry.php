<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\TargetHandler;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Netgen\BlockManager\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler;

class Entry implements TargetHandler
{
    public function handleQuery(QueryBuilder $query, $value)
    {
        $query->andWhere(
            $query->expr()->eq('rt.value', ':target_value')
        )
            ->setParameter('target_value', $value, Type::STRING);
    }
}
