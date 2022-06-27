<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Tests\Fixtures;

class Person
{
    public array $children;

    public int $height;

    public function __construct(array $children, int $height)
    {
        $this->children = $children;
        $this->height = $height;
    }
}
