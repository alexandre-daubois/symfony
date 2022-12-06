<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Tests\Dumper\ContextProvider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Dumper\ContextProvider\BacktraceContextProvider;

class BacktraceContextProviderTest extends TestCase
{
    public function testPositiveBacktraceLimit()
    {
        $provider = new BacktraceContextProvider(2);
        $this->assertCount(2, $provider->getContext());
    }
}
