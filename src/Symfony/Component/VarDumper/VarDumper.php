<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Debug\FileLinkFormatter;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\BacktraceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\RequestContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\VarDumperOptions;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\Factory\VarClonerFactory;

// Load the global dump() function
require_once __DIR__.'/Resources/functions/dump.php';

/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class VarDumper
{
    /**
     * @var callable|null
     */
    private static $handler;

    private static ?string $prevOptionsHash = null;

    private static bool $manualHandlerRegister = false;

    /**
     * @param string|null $label
     * @param VarDumperOptions|iterable $options
     */
    public static function dump(mixed $var/* , string $label = null, VarDumperOptions|iterable $options = [] */)
    {
        $label = 2 <= \func_num_args() ? func_get_arg(1) : null;
        $options = 3 <= \func_num_args() ? func_get_arg(2) : [];

        if (!$options instanceof VarDumperOptions) {
            $options = new VarDumperOptions($options);
        }

        if (self::requiresRegister($options)) {
            self::register($options);
        }

        return (self::$handler)($var, $label);
    }

    public static function setHandler(callable $callable = null): ?callable
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/var-dumper', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        $prevHandler = self::$handler;

        // Prevent replacing the handler with expected format as soon as the env var was set:
        if (isset($_SERVER['VAR_DUMPER_FORMAT'])) {
            return $prevHandler;
        }

        self::$handler = $callable;
        self::$manualHandlerRegister = true;

        return $prevHandler;
    }

    private static function register(VarDumperOptions $options): void
    {
        self::$prevOptionsHash = self::getOptionsHash($options);

        $cloner = VarClonerFactory::withOptions($options);
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

        $format = $_SERVER['VAR_DUMPER_FORMAT'] ?? $options->get(VarDumperOptions::FORMAT);
        $charset = $options->get(VarDumperOptions::CHARSET);
        $flags = $options->get(VarDumperOptions::FLAGS);

        switch (true) {
            case 'html' === $format:
                $dumper = new HtmlDumper(null, $charset, $flags);
                $dumper->setTheme($options->get(VarDumperOptions::THEME)->value);
                break;
            case 'cli' === $format:
                $dumper = new CliDumper(null, $charset, $flags);
                break;
            case 'server' === $format:
            case $format && 'tcp' === parse_url($format, \PHP_URL_SCHEME):
                $host = 'server' === $format ? $_SERVER['VAR_DUMPER_SERVER'] ?? '127.0.0.1:9912' : $format;
                $dumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) ?
                    new CliDumper(null, $charset, $flags) : new HtmlDumper(null, $charset, $flags);
                $dumper = new ServerDumper($host, $dumper, self::getDefaultContextProviders());
                break;
            default:
                $dumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) ?
                    new CliDumper(null, $charset, $flags) : new HtmlDumper(null, $charset, $flags);
        }

        if (!$dumper instanceof ServerDumper) {
            $dumper = new ContextualizedDumper($dumper, [
                new SourceContextProvider(),
                new BacktraceContextProvider($options->get(VarDumperOptions::TRACE_LIMIT)),
            ]);
        }

        self::$handler = function ($var, string $label = null) use ($cloner, $dumper, $options) {
            $var = self::cloneVarWithOptions($cloner, $var, $options);

            if (null !== $label) {
                $var = $var->withContext(['label' => $label]);
            }

            $var = $var->withContext($var->getContext() + ['options' => $options]);
            $dumper->dump($var);
        };
    }

    private static function getDefaultContextProviders(): array
    {
        $contextProviders = [];

        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) && class_exists(Request::class)) {
            $requestStack = new RequestStack();
            $requestStack->push(Request::createFromGlobals());
            $contextProviders['request'] = new RequestContextProvider($requestStack);
        }

        $fileLinkFormatter = class_exists(FileLinkFormatter::class) ? new FileLinkFormatter(null, $requestStack ?? null) : null;

        return $contextProviders + [
            'cli' => new CliContextProvider(),
            'source' => new SourceContextProvider(null, null, $fileLinkFormatter),
            'backtrace' => new BacktraceContextProvider(null, $fileLinkFormatter),
        ];
    }

    private static function cloneVarWithOptions(VarCloner $cloner, mixed $var, VarDumperOptions $options): Data
    {
        $var = $cloner->cloneVar($var);

        if (null !== $maxDepth = $options->get(VarDumperOptions::MAX_DEPTH)) {
            $var = $var->withMaxDepth($maxDepth);
        }

        if (null !== $maxItemsPerDepth = $options->get(VarDumperOptions::MAX_ITEMS_PER_DEPTH)) {
            $var = $var->withMaxItemsPerDepth($maxItemsPerDepth);
        }

        return $var;
    }

    private static function requiresRegister(VarDumperOptions $options): bool
    {
        return null === self::$handler || (self::getOptionsHash($options) !== self::$prevOptionsHash && !self::$manualHandlerRegister);
    }

    private static function getOptionsHash(VarDumperOptions $options): string
    {
        return md5(serialize($options->getOptions()));
    }
}
