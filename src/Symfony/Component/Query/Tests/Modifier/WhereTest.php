<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Tests\Modifier;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\Query\Exception\AlreadyRegisteredWhereFunctionException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\Tests\AbstractQueryTest;

class WhereTest extends AbstractQueryTest
{
    public function testObjectsSelectWhere(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->where('_.minimalAge > 20');

        $this->assertSame('Lyon', \current($query->select())->name);
    }

    public function testWhereWithoutResult(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->where('_.minimalAge < 1');

        $this->assertEmpty($query->select());
    }

    public function testWhereWithAncestorNode(): void
    {
        $cityQuery = new Query();
        $result = $cityQuery
            ->from($this->cities, 'city')
            ->where('city.name contains "Lyon"')
            ->selectMany('persons', 'person')
            ->where('person.height > 180')
            ->selectMany('children', 'child')
            ->where('child.age > city.minimalAge')
            ->select('name');

        $this->assertCount(3, $result);
        $this->assertSame('Hubert', $result[0]);
        $this->assertSame('Alex', $result[1]);
        $this->assertSame('Will', $result[2]);
    }

    public function testRegisterExpressionFunction(): void
    {
        Query::registerWhereFunction(ExpressionFunction::fromPhp('strtoupper'));

        $query = new Query();
        $query->from($this->cities)
            ->where('strtoupper(_.name) == "LYON"');

        $this->assertSame('Lyon', \current($query->select())->name);

        $this->expectException(AlreadyRegisteredWhereFunctionException::class);
        $this->expectExceptionMessage('Function "strtoupper" has already been globally registered to be used in the "where" clause of Query.');
        Query::registerWhereFunction(ExpressionFunction::fromPhp('strtoupper'));
    }

    public function testWhereWithEnvironment(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->where('_.name in listOfCities', [
                'listOfCities' => [
                    'Lyon',
                    'Grenoble',
                    'Saint-Tropez',
                ]
            ]);

        $result = $query->select('name');
        $this->assertCount(1, $result);
        $this->assertSame('Lyon', $result[0]);
    }
}
