<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Tests\Builder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Builder\ArrayShapeGenerator;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\FloatNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\Config\Definition\VariableNode;

class ArrayShapeGeneratorTest extends TestCase
{
    public function testInvalidFormatThrows()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported format to generate array shape. Expected one of "phpdoc", "jetbrains_array_shape", got "invalid_format".');

        ArrayShapeGenerator::generate(new ArrayNode('root'), 'invalid_format');
    }

    /**
     * @dataProvider provideNodes
     */
    public function testPhpDocHandlesNodeTypes(NodeInterface $node, string $expected)
    {
        $arrayNode = new ArrayNode('root');
        $arrayNode->addChild($node);

        $expected = 'node?: '.$expected;

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($arrayNode, ArrayShapeGenerator::FORMAT_PHPDOC));
    }

    /**
     * @dataProvider provideNodes
     */
    public function testJetbrainsArrayShapeHandlesNodeTypes(NodeInterface $node, string $expected)
    {
        $arrayNode = new ArrayNode('root');
        $arrayNode->addChild($node);

        $expected = "'node' => '$expected',";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($arrayNode, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public static function provideNodes(): iterable
    {
        yield [new ArrayNode('node'), 'array<array-key, mixed>'];

        yield [new BooleanNode('node'), 'bool'];
        yield [new EnumNode('node', values: ['a', 'b']), '"a"|"b"'];
        yield [new ScalarNode('node'), 'string|int|float|bool'];
        yield [new VariableNode('node'), 'mixed'];

        yield [new IntegerNode('node'), 'int<min, max>'];
        yield [new IntegerNode('node', min: 1), 'int<1, max>'];
        yield [new IntegerNode('node', max: 10), 'int<min, 10>'];
        yield [new IntegerNode('node', min: 1, max: 10), 'int<1, 10>'];

        yield [new FloatNode('node'), 'float<min, max>'];
        yield [new FloatNode('node', min: 1.1), 'float<1.1, max>'];
        yield [new FloatNode('node', max: 10.1), 'float<min, 10.1>'];
        yield [new FloatNode('node', min: 1.1, max: 10.1), 'float<1.1, 10.1>'];
    }

    public function testPhpDocHandlesRequiredNode()
    {
        $child = new BooleanNode('node');
        $child->setRequired(true);

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = 'node: bool';

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_PHPDOC));
    }

    public function testJetbrainsArrayShapeDoesntHandleRequiredNode()
    {
        $child = new BooleanNode('node');
        $child->setRequired(true);

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = "'node' => 'bool',";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public function testPhpDocDoesntHandleAdditionalDocumentation()
    {
        $child = new BooleanNode('node');
        $child->setDeprecated('vendor/package', '1.0', 'The "%path%" option is deprecated.');
        $child->setDefaultValue(true);
        $child->setInfo('This is a boolean node.');

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = "node?: bool,\n";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_PHPDOC));
    }

    public function testJetbrainsArrayShapeHandlesDeprecatedNode()
    {
        $child = new BooleanNode('node');
        $child->setDeprecated('vendor/package', '1.0', 'The "%path%" option is deprecated.');

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = "'node' => 'bool', /* Deprecated: The \"node\" option is deprecated. */";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public function testJetbrainsArrayShapeHandlesDefaultValueNode()
    {
        $child = new BooleanNode('node');
        $child->setDefaultValue(true);

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = "'node' => 'bool', /* Default value: true. */";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public function testJetbrainsArrayShapeHandlesNodeInfo()
    {
        $child = new BooleanNode('node');
        $child->setInfo('This is a boolean node.');

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = "'node' => 'bool', /* This is a boolean node. */";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public function testJetbrainsArrayShapeHandlesAllDocOnNode()
    {
        $child = new BooleanNode('node');
        $child->setDeprecated('vendor/package', '1.0', 'The "%path%" option is deprecated.');
        $child->setDefaultValue(true);
        $child->setInfo('This is a boolean node.');

        $root = new ArrayNode('root');
        $root->addChild($child);

        $expected = "'node' => 'bool', /* Deprecated: The \"node\" option is deprecated. This is a boolean node. Default value: true. */";

        $this->assertStringContainsString($expected, ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public function testJetbrainsShape()
    {
        $root = new ArrayNode('root');

        $this->assertStringMatchesFormat('#[ArrayShape(%A)]', ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
    }

    public function testPhpDocShapeSingleLevel()
    {
        $root = new ArrayNode('root');

        $this->assertStringMatchesFormat('array<%s>', ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_PHPDOC));
    }

    public function testPhpDocShapeMultiLevel()
    {
        $root = new ArrayNode('root');
        $child = new ArrayNode('child');
        $root->addChild($child);

        $this->assertStringMatchesFormat('array{%Achild?: array<%s>,%A}', ArrayShapeGenerator::generate($root, ArrayShapeGenerator::FORMAT_PHPDOC));
    }
}
