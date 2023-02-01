<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Attribute;

/**
 * Marks an element as sensitive, which indicates its content cloned by cloners.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_PROPERTY)]
class SensitiveElement
{
}
