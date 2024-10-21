<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This function is automatically generated to help in creating a config.
 *
 * @param array{
 *     boolean_node?: bool,
 *     enum_node?: "foo"|"bar"|"baz"|Symfony\Component\Config\Tests\Fixtures\TestEnum::Bar,
 *     float_node?: float,
 *     integer_node?: int<min, max>,
 *     scalar_node?: string|int|float|bool,
 *     scalar_node_with_default?: string|int|float|bool,
 * } $primitive_types
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
    // primitive_types:
    #[ArrayShape([
        'boolean_node' => 'bool',
        'enum_node' => '"foo"|"bar"|"baz"|Symfony\Component\Config\Tests\Fixtures\TestEnum::Bar',
        'float_node' => 'float',
        'integer_node' => 'int<min, max>',
        'scalar_node' => 'string|int|float|bool',
        'scalar_node_with_default' => 'string|int|float|bool', /* Default: true */
    ])] array $primitive_types = [], 
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
    return static function (\Symfony\Config\Primitive_typesConfig $primitive_typesConfig, \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) use ($primitive_types, $imports, $parameters, $services) {
        $primitive_typesConfig->configure($primitive_types);

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
