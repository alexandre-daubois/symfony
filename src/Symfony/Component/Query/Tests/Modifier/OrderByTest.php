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

class OrderByTest extends AbstractQueryTest
{
    public function testObjectsAscendingOrderBy(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->orderBy(QueryOrder::Ascending, 'minimalAge');

        $result = $query->select();
        $this->assertSame('Paris', $result[0]->name);
        $this->assertSame('Lyon', $result[1]->name);
    }

    public function testObjectsDescendingOrderBy(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->orderBy(QueryOrder::Descending, 'minimalAge');

        $result = $query->select();
        $this->assertSame('Lyon', $result[0]->name);
        $this->assertSame('Paris', $result[1]->name);
    }

    public function testObjectsShuffleWithOrderFieldFailure(): void
    {
        $query = new Query();
        $query->from($this->cities)
            ->orderBy(QueryOrder::Shuffle, 'minimalAge');

        $this->expectException(InvalidModifierConfigurationException::class);
        $this->expectExceptionMessage('The modifier "orderBy" is wrongly configured: An order field must not be provided when shuffling a collection.');
        $query->select();
    }

    public function testObjectsShuffle(): void
    {
        $query = (new Query())
            ->from($this->cities, 'city')
            ->selectMany('persons', 'person')
            ->selectMany('children', 'child')
            ->orderBy(QueryOrder::Shuffle);

        $firstShuffle = $query->concat(', ', 'name');
        $secondShuffle = $query->concat(', ', 'name');

        $this->assertNotSame($firstShuffle, $secondShuffle);
    }
}
