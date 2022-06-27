<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Modifier;

use Symfony\Component\Query\Exception\InvalidModifierConfigurationException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
final class Limit extends AbstractModifier
{
    private readonly ?int $limit;

    public function __construct(Query $parentQuery, ?int $limit)
    {
        parent::__construct($parentQuery);

        $this->limit = $limit;
    }

    public function apply(array $source, QueryContext $context): array
    {
        if (null === $this->limit) {
            return $source;
        }

        if ($this->limit <= 0) {
            throw new InvalidModifierConfigurationException('limit', 'The limit must be a positive integer or null to set no limit');
        }

        return \array_slice($source, 0, $this->limit);
    }
}
