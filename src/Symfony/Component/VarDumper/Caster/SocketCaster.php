<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
final class SocketCaster
{
    public static function castSocket(\Socket $h, array $a, Stub $stub, bool $isNested): array
    {
        socket_getsockname($h, $addr, $port);
        $info = socket_get_status(socket_export_stream($h));

        $a += [
            Caster::PREFIX_VIRTUAL.'address' => $addr,
            Caster::PREFIX_VIRTUAL.'port' => $port,
            Caster::PREFIX_VIRTUAL.'info' => new EnumStub([
                'timed_out' => new ConstStub($info['timed_out'] ? 'true' : 'false'),
                'blocked' => new ConstStub($info['blocked'] ? 'true' : 'false'),
                'eof' => new ConstStub($info['eof'] ? 'true' : 'false'),
                'unread_bytes' => new ScalarStub($info['unread_bytes']),
                'stream_type' => new ConstStub($info['stream_type']),
                'wrapper_type' => new ConstStub($info['wrapper_type'] ?? '?'),
                'wrapper_data' => new ConstStub($info['wrapper_data'] ?? '?'),
                'mode' => new ConstStub($info['mode']),
                'seekable' => new ConstStub($info['seekable'] ? 'true' : 'false'),
                'uri' => new ConstStub($info['uri']),
            ]),
        ];

        return $a;
    }
}
