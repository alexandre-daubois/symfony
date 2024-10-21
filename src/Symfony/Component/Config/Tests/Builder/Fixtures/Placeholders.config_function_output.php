<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     enabled?: bool,
 *     favorite_float?: float<min, max>,
 *     good_integers?: array<array-key, mixed>,
 * } $placeholders
 */
function config(
    // placeholders:
    #[ArrayShape([
        'enabled' => 'bool', /* Default: false. */
        'favorite_float' => 'float<min, max>',
        'good_integers' => 'array<array-key, mixed>',
    ])] array $placeholders = []): \Closure {
    return static function (\Symfony\Config\PlaceholdersConfig $placeholdersConfig) use ($placeholders) {
        $placeholdersConfig->configure($placeholders);
    };
}
