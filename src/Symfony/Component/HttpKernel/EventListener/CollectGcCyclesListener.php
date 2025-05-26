<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
final class CollectGcCyclesListener implements EventSubscriberInterface
{
    public function onKernelTerminate(): void
    {
        if (!($_SERVER['FRANKENPHP_WORKER'] ?? false)) {
            return;
        }

        gc_collect_cycles();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onKernelTerminate', -2048],
        ];
    }
}
