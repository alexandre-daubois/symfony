<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Operation;

use Symfony\Component\Query\Exception\NonUniqueResultException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
final class SelectOne extends AbstractOperation
{
    public function __construct(Query $parentQuery, ?string $fields = null)
    {
        parent::__construct($parentQuery, $fields);

        $this->parentQuery = $parentQuery;
    }

    public function apply(array $source, QueryContext $context): mixed
    {
        $result = $this->applySelect($source, $context);

        $resultCount = \count($result);
        if ($resultCount > 1) {
            throw new NonUniqueResultException($resultCount);
        }

        return $result[0] ?? null;
    }
}
