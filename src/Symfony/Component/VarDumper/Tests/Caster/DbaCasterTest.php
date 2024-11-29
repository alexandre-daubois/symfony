<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Caster;

use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

/**
 * @requires extension dba
 */
class DbaCasterTest extends TestCase
{
    use VarDumperTestTrait;

    /**
     * @requires PHP 8.4
     */
    public function testCastDba()
    {
        $dba = dba_open(sys_get_temp_dir().'/test.db', 'c');

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
Dba\Connection {}
EODUMP, $dba);
    }

    /**
     * @requires PHP < 8.4
     */
    public function testCastDbaPriorToPhp84()
    {
        $dba = dba_open(sys_get_temp_dir().'/test.db', 'c');

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
dba resource {
  file: %s
}
EODUMP, $dba);
    }
}
