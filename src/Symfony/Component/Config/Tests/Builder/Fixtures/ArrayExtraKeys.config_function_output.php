<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This function is automatically generated to help in creating a config.
 *
 * @param array{
 *     foo?: array{
 *         baz?: string|int|float|bool,
 *         qux?: string|int|float|bool,
 *     },
 *     bar?: array<array{
 *         corge?: string|int|float|bool,
 *         grault?: string|int|float|bool,
 *     }>,
 *     baz?: array<array-key, mixed>,
 * } $array_extra_keys
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
    // array_extra_keys:
    #[ArrayShape([
        'foo' => [
            'baz' => 'string|int|float|bool',
            'qux' => 'string|int|float|bool',
        ],
        'bar' => [[
            'corge' => 'string|int|float|bool',
            'grault' => 'string|int|float|bool',
        ]],
        'baz' => 'array<array-key, mixed>',
    ])] array $array_extra_keys = [], 
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
    return static function (\Symfony\Config\Array_extra_keysConfig $array_extra_keysConfig, \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) use ($array_extra_keys, $imports, $parameters, $services) {
        $array_extra_keysConfig->configure($array_extra_keys);

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
