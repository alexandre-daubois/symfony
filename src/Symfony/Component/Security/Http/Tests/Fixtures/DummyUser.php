<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Tests\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class DummyUser implements UserInterface
{
    public function getRoles(): array
    {
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
    }
}
