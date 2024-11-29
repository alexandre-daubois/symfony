<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Tests\Caster;

use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

/**
 * @requires extension sockets
 */
class SocketCasterTest extends TestCase
{
    use VarDumperTestTrait;

    public function testCastSocket()
    {
        $socket = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        @socket_connect($socket, '127.0.0.1', 80);

        $this->assertDumpMatchesFormat(
            <<<'EODUMP'
Socket {
  address: "127.0.0.1"
  port: %d
  timed_out: false
  blocked: true
  eof: false
  stream_type: "udp_socket"
  mode: "r+"
  unread_bytes: 0
  seekable: false%A
}
EODUMP, $socket);
    }
}
