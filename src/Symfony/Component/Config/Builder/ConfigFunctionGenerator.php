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

use Symfony\Component\Config\Definition\ConfigurationInterface;

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
     * @param array<string, ConfigurationInterface> $configurations Configurations indexed by their alias.
     * @return \Closure
     */
    public function build(array $configurations): \Closure
    {
        $function = new FunctionBuilder('config', 'Symfony\Config');
        $path = $this->getFullPath($function);

        if (!is_file($path)) {
            $function->addUse('Jetbrains\PhpStorm\ArrayShape');
            $function->setReturnType('\Closure');
            $function->setBody($this->createBody(array_keys($configurations)));

            foreach ($configurations as $alias => $configuration) {
                $function->addParam('array', $alias);
                $function->addParamDefaultValue($alias, '[]');
                $function->addParamPhpDoc($alias, ArrayShapeGenerator::generate($configuration->getConfigTreeBuilder()->buildTree()));
                $function->addParamAttribute($alias, ArrayShapeGenerator::generate($configuration->getConfigTreeBuilder()->buildTree(), ArrayShapeGenerator::FORMAT_JETBRAINS_ARRAY_SHAPE));
            }

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
            };
        PHP, [
            'ARGUMENTS' => implode(", ", array_map(fn ($alias) => sprintf('\Symfony\Config\%sConfig $%sConfig', ucfirst($alias), $alias), $aliases)),
            'USE_VARS' => $aliases ? ' use ('.implode(', ', array_map(fn ($alias) => sprintf('$%s', $alias), $aliases)).')' : '',
            'CONFIGURE_EXTENSIONS' => implode("\n    ", array_map(fn ($alias) => sprintf('    $%sConfig->configure($%1$s);', $alias), $aliases)),
        ]);
    }
}
