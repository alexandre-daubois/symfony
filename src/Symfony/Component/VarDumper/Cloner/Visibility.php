<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Cloner;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
enum Visibility
{
    case Public;

    case Protected;

    case Private;
}
