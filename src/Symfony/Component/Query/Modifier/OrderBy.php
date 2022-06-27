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

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Query\Exception\InvalidModifierConfigurationException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;
use Symfony\Component\Query\QueryOrder;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
final class OrderBy extends AbstractModifier
{
    private readonly QueryOrder $orderBy;
    private readonly ?string $orderField;

    protected PropertyAccessor $propertyAccessor;

    public function __construct(Query $parentQuery, QueryOrder $orderBy = QueryOrder::None, ?string $orderField = null)
    {
        parent::__construct($parentQuery);

        $this->orderBy = $orderBy;
        $this->orderField = $orderField;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function apply(array $source, QueryContext $context): array
    {
        if (null !== $this->orderField && QueryOrder::Shuffle === $this->orderBy) {
            throw new InvalidModifierConfigurationException('orderBy', 'An order field must not be provided when shuffling a collection');
        }

        if (QueryOrder::Shuffle === $this->orderBy) {
            \shuffle($source);

            return $source;
        }

        if (QueryOrder::None !== $this->orderBy) {
            if (null === $this->orderField) {
                throw new InvalidModifierConfigurationException('orderBy', 'An order field must be provided');
            }

            \usort($source, function ($elementA, $elementB) {
                return $this->propertyAccessor->getValue($elementA, $this->orderField) <=> $this->propertyAccessor->getValue($elementB, $this->orderField);
            });

            if (QueryOrder::Descending === $this->orderBy) {
                $source = \array_reverse($source);
            }

            return $source;
        }

        return $source;
    }
}
