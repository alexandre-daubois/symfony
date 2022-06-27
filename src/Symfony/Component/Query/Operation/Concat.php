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

use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
final class Concat extends AbstractOperation
{
    private readonly string $separator;

    public function __construct(Query $parentQuery, string $field, string $separator = ' ')
    {
        parent::__construct($parentQuery, $field);

        $this->separator = $separator;
    }

    public function apply(array $source, QueryContext $context): string
    {
        $source = $this->applySelect($source, $context);

        $string = '';
        foreach ($source as $key => $value) {
            $string .= $value;

            if ($key !== \array_key_last($source)) {
                $string .= $this->separator;
            }
        }

        return $string;
    }
}
