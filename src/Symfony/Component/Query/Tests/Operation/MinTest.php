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

class MinTest extends AbstractQueryTest
{
    public function testMin(): void
    {
        $query = (new Query())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->selectMany('children', 'child');

        $this->assertSame(8, $query->min('age'));
    }

    public function testMinWithoutResult(): void
    {
        $query = (new Query())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->selectMany('children', 'child')
            ->where('child.age < 0');

        $this->assertNull($query->min('age'));
    }
}
