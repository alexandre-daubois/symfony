<?php

namespace Symfony\Component\HttpKernel\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\EventListener\CollectGcCyclesListener;

class CollectGcCyclesListenerTest extends TestCase
{
    public function testCollectGcCycles()
    {
        $listener = new CollectGcCyclesListener();
        $collected = gc_status()['collected'];

        $_SERVER['FRANKENPHP_WORKER'] = true;
        $listener->onKernelTerminate();

        $this->assertNotSame($collected, gc_status()['collected'], 'gc_collect_cycles() must have been called');
    }

    public function testDontCollectOnNonWorkerMode()
    {
        $listener = new CollectGcCyclesListener();
        $collected = gc_status()['collected'];

        $_SERVER['FRANKENPHP_WORKER'] = false;
        $listener->onKernelTerminate();

        $this->assertSame($collected, gc_status()['collected'], 'gc_collect_cycles() must not have been called');
    }
}
