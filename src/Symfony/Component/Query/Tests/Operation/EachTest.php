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

class EachTest extends AbstractQueryTest
{
    public function testEach(): void
    {
        $query = new Query();
        $query
            ->from($this->cities)
            ->selectMany('persons', 'p');

        $result = $query
            ->each(fn($element) => $element->height * 2);

        $this->assertSame(362, $result[0]);
        $this->assertSame(352, $result[1]);
    }
}
