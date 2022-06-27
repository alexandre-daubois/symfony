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
final class Sum extends AbstractOperation
{
    public function __construct(Query $parentQuery, string $field)
    {
        parent::__construct($parentQuery, $field);
    }

    public function apply(array $source, QueryContext $context): int|float
    {
        $source = $this->applySelect($source, $context);

        if (\count($source) !== \count(\array_filter($source, 'is_numeric'))) {
            throw new IncompatibleCollectionException('sum', 'Operation can only be applied to a collection of numerics');
        }

        return \array_sum($source);
    }
}
