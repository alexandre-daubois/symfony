<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonPath\Exception;

/**
 * Thrown when a JSON passed as an input is invalid, e.g. in {@see JsonCrawler}.
 *
 * @experimental
 */
class InvalidInputJsonException extends \InvalidArgumentException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Invalid input JSON: %s.', $message), previous: $previous);
    }
}
