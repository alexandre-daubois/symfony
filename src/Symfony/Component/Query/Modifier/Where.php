<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Query\Modifier;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Query\Query;
use Symfony\Component\Query\QueryContext;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @experimental
 */
final class Where extends AbstractModifier
{
    private readonly Expression $expression;
    private ExpressionLanguage $expressionLanguage;

    private readonly array $environment;

    public function __construct(Query $parentQuery, string $expression, array $environment = [])
    {
        parent::__construct($parentQuery);

        $this->expression = new Expression($expression);
        $this->environment = $environment;
        $this->expressionLanguage = new ExpressionLanguage();

        foreach (Query::getRegisteredWhereFunctions() as $function) {
            $this->expressionLanguage->addFunction($function);
        }
    }

    public function apply(array $source, QueryContext $context): array
    {
        $final = [];
        foreach ($source as $item) {
            $localContext = [$this->parentQuery->getSourceAlias() => $item] + $this->environment;
            $localContext += $context->getEnvironment($item);

            if ($this->expressionLanguage->evaluate($this->expression, $localContext)) {
                $final[] = $item;
            }
        }

        return $final;
    }
}
