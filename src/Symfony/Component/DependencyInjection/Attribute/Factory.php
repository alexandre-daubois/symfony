<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Attribute;

/**
 * An attribute to configure the factory to use on
 * a service.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Factory extends Autoconfigure
{
    public function __construct(
        array|string $factory,
        array $bind = null,
    ) {
        parent::__construct(bind: $bind, factory: $factory);
    }
}
