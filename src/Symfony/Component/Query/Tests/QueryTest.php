<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Tests;

use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Query\Exception\AliasAlreadyTakenInQueryContextException;
use Symfony\Component\Query\Exception\IncompatibleCollectionException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryOrder;

class QueryTest extends AbstractQueryTest
{
    public function testSimpleAlias(): void
    {
        $query = new Query();
        $query->from($this->cities, 'city')
            ->where('city.name == "Lyon"');

        $this->assertSame('Lyon', $query->selectOne('name'));
    }

    public function testWrongAlias(): void
    {
        $query = new Query();
        $query->from($this->cities, 'element')
            ->where('city.name == "Lyon"');

        $this->expectException(SyntaxError::class);
        $query->select();
    }

    public function testAliasAlreadyInUse(): void
    {
        $this->expectException(AliasAlreadyTakenInQueryContextException::class);
        $this->expectExceptionMessage('Alias "__" is already taken in the query. You should choose another name for your alias.');

        $query = new Query();
        $query
            ->from($this->cities, '__')
            ->selectMany('persons', '__');
    }

    public function testFromScalarCollection(): void
    {
        $query = new Query();

        $this->expectException(IncompatibleCollectionException::class);
        $this->expectExceptionMessage('The given collection is incompatible with "from" because of the following reason: Mixed and scalar collections are not supported. Collection must only contain objects to be used by Query.');
        $query
            ->from(self::NUMBERS);
    }

    public function testFromMixedCollection(): void
    {
        $query = new Query();

        $this->expectException(IncompatibleCollectionException::class);
        $this->expectExceptionMessage('The given collection is incompatible with "from" because of the following reason: Mixed and scalar collections are not supported. Collection must only contain objects to be used by Query.');
        $query
            ->from($this->cities + self::NUMBERS);
    }

    public function testSelectOnInitialQueryWithSubQueries(): void
    {
        $query = new Query();
        $query
            ->from($this->cities)
            ->orderBy(QueryOrder::Ascending, 'name')
            ->limit(1)
        ;

        $this->assertSame('Lyon', $query->selectOne('name'));

        $query
            ->selectMany('persons', '__')
        ;

        $query
            ->selectMany('children', '___')
            ->where('___.age >= 30')
        ;

        $this->assertSame('Hubert, Bob', $query->concat(', ', 'name'));
    }

    public function testSelectOnInitialQueryWithSubQueriesAndIntermediateWhere(): void
    {
        $query = new Query();
        $query
            ->from($this->cities)
            ->orderBy(QueryOrder::Ascending, 'name')
            ->limit(1)
        ;

        $this->assertSame('Lyon', $query->selectOne('name'));

        $query
            ->selectMany('persons', '__')
            ->where('__.height > 180')
        ;

        $query
            ->selectMany('children', '___')
            ->where('___.age >= 30')
        ;

        $this->assertSame('Hubert', $query->selectOne('name'));
    }
}
