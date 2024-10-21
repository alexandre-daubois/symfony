<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Builder;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\FloatNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\NumericNode;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\Config\Definition\StringNode;
use Symfony\Component\Config\Definition\VariableNode;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
final class ArrayShapeGenerator
{
    public const FORMAT_PHPDOC = 'phpdoc';
    public const FORMAT_JETBRAINS_ATTRIBUTE = 'jetbrains_attribute';

    /**
     * @param self::FORMAT_* $format
     */
    public static function generate(ArrayNode $node, string $format): string
    {
        if (self::FORMAT_PHPDOC === $format) {
            return static::prependPhpDocWithStar(static::doGeneratePhpDoc($node));
        }

        if (self::FORMAT_JETBRAINS_ATTRIBUTE === $format) {
            return static::doGenerateJetBrainsArrayShape($node);
        }

        throw new \LogicException(\sprintf('Unsupported format to generate array shape. Expected one of "%s", got "%s".', implode('", "', [self::FORMAT_PHPDOC, self::FORMAT_JETBRAINS_ATTRIBUTE]), $format));
    }

    private static function doGeneratePhpDoc(NodeInterface $node, int $nestingLevel = 1): string
    {
        if (!$node instanceof ArrayNode) {
            return $node->getName();
        }

        $arrayShape = 'array';

        if (!$children = $node->getChildren()) {
            return $arrayShape.'<array-key, mixed>';
        }

        $arrayShape .= '{'.\PHP_EOL;

        /** @var NodeInterface $child */
        foreach ($children as $child) {
            $arrayShape .= str_repeat(' ', $nestingLevel * 4).static::dumpNodeKey($child).': ';

            if ($child instanceof ArrayNode) {
                $arrayShape .= static::doGeneratePhpDoc($child, 1 + $nestingLevel);
            } else {
                $arrayShape .= static::handleNodeType($child);
            }

            $arrayShape .= ','.\PHP_EOL;
        }

        return $arrayShape.str_repeat(' ', 4 * ($nestingLevel - 1)).'}';
    }

    private static function doGenerateJetBrainsArrayShape(NodeInterface $node, int $nestingLevel = 1): string
    {
        if (!$node instanceof ArrayNode) {
            return $node->getName();
        }

        $children = $node->getChildren();

        $shape = '';
        if (1 === $nestingLevel) {
            $shape = '#[ArrayShape(';
        } elseif (!$children) {
            return "'array<array-key, mixed>'";
        }

        $shape .= '['.\PHP_EOL;

        /** @var BaseNode $child */
        foreach ($children as $child) {
            $shape .= \sprintf("%s'%s' => ", str_repeat(' ', 4 * $nestingLevel), $child->getName());
            if ($child instanceof ArrayNode) {
                $shape .= static::doGenerateJetBrainsArrayShape($child, 1 + $nestingLevel);
            } else {
                $shape .= "'".static::handleNodeType($child)."'";
            }

            $shape .= ','.static::generateInlinePhpDocForNode($child).\PHP_EOL;
        }

        $shape .= str_repeat(' ', 4 * ($nestingLevel - 1)).']';

        return $shape.(1 === $nestingLevel ? ')]' : '');
    }

    private static function dumpNodeKey(NodeInterface $node): string
    {
        return $node->getName().($node->isRequired() ? '' : '?');
    }

    private static function handleNumericNode(NumericNode $node): string
    {
        if ($node instanceof IntegerNode) {
            $type = 'int<%s, %s>';
        } elseif ($node instanceof FloatNode) {
            $type = 'float<%s, %s>';
        } else {
            $type = 'int<%s, %s>|float<%1$s, %2$s>';
        }

        $min = $node->getMin() ?? 'min';
        $max = $node->getMax() ?? 'max';

        return \sprintf($type, $min, $max);
    }

    private static function prependPhpDocWithStar(string $shape): string
    {
        return str_replace("\n", "\n * ", $shape);
    }

    private static function generateInlinePhpDocForNode(BaseNode $node): string
    {
        $hasContent = false;
        $comment = ' /* ';

        if ($node->hasDefaultValue() || $node->getInfo() || $node->isDeprecated()) {
            if ($node->isDeprecated()) {
                $hasContent = true;
                $comment .= 'Deprecated: '.$node->getDeprecation($node->getName(), $node->getPath())['message'].' ';
            }

            if ($info = $node->getInfo()) {
                $hasContent = true;
                $comment .= $info.' ';
            }

            if ($node->hasDefaultValue() && !\is_array($defaultValue = $node->getDefaultValue())) {
                $hasContent = true;
                $comment .= 'Default: '.json_encode($defaultValue).'. ';
            }

            $comment .= '*/';
        }

        return $hasContent ? $comment : '';
    }

    private static function handleNodeType(NodeInterface $node): string
    {
        return match (true) {
            $node instanceof BooleanNode => 'bool',
            $node instanceof StringNode => 'string',
            $node instanceof NumericNode => static::handleNumericNode($node),
            $node instanceof EnumNode => $node->getPermissibleValues('|'),
            $node instanceof ScalarNode => 'string|int|float|bool',
            $node instanceof VariableNode => 'mixed',
        };
    }
}
