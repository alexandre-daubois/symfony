<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     boolean_node?: bool,
 *     enum_node?: "foo"|"bar"|"baz"|Symfony\Component\Config\Tests\Fixtures\TestEnum::Bar,
 *     float_node?: float<min, max>,
 *     integer_node?: int<min, max>,
 *     scalar_node?: string|int|float|bool,
 *     scalar_node_with_default?: string|int|float|bool,
 * } $primitive_types
 */
function config(
    // primitive_types:
    #[ArrayShape([
        'boolean_node' => 'bool',
        'enum_node' => '"foo"|"bar"|"baz"|Symfony\Component\Config\Tests\Fixtures\TestEnum::Bar',
        'float_node' => 'float<min, max>',
        'integer_node' => 'int<min, max>',
        'scalar_node' => 'string|int|float|bool',
        'scalar_node_with_default' => 'string|int|float|bool', /* Default: true. */
    ])] array $primitive_types = []): \Closure {
    return static function (\Symfony\Config\Primitive_typesConfig $primitive_typesConfig) use ($primitive_types) {
        $primitive_typesConfig->configure($primitive_types);
    };
}
