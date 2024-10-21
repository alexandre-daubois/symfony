<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Builder;

use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
interface ConfigFunctionAwareBuilderGeneratorInterface
{
    /**
     * @param array<string, ConfigurationInterface> $configurations Configurations indexed by their alias
     *
     * @return \Closure that will return the "config()" function
     */
    public function buildConfigFunction(array $configurations): \Closure;
}
