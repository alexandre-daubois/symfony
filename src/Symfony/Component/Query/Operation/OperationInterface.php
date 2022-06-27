<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Operation;

use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
interface OperationInterface
{
    public function apply(array $source, QueryContext $context): mixed;
}
