<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Exception;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
class AlreadyRegisteredWhereFunctionException extends \Exception
{
    protected $message = 'Function "%s" has already been globally registered to be used in the "where" clause of Query.';

    public function __construct(string $functionName)
    {
        parent::__construct(\sprintf($this->message, $functionName));
    }
}
