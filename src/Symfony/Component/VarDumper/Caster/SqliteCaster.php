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
class SqliteCaster
{
    public static function castSqlite3Result(\SQLite3Result $c, array $a, Stub $stub, bool $isNested): array
    {
        $a += [
            Caster::PREFIX_VIRTUAL.'numColumns' => $c->numColumns(),
            Caster::PREFIX_VIRTUAL.'result' => $c->fetchArray(\SQLITE3_ASSOC),
        ];

        return $a;
    }
}
