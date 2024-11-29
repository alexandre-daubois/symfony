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

final class SocketCaster
{
    public static function castSocket(\Socket $socket, array $a, Stub $stub, bool $isNested): array
    {
        socket_getsockname($socket, $addr, $port);
        $info = stream_get_meta_data(socket_export_stream($socket));

        $a += [
            Caster::PREFIX_VIRTUAL.'address' => $addr,
            Caster::PREFIX_VIRTUAL.'port' => $port,
        ];

        foreach ($info as $key => $val) {
            $a[Caster::PREFIX_VIRTUAL.$key] = $val;
        }

        return $a;
    }
}
