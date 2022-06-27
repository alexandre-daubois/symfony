<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Tests\Operation;

use Symfony\Component\Query\Exception\IncompatibleCollectionException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\Tests\AbstractQueryTest;

class AverageTest extends AbstractQueryTest
{
    public function testAverage(): void
    {
        $query = (new Query())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person');

        $query->selectMany('children', 'child')
            ->where('child.age > 20');

        $this->assertSame(30.75, $query->average('age'));
    }

    public function testAverageOnNonNumericCollection(): void
    {
        $query = new Query();
        $foo = new class {
            public array $collection = [1, 2, 3, 'average'];
        };

        $query->from([$foo]);

        $this->expectException(IncompatibleCollectionException::class);
        $this->expectExceptionMessage('The given collection is incompatible with "average" because of the following reason: Operation can only be applied to a collection of numerics.');
        $query->average('collection');
    }
}
