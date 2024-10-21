<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     foo?: array{
 *         baz?: string|int|float|bool,
 *         qux?: string|int|float|bool,
 *     },
 *     bar?: array<array-key, mixed>,
 *     baz?: array<array-key, mixed>,
 * } $array_extra_keys
 */
function config(
    // array_extra_keys:
    #[ArrayShape([
        'foo' => [
            'baz' => 'string|int|float|bool',
            'qux' => 'string|int|float|bool',
        ],
        'bar' => 'array<array-key, mixed>',
        'baz' => 'array<array-key, mixed>',
    ])] array $array_extra_keys = []): \Closure {
    return static function (\Symfony\Config\Array_extra_keysConfig $array_extra_keysConfig) use ($array_extra_keys) {
        $array_extra_keysConfig->configure($array_extra_keys);
    };
}
