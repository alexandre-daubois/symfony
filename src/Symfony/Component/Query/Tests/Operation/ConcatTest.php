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

class ConcatTest extends AbstractQueryTest
{
    public function testConcat(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->selectMany('persons', 'p')
            ->selectMany('children', 'c');

        $this->assertSame('Hubert, Alex, Will, Fabien, Nicolas, Salah, Bob', $query->concat(', ', 'name'));
    }
}
