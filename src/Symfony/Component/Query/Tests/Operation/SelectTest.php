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

class SelectTest extends AbstractQueryTest
{
    public function testObjectsSelect(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->select('name');

        $this->assertSame('Lyon', $result[0]);
        $this->assertSame('Paris', $result[1]);
    }

    public function testObjectsMultipleSelect(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->select(['name', 'minimalAge']);

        $this->assertSame('Lyon', $result[0]['name']);
        $this->assertSame(21, $result[0]['minimalAge']);
        $this->assertSame('Paris', $result[1]['name']);
        $this->assertSame(10, $result[1]['minimalAge']);
    }
}
