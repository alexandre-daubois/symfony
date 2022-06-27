<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 * @author Hubert Lenoir <lenoir.hubert@gmail.com>
 *
 * @experimental
 */
final class QueryContext
{
    public function __construct(
        /** @var \SplObjectStorage<array<string, object>> */
        readonly private \SplObjectStorage $environments = new \SplObjectStorage,
        /** @var array<string, true> */
        readonly private array $usedAliases = [],
    )
    {}

    public function getEnvironment(object $environment): array
    {
        return $this->environments[$environment] ?? [];
    }

    public function withEnvironment(object $environment, array $info): self
    {
        $environments = clone($this->environments);
        $environments[$environment] = $info + $this->getEnvironment($environment);

        return new self($environments, $this->usedAliases);
    }

    public function isUsedAlias(string $alias): bool
    {
        return isset($this->usedAliases[$alias]);
    }

    public function withUsedAlias(string $alias): self
    {
        return new self(clone($this->environments), $this->usedAliases + [$alias => true]);
    }
}
