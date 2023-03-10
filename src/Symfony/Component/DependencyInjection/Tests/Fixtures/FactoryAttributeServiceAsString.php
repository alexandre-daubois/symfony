<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Attribute\Factory;

#[Factory('create', ['$foo' => 'foo'])]
class FactoryAttributeServiceAsString
{
    public function __construct(private readonly string $bar = 'test')
    {
    }

    public function getBar(): string
    {
        return $this->bar;
    }

    public static function create(string $foo): static
    {
        return new self($foo);
    }
}
