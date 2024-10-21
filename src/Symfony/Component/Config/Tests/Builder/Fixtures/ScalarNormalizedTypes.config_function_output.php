<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This function is automatically generated to help in creating a config.
 *
 * @param array{
 *     simple_array?: array<string|int|float|bool>,
 *     keyed_array?: array<string, array<string|int|float|bool>>,
 *     object?: array{
 *         enabled?: bool,
 *         date_format?: string|int|float|bool,
 *         remove_used_context_fields?: bool,
 *     },
 *     list_object: array<array{
 *         name: string|int|float|bool,
 *         data?: array<mixed>,
 *     }>,
 *     keyed_list_object?: array<string, array{
 *         enabled?: bool,
 *         settings?: array<string|int|float|bool>,
 *     }>,
 *     nested?: array{
 *         nested_object?: array{
 *             enabled?: bool,
 *         },
 *         nested_list_object?: array<array{
 *             name: string|int|float|bool,
 *         }>,
 *     },
 * } $scalar_normalized_types
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
    // scalar_normalized_types:
    #[ArrayShape([
        'simple_array' => ['string|int|float|bool'],
        'keyed_array' => ['array<array-key, mixed>'],
        'object' => [
            'enabled' => 'bool', /* Default: null */
            'date_format' => 'string|int|float|bool',
            'remove_used_context_fields' => 'bool',
        ],
        'list_object' => [[
            'name' => 'string|int|float|bool',
            'data' => ['mixed'],
        ]],
        'keyed_list_object' => [[
            'enabled' => 'bool', /* Default: true */
            'settings' => ['string|int|float|bool'],
        ]],
        'nested' => [
            'nested_object' => [
                'enabled' => 'bool', /* Default: null */
            ],
            'nested_list_object' => [[
                'name' => 'string|int|float|bool',
            ]],
        ],
    ])] array $scalar_normalized_types = [], 
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
    return static function (\Symfony\Config\Scalar_normalized_typesConfig $scalar_normalized_typesConfig, \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) use ($scalar_normalized_types, $imports, $parameters, $services) {
        $scalar_normalized_typesConfig->configure($scalar_normalized_types);

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
