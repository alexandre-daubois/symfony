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
class IncompatibleCollectionException extends \Exception
{
    protected $message = 'The given collection is incompatible with "%s" because of the following reason: %s.';

    public function __construct(string $place, string $message)
    {
        parent::__construct(\sprintf($this->message, $place, $message));
    }
}
