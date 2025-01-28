<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonCrawler\Exception;

class InvalidJsonPathException extends \InvalidArgumentException
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('The JSON path "%s" is not valid.', $path));
    }
}
