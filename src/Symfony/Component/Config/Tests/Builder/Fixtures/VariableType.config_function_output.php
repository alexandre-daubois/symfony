<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     any_value?: mixed,
 * } $variable_type
 */
function config(
    // variable_type:
    #[ArrayShape([
        'any_value' => 'mixed',
    ])] array $variable_type = []): \Closure {
    return static function (\Symfony\Config\Variable_typeConfig $variable_typeConfig) use ($variable_type) {
        $variable_typeConfig->configure($variable_type);
    };
}
