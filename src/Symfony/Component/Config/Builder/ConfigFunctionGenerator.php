<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Builder;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Generate the "\Symfony\Config\config()" function.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
final class ConfigFunctionGenerator
{
    public function __construct(
        private string $outputDir,
    ) {
    }

    /**
     * @param array<string, ConfigurationInterface> $configurations Configurations indexed by their alias
     */
    public function build(array $configurations): \Closure
    {
        if (\function_exists('\Symfony\Config\config')) {
            return static function () {};
        }

        $function = new FunctionBuilder('config', 'Symfony\Config');
        $path = $this->getFullPath($function);

        if (!is_file($path)) {
            $function->addUse('Jetbrains\PhpStorm\ArrayShape');
            $function->setReturnType('\Closure');
            $function->setBody($this->createBody(array_keys($configurations)));

            foreach ($configurations as $alias => $configuration) {
                $function->addParam('array', $alias);
                $function->addParamDefaultValue($alias, '[]');

                $tree = $configuration->getConfigTreeBuilder()->buildTree();
                $function->addParamPhpDoc($alias, ArrayShapeGenerator::generate($tree, ArrayShapeGenerator::FORMAT_PHPDOC));
                $function->addParamAttribute($alias, ArrayShapeGenerator::generate($tree, ArrayShapeGenerator::FORMAT_JETBRAINS_ATTRIBUTE));
            }

            $importsTree = $this->createImportsTree();
            $function->addParam('array', 'imports');
            $function->addParamDefaultValue('imports', '[]');
            $function->addParamPhpDoc('imports', rtrim(ArrayShapeGenerator::generate($importsTree, ArrayShapeGenerator::FORMAT_PHPDOC), '>').'|non-empty-string>');
            $function->addParamAttribute('imports', ArrayShapeGenerator::generate($importsTree, ArrayShapeGenerator::FORMAT_JETBRAINS_ATTRIBUTE));

            $function->addParam('array', 'parameters');
            $function->addParamDefaultValue('parameters', '[]');
            $function->addParamPhpDoc('parameters', 'array<string, mixed>');

            $servicesTree = $this->createServicesTree();
            $function->addParam('array', 'services');
            $function->addParamDefaultValue('services', '[]');
            $function->addParamPhpDoc('services', ArrayShapeGenerator::generate($servicesTree, ArrayShapeGenerator::FORMAT_PHPDOC).'|array<string, class-string|null>');
            $function->addParamAttribute('services', ArrayShapeGenerator::generate($servicesTree, ArrayShapeGenerator::FORMAT_JETBRAINS_ATTRIBUTE));

            file_put_contents($path, $function->build());
        }

        return function () use ($path) {
            return require_once $path;
        };
    }

    private function getFullPath(FunctionBuilder $function): string
    {
        $directory = $this->outputDir.\DIRECTORY_SEPARATOR.$function->getDirectory();
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }

        return $directory.\DIRECTORY_SEPARATOR.$function->getFilename();
    }

    private function createBody(array $aliases): string
    {
        return strtr(<<<'PHP'
            return static function (ARGUMENTS)USE_VARS {
            CONFIGURE_EXTENSIONS

            CONFIGURE_IMPORTS
            CONFIGURE_PARAMETERS
            CONFIGURE_SERVICES
            };
        PHP, [
            'ARGUMENTS' => implode(', ', array_map(fn ($alias) => \sprintf('\Symfony\Config\%sConfig $%sConfig', ucfirst($alias), $alias), $aliases)).($aliases ? ', ' : '').'\\'.ContainerConfigurator::class.' $containerConfigurator',
            'USE_VARS' => ' use ('.implode(', ', array_map(fn ($alias) => \sprintf('$%s', $alias), $aliases)).($aliases ? ', ' : '').'$imports, $parameters, $services)',
            'CONFIGURE_EXTENSIONS' => implode("\n    ", array_map(fn ($alias) => \sprintf('    $%sConfig->configure($%1$s);', $alias), $aliases)),
            'CONFIGURE_IMPORTS' => <<<'PHP'
    foreach ($imports as $import) {
            if (\is_array($import)) {
                $containerConfigurator->import($import['resource'], $import['type'] ?? null, $import['ignoreErrors'] ?? false);
            } else {
                $containerConfigurator->import($import);
            }
        }
PHP,
            'CONFIGURE_PARAMETERS' => <<<'PHP'
    $parametersConfigurator = $containerConfigurator->parameters();
        foreach ($parameters as $key => $value) {
            $parametersConfigurator->set($key, $value);
        }
PHP,
            'CONFIGURE_SERVICES' => <<<'PHP'
    $servicesConfigurator = $containerConfigurator->services();
        foreach ($services as $id => $class) {
            if (\is_array($class)) {
                $servicesConfigurator->set($class['id'], $class['class'] ?? null);
            } else {
                $servicesConfigurator->set($id, $class);
            }
        }
PHP,
        ]);
    }

    private function createImportsTree(): NodeInterface
    {
        $builder = new TreeBuilder('imports');
        $builder->getRootNode()
            ->arrayPrototype()
                ->children()
                    ->stringNode('resource')->isRequired()->end()
                    ->scalarNode('type')->defaultNull()->end()
                    ->scalarNode('ignoreErrors')->defaultFalse()->end()
                ->end()
            ->end()
        ;

        return $builder->buildTree();
    }

    private function createServicesTree(): NodeInterface
    {
        $builder = new TreeBuilder('services');
        $builder->getRootNode()
            ->arrayPrototype()
                ->children()
                    ->stringNode('id')->isRequired()->end()
                    ->scalarNode('class')->defaultNull()->end()
                ->end()
            ->end()
        ;

        return $builder->buildTree();
    }
}
