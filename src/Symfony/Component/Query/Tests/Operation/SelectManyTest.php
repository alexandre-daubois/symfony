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

use Symfony\Component\Query\Exception\IncompatibleFieldException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\Tests\AbstractQueryTest;

class SelectManyTest extends AbstractQueryTest
{
    public function testSelectMany(): void
    {
        $cityQuery = new Query();
        $result = $cityQuery
            ->from($this->cities)
            ->where('_.name contains "Lyon"')
            ->selectMany('persons', '__')
            ->where('__.height > 180')
            ->select();

        $this->assertCount(1, $result);
        $this->assertSame(181, $result[0]->height);
    }

    public function testSelectManyOnScalarField(): void
    {
        $cityQuery = new Query();

        $this->expectException(IncompatibleFieldException::class);
        $this->expectExceptionMessage('The given field is incompatible with "selectMany" because of the following reason: You can only selectMany on fields that are collections of objects.');
        $cityQuery
            ->from($this->cities)
            ->selectMany('minimalAge');
    }
}
