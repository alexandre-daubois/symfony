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

class MaxTest extends AbstractQueryTest
{
    public function testMax(): void
    {
        $query = (new Query())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->selectMany('children', 'child');

        $this->assertSame(45, $query->max('age'));
    }

    public function testMaxWithoutResult(): void
    {
        $query = (new Query())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->where('person.height > 190')
            ->selectMany('children', 'child');

        $this->assertNull($query->max('age'));
    }
}
