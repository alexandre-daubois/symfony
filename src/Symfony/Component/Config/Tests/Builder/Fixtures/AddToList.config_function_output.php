<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This function is automatically generated to help in creating a config.
 *
 * @param array{
 *     translator?: array{
 *         fallbacks?: array<string|int|float|bool>,
 *         sources?: array<string, string|int|float|bool>,
 *         books?: array{
 *             page?: array<array{
 *                 number?: int<min, max>,
 *                 content?: string|int|float|bool,
 *             }>,
 *         },
 *     },
 *     messenger?: array{
 *         routing?: array<string, array{
 *             senders?: array<string|int|float|bool>,
 *         }>,
 *         receiving?: array<array{
 *             priority?: int<min, max>,
 *             color?: string|int|float|bool,
 *         }>,
 *     },
 * } $add_to_list
 *
 * @param array<array{
 *     resource: string,
 *     type?: string|int|float|bool,
 *     ignoreErrors?: string|int|float|bool,
 * }|non-empty-string> $imports
 *
 * @param array<string, mixed> $parameters
 *
 * @param array<array{
 *     id: string,
 *     class?: string|int|float|bool,
 * }>|array<string, class-string|null> $services
 */
function config(
    // add_to_list:
    #[ArrayShape([
        'translator' => [
            'fallbacks' => ['string|int|float|bool'],
            'sources' => ['string|int|float|bool'],
            'books' => [ /* Deprecated: The child node "books" at path "add_to_list.translator.books" is deprecated. looks for translation in old fashion way */
                'page' => [[
                    'number' => 'int<min, max>',
                    'content' => 'string|int|float|bool',
                ]],
            ],
        ],
        'messenger' => [
            'routing' => [[
                'senders' => ['string|int|float|bool'],
            ]],
            'receiving' => [[
                'priority' => 'int<min, max>',
                'color' => 'string|int|float|bool',
            ]],
        ],
    ])] array $add_to_list = [], 
    // imports:
    #[ArrayShape([[
        'resource' => 'string',
        'type' => 'string|int|float|bool', /* Default: null */
        'ignoreErrors' => 'string|int|float|bool', /* Default: false */
    ]])] array $imports = [], array $parameters = [], 
    // services:
    #[ArrayShape([[
        'id' => 'string',
        'class' => 'string|int|float|bool', /* Default: null */
    ]])] array $services = []): \Closure {
    return static function (\Symfony\Config\Add_to_listConfig $add_to_listConfig, \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) use ($add_to_list, $imports, $parameters, $services) {
        $add_to_listConfig->configure($add_to_list);

        foreach ($imports as $import) {
            if (\is_array($import)) {
                $containerConfigurator->import($import['resource'], $import['type'] ?? null, $import['ignoreErrors'] ?? false);
            } else {
                $containerConfigurator->import($import);
            }
        }
        $parametersConfigurator = $containerConfigurator->parameters();
        foreach ($parameters as $key => $value) {
            $parametersConfigurator->set($key, $value);
        }
        $servicesConfigurator = $containerConfigurator->services();
        foreach ($services as $id => $class) {
            if (\is_array($class)) {
                $servicesConfigurator->set($class['id'], $class['class'] ?? null);
            } else {
                $servicesConfigurator->set($id, $class);
            }
        }
    };
}
