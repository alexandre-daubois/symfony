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

use Symfony\Component\Query\Exception\InvalidModifierConfigurationException;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryOrder;
use Symfony\Component\Query\Tests\AbstractQueryTest;

class LimitTest extends AbstractQueryTest
{
    public function testLimit(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->limit(1)
            ->select();

        $this->assertCount(1, $result);
    }

    public function testNullLimit(): void
    {
        $query = new Query();
        $result = $query->from($this->cities)
            ->limit(null)
            ->select();

        $this->assertCount(\count($this->cities), $result);
    }

    public function testNegativeLimit(): void
    {
        $query = new Query();

        $this->expectException(InvalidModifierConfigurationException::class);
        $this->expectExceptionMessage('The limit must be a positive integer or null to set no limit.');
        $query->from($this->cities)
            ->limit(-1)
            ->select();
    }

    public function testLimitWithObjects(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->orderBy(QueryOrder::Descending, 'minimalAge')
            ->limit(1);

        $result = $query->select();
        $this->assertCount(1, $result);
        $this->assertSame('Lyon', $result[0]->name);
    }
}
