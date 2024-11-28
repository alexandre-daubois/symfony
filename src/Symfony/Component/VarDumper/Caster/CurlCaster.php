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
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class CurlCaster
{
    public static function castCurl(\CurlHandle $h, array $a, Stub $stub, bool $isNested): array
    {
        return curl_getinfo($h);
    }

    public static function castCurlMulti(\CurlMultiHandle $h, array $a, Stub $stub, bool $isNested): array
    {
        return curl_multi_info_read($h) ?: [];
    }
}
