<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Query\Tests\Fixtures\Child;
use Symfony\Component\Query\Tests\Fixtures\City;
use Symfony\Component\Query\Tests\Fixtures\Person;

abstract class AbstractQueryTest extends TestCase
{
    protected const NUMBERS = [5, 4, 1, 3, 9, 8, 6, 7, 2, 0];

    protected array $cities = [];

    protected function setUp(): void
    {
        $this->cities[] = new City('Lyon', [
            new Person([
                new Child('Hubert', 30),
                new Child('Alex', 26),
                new Child('Will', 22),
            ], 181),
            new Person([
                new Child('Fabien', 10),
                new Child('Nicolas', 8),
                new Child('Salah', 11),
                new Child('Bob', 45),
            ], 176)
        ], 21);

        $this->cities[] = new City('Paris', [], 10);
    }
}
