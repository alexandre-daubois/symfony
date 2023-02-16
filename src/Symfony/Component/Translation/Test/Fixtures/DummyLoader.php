<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Test\Fixtures;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DummyLoader implements LoaderInterface
{
    public function load($resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        return new MessageCatalogue($locale);
    }
}
