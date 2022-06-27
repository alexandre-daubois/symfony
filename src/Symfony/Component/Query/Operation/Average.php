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

use Symfony\Component\Query\Exception\IncompatibleCollectionException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
final class Average extends AbstractOperation
{
    public function __construct(Query $parentQuery, string $field)
    {
        parent::__construct($parentQuery, $field);
    }

    public function apply(array $source, QueryContext $context): float
    {
        $source = $this->applySelect($source, $context);

        $count = \count($source);
        if ($count !== \count(\array_filter($source, 'is_numeric'))) {
            throw new IncompatibleCollectionException('average', 'Operation can only be applied to a collection of numerics');
        }

        return (float) (\array_sum($source) / $count);
    }
}
