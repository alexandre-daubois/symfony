<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     simple_array?: array<array-key, mixed>,
 *     keyed_array?: array<array-key, mixed>,
 *     object?: array{
 *         enabled?: bool,
 *         date_format?: string|int|float|bool,
 *         remove_used_context_fields?: bool,
 *     },
 *     list_object: array<array-key, mixed>,
 *     keyed_list_object?: array<array-key, mixed>,
 *     nested?: array{
 *         nested_object?: array{
 *             enabled?: bool,
 *         },
 *         nested_list_object?: array<array-key, mixed>,
 *     },
 * } $scalar_normalized_types
 */
function config(
    // scalar_normalized_types:
    #[ArrayShape([
        'simple_array' => 'array<array-key, mixed>',
        'keyed_array' => 'array<array-key, mixed>',
        'object' => [
            'enabled' => 'bool', /* Default: null. */
            'date_format' => 'string|int|float|bool',
            'remove_used_context_fields' => 'bool',
        ],
        'list_object' => 'array<array-key, mixed>',
        'keyed_list_object' => 'array<array-key, mixed>',
        'nested' => [
            'nested_object' => [
                'enabled' => 'bool', /* Default: null. */
            ],
            'nested_list_object' => 'array<array-key, mixed>',
        ],
    ])] array $scalar_normalized_types = []): \Closure {
    return static function (\Symfony\Config\Scalar_normalized_typesConfig $scalar_normalized_typesConfig) use ($scalar_normalized_types) {
        $scalar_normalized_typesConfig->configure($scalar_normalized_types);
    };
}
