<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * todo This listeners uses the interfaces of authenticators to
 * determine how to check credentials.
 *
 * @author Wouter de Jong <wouter@driveamber.com>
 *
 * @final
 */
class LateCheckPassportEventListener implements EventSubscriberInterface
{
    private $badges;

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $badges = $passport->getBadges();

        foreach ($badges as $badge) {
            $this->badges[] = [
                'badge' => $badge,
                'resolved' => $badge->isResolved(),
            ];
        }
    }

    public function getBadges()
    {
        return $this->badges;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', -2048]
        ];
    }
}
