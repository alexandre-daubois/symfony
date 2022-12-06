<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Factory;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\VarDumperOptions;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
final class VarClonerFactory
{
    public static function withOptions(VarDumperOptions $options): VarCloner
    {
        $cloner = new VarCloner();

        if (null !== $maxItems = $options->get(VarDumperOptions::MAX_ITEMS)) {
            $cloner->setMaxItems($maxItems);
        }

        if (null !== $minDepth = $options->get(VarDumperOptions::MIN_DEPTH)) {
            $cloner->setMinDepth($minDepth);
        }

        if (null !== $maxString = $options->get(VarDumperOptions::MAX_STRING)) {
            $cloner->setMaxString($maxString);
        }

        return $cloner;
    }
}
