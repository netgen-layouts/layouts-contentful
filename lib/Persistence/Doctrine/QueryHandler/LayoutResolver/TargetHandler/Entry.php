<?php

namespace Netgen\BlockManager\Contentful\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Netgen\BlockManager\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler;

final class Entry implements TargetHandler
{
    public function handleQuery(QueryBuilder $query, $value)
    {
        $query->andWhere(
            $query->expr()->eq('rt.value', ':target_value')
        )
            ->setParameter('target_value', $value, Type::STRING);
    }
}
