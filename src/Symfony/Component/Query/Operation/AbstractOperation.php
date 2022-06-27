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

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
abstract class AbstractOperation implements OperationInterface
{
    protected readonly array|string|null $fields;
    protected Query $parentQuery;
    protected PropertyAccessor $propertyAccessor;

    public function __construct(Query $parentQuery, array|string|null $fields = null)
    {
        $this->parentQuery = $parentQuery;
        $this->fields = $fields;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    protected function applySelect(array $source, QueryContext $context): array
    {
        if ($where = $this->parentQuery->getWhere()) {
            $source = $where->apply($source, $context);
        }

        if ($orderBy = $this->parentQuery->getOrderBy()) {
            $source = $orderBy->apply($source, $context);
        }

        if ($offset = $this->parentQuery->getOffset()) {
            $source = $offset->apply($source, $context);
        }

        if ($limit = $this->parentQuery->getLimit()) {
            $source = $limit->apply($source, $context);
        }

        if (null !== $this->fields) {
            $filteredResult = [];

            foreach ($source as $item) {
                if (\is_string($this->fields)) {
                    $fieldsValues = $this->propertyAccessor->getValue($item, $this->fields);
                } else {
                    $fieldsValues = [];
                    foreach ($this->fields as $field) {
                        $fieldsValues[$field] = $this->propertyAccessor->getValue($item, $field);
                    }

                }

                $filteredResult[] = $fieldsValues;
            }

            $source = $filteredResult;
        }

        return $source;
    }
}
