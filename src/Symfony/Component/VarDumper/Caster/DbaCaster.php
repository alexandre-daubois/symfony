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
class DbaCaster
{
    public static function castDbaConnection(\Dba\Connection $dba, array $a, Stub $stub, bool $isNested): array
    {
        // not yet possible to gather information about the connection object

        return $a;
    }

    public static function castDbaResource($dba, array $a, Stub $stub, bool $isNested): array
    {
        $list = dba_list();
        $a['file'] = $list[(int) $dba];

        return $a;
    }
}
