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

class City
{
    public string $name;

    public array $persons;

    public int $minimalAge;

    public function __construct(string $name, array $persons, int $minimalAge)
    {
        $this->name = $name;
        $this->persons = $persons;
        $this->minimalAge = $minimalAge;
    }
}
