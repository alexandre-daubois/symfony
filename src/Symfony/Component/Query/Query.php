<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\Query\Exception\AliasAlreadyTakenInQueryContextException;
use Symfony\Component\Query\Exception\AlreadyRegisteredWhereFunctionException;
use Symfony\Component\Query\Exception\IncompatibleCollectionException;
use Symfony\Component\Query\Modifier\Limit;
use Symfony\Component\Query\Modifier\Offset;
use Symfony\Component\Query\Modifier\OrderBy;
use Symfony\Component\Query\Modifier\Where;
use Symfony\Component\Query\Operation\Average;
use Symfony\Component\Query\Operation\Concat;
use Symfony\Component\Query\Operation\Count;
use Symfony\Component\Query\Operation\Each;
use Symfony\Component\Query\Operation\Max;
use Symfony\Component\Query\Operation\Min;
use Symfony\Component\Query\Operation\Select;
use Symfony\Component\Query\Operation\SelectMany;
use Symfony\Component\Query\Operation\SelectOne;
use Symfony\Component\Query\Operation\Sum;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
class Query
{
    private array $source;
    private string $sourceAlias;

    private ?Where $where = null;
    private ?OrderBy $orderBy = null;
    private ?Limit $limit = null;
    private ?Offset $offset = null;

    private QueryContext $context;

    private ?Query $subQuery = null;

    private static array $registeredWhereFunctions = [];
    private static array $registeredWhereFunctionNames = [];

    public function __construct(QueryContext $context = null)
    {
        $this->context = $context ?? new QueryContext();
    }

    public function from(array $source, string $alias = '_'): Query
    {
        if ($this->context->isUsedAlias($alias)) {
            throw new AliasAlreadyTakenInQueryContextException($alias);
        }

        $this->source = $source;
        $this->sourceAlias = $alias;
        $this->context = $this->context->withUsedAlias($alias);

        $countObjects = \count(\array_filter($source, 'is_object'));

        if (\count($source) !== $countObjects) {
            throw new IncompatibleCollectionException('from', 'Mixed and scalar collections are not supported. Collection must only contain objects to be used by Query');
        }

        return $this;
    }

    public function where(string $expression, array $environment = []): Query
    {
        if ($this->subQuery) {
            return $this->subQuery->where($expression, $environment);
        }

        $this->where = new Where($this, $expression, $environment);

        return $this;
    }

    public function orderBy(QueryOrder $order, ?string $field = null): Query
    {
        if ($this->subQuery) {
            return $this->subQuery->orderBy($order, $field);
        }

        $this->orderBy = new OrderBy($this, $order, $field);

        return $this;
    }

    public function limit(?int $limit): Query
    {
        if ($this->subQuery) {
            return $this->subQuery->limit($limit);
        }

        $this->limit = new Limit($this, $limit);

        return $this;
    }

    public function offset(?int $offset): Query
    {
        if ($this->subQuery) {
            return $this->subQuery->offset($offset);
        }

        $this->offset = new Offset($this, $offset);

        return $this;
    }

    public function selectMany(string $field, ?string $alias = '_'): Query
    {
        if ($this->subQuery) {
            $this->subQuery->selectMany($field, $alias);

            return $this;
        }

        $this->subQuery = (new SelectMany($this, $field, $alias))
            ->apply($this->source, $this->context);

        return $this;
    }

    public function select(array|string|null $fields = null): array
    {
        return $this->applyOperation(Select::class, [$fields]);
    }

    public function selectOne(string|null $fields = null): mixed
    {
        return $this->applyOperation(SelectOne::class, [$fields]);
    }

    public function count(): int
    {
        return $this->applyOperation(Count::class);
    }

    public function concat(string $separator = ' ', ?string $field = null): string
    {
        return $this->applyOperation(Concat::class, [$field, $separator]);
    }

    public function each(callable $callback): array
    {
        return $this->applyOperation(Each::class, [$callback]);
    }

    public function max(?string $field = null): mixed
    {
        return $this->applyOperation(Max::class, [$field]);
    }

    public function min(?string $field = null): mixed
    {
        return $this->applyOperation(Min::class, [$field]);
    }

    public function average(?string $field = null): float
    {
        return $this->applyOperation(Average::class, [$field]);
    }

    public function sum(?string $field = null): int|float
    {
        return $this->applyOperation(Sum::class, [$field]);
    }

    public function getSourceAlias(): string
    {
        return $this->sourceAlias;
    }

    public function getWhere(): ?Where
    {
        return $this->where;
    }

    public function getOrderBy(): ?OrderBy
    {
        return $this->orderBy;
    }

    public function getLimit(): ?Limit
    {
        return $this->limit;
    }

    public function getOffset(): ?Offset
    {
        return $this->offset;
    }

    public function getContext(): QueryContext
    {
        return $this->context;
    }

    public static function registerWhereFunction(ExpressionFunction $expressionFunction): void
    {
        if (\in_array($expressionFunction->getName(), self::$registeredWhereFunctionNames, true)) {
            throw new AlreadyRegisteredWhereFunctionException($expressionFunction->getName());
        }

        self::$registeredWhereFunctions[] = $expressionFunction;
        self::$registeredWhereFunctionNames[] = $expressionFunction->getName();
    }

    public static function getRegisteredWhereFunctions(): array
    {
        return self::$registeredWhereFunctions;
    }

    private function applyOperation(string $operationClass, array $args = []): mixed
    {
        if ($this->subQuery) {
            return $this->subQuery->applyOperation($operationClass, $args);
        }

        return (new $operationClass($this, ...$args))
            ->apply($this->source, $this->context);
    }
}
