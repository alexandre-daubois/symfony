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

use Symfony\Component\Query\Query;
use Symfony\Component\Query\Tests\AbstractQueryTest;

class CountTest extends AbstractQueryTest
{
    public function testCount(): void
    {
        $query = new Query();
        $query
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->selectMany('children', 'child')
        ;

        $query->where('child.age < 9 or child.age > 28');

        $this->assertSame(3, $query->count());
    }
}
