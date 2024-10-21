<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     some_clever_name?: array{
 *         first?: string|int|float|bool,
 *         second?: string|int|float|bool,
 *         third?: string|int|float|bool,
 *     },
 *     messenger?: array{
 *         transports?: array<array-key, mixed>,
 *     },
 * } $node_initial_values
 */
function config(
    // node_initial_values:
    #[ArrayShape([
        'some_clever_name' => [
            'first' => 'string|int|float|bool',
            'second' => 'string|int|float|bool',
            'third' => 'string|int|float|bool',
        ],
        'messenger' => [
            'transports' => 'array<array-key, mixed>',
        ],
    ])] array $node_initial_values = []): \Closure {
    return static function (\Symfony\Config\Node_initial_valuesConfig $node_initial_valuesConfig) use ($node_initial_values) {
        $node_initial_valuesConfig->configure($node_initial_values);
    };
}
