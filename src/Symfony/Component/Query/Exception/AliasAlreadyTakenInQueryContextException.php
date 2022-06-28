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
class AliasAlreadyTakenInQueryContextException extends \Exception
{
    protected $message = 'Alias "%s" is already taken in the query. You should choose another name for your alias.';

    public function __construct(string $alias)
    {
        parent::__construct(\sprintf($this->message, $alias));
    }
}
