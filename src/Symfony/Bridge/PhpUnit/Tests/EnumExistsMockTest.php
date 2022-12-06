<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClassExistsMock;

class EnumExistsMockTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        ClassExistsMock::register(__CLASS__);
    }

    protected function setUp(): void
    {
        ClassExistsMock::withMockedClasses([
            ExistingEnum::class => false,
            'NonExistingEnum' => true,
        ]);
    }

    /**
     * @requires PHP 8.1
     */
    public function testEnumExists()
    {
        $this->assertFalse(enum_exists(ExistingEnum::class));
        $this->assertFalse(enum_exists(ExistingEnum::class, false));
        $this->assertFalse(enum_exists('\\'.ExistingEnum::class));
        $this->assertFalse(enum_exists('\\'.ExistingEnum::class, false));
        $this->assertTrue(enum_exists('NonExistingEnum'));
        $this->assertTrue(enum_exists('NonExistingEnum', false));
        $this->assertTrue(enum_exists('\\NonExistingEnum'));
        $this->assertTrue(enum_exists('\\NonExistingEnum', false));
        $this->assertTrue(enum_exists(ExistingEnumReal::class));
        $this->assertTrue(enum_exists(ExistingEnumReal::class, false));
        $this->assertTrue(enum_exists('\\'.ExistingEnumReal::class));
        $this->assertTrue(enum_exists('\\'.ExistingEnumReal::class, false));
        $this->assertFalse(enum_exists('NonExistingClassReal'));
        $this->assertFalse(enum_exists('NonExistingClassReal', false));
        $this->assertFalse(enum_exists('\\NonExistingEnumReal'));
        $this->assertFalse(enum_exists('\\NonExistingEnumReal', false));
    }
}

enum ExistingEnum
{
}

enum ExistingEnumReal
{
}
