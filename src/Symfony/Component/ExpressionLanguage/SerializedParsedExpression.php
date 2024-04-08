<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Node\Node;

/**
 * Represents an already serialized parsed expression.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SerializedParsedExpression extends ParsedExpression
{
    /**
     * @param $expression An expression
     * @param $nodes      The serialized nodes for the expression
     */
    public function __construct(
        string $expression,
        private string $nodes,
    ) {
        $this->expression = $expression;
    }

    public function getNodes(): Node
    {
        return unserialize($this->nodes);
    }
}
