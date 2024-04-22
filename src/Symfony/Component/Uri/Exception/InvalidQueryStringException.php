<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Uri\Exception;

class InvalidQueryStringException extends \RuntimeException
{
    public function __construct(string $queryString)
    {
        parent::__construct(sprintf('The query string "%s" is invalid.', $queryString));
    }
}
