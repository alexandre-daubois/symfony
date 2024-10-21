<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This function is automatically generated to help in creating a config.
 *
 * @param array{
 *     some_clever_name?: array{
 *         first?: string|int|float|bool,
 *         second?: string|int|float|bool,
 *         third?: string|int|float|bool,
 *     },
 *     messenger?: array{
 *         transports?: array<string, array{
 *             dsn?: string|int|float|bool,
 *             serializer?: string|int|float|bool,
 *             options?: array<mixed>,
 *         }>,
 *     },
 * } $node_initial_values
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
    // node_initial_values:
    #[ArrayShape([
        'some_clever_name' => [
            'first' => 'string|int|float|bool',
            'second' => 'string|int|float|bool',
            'third' => 'string|int|float|bool',
        ],
        'messenger' => [
            'transports' => [[
                'dsn' => 'string|int|float|bool',
                'serializer' => 'string|int|float|bool', /* Default: null */
                'options' => ['mixed'],
            ]],
        ],
    ])] array $node_initial_values = [], 
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
    return static function (\Symfony\Config\Node_initial_valuesConfig $node_initial_valuesConfig, \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) use ($node_initial_values, $imports, $parameters, $services) {
        $node_initial_valuesConfig->configure($node_initial_values);

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
