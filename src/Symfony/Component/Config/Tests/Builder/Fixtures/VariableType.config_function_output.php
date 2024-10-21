<?php

namespace Symfony\Config;

use Jetbrains\PhpStorm\ArrayShape;

/**
 * This function is automatically generated to help in creating a config.
 *
 * @param array{
 *     any_value?: mixed,
 * } $variable_type
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
    // variable_type:
    #[ArrayShape([
        'any_value' => 'mixed',
    ])] array $variable_type = [], 
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
    return static function (\Symfony\Config\Variable_typeConfig $variable_typeConfig, \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) use ($variable_type, $imports, $parameters, $services) {
        $variable_typeConfig->configure($variable_type);

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
