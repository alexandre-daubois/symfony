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
use Symfony\Component\Query\Tests\Fixtures\City;

class SelectOneTest extends AbstractQueryTest
{
    public function testObjectsSelectOne(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->where('_.name == "Lyon"')
            ->selectOne();

        $this->assertInstanceOf(City::class, $result);
        $this->assertSame('Lyon', $result->name);
    }

    public function testObjectsSelectOneWithoutResult(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->where('_.name == "Invalid city"')
            ->selectOne();

        $this->assertNull($result);
    }

    public function testSelectOneWithField(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->where('_.name == "Lyon"')
            ->selectOne('name');

        $this->assertIsString($result);
        $this->assertSame('Lyon', $result);
    }

    public function testSelectOneWithFieldWithoutResult(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->where('_.name == "Rouen"')
            ->selectOne('name');

        $this->assertNull($result);
    }
}
