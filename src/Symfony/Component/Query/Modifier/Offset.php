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
final class Offset extends AbstractModifier
{
    private readonly ?int $offset;

    public function __construct(Query $parentQuery, ?int $offset)
    {
        parent::__construct($parentQuery);

        $this->offset = $offset;
    }

    public function apply(array $source, QueryContext $context): array
    {
        if (null === $this->offset) {
            return $source;
        }

        if ($this->offset <= 0) {
            throw new InvalidModifierConfigurationException('offset', 'The offset must be a positive integer or null to set no offset.');
        }

        return \array_slice($source, $this->offset);
    }
}
