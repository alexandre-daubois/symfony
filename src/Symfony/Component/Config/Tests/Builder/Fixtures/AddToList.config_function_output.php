<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This class is automatically generated to help in creating a config.
 *
 * @param array{
 *     translator?: array{
 *         fallbacks?: array<array-key, mixed>,
 *         sources?: array<array-key, mixed>,
 *         books?: array{
 *             page?: array<array-key, mixed>,
 *         },
 *     },
 *     messenger?: array{
 *         routing?: array<array-key, mixed>,
 *         receiving?: array<array-key, mixed>,
 *     },
 * } $add_to_list
 */
function config(
    // add_to_list:
    #[ArrayShape([
        'translator' => [
            'fallbacks' => 'array<array-key, mixed>',
            'sources' => 'array<array-key, mixed>',
            'books' => [
                'page' => 'array<array-key, mixed>',
            ], /* Deprecated: The child node "books" at path "add_to_list.translator.books" is deprecated. looks for translation in old fashion way */
        ],
        'messenger' => [
            'routing' => 'array<array-key, mixed>',
            'receiving' => 'array<array-key, mixed>',
        ],
    ])] array $add_to_list = []): \Closure {
    return static function (\Symfony\Config\Add_to_listConfig $add_to_listConfig) use ($add_to_list) {
        $add_to_listConfig->configure($add_to_list);
    };
}
