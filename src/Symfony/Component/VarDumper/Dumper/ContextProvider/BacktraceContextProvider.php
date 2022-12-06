<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Dumper\ContextProvider;

use Symfony\Component\HttpKernel\Debug\FileLinkFormatter;

/**
 * Provides the debug stacktrace of the VarDumper call.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
final class BacktraceContextProvider implements ContextProviderInterface
{
    private const BACKTRACE_CONTEXT_PROVIDER_DEPTH = 5;

    public function __construct(
        private readonly ?int $limit = null,
        private readonly ?FileLinkFormatter $fileLinkFormatter = null
    ) {
    }

    public function getContext(): ?array
    {
        $context = [];
        $trace = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT | \DEBUG_BACKTRACE_IGNORE_ARGS);

        for ($i = self::BACKTRACE_CONTEXT_PROVIDER_DEPTH; $i < count($trace); ++$i) {
            $file = $trace[$i]['file'];
            $line = $trace[$i]['line'];

            $name = str_replace('\\', '/', $file);
            $name = substr($name, strrpos($name, '/') + 1);

            if ($this->fileLinkFormatter) {
                $fileLink = $this->fileLinkFormatter->format($file, $line);
            }

            $context[] = ['name' => $name, 'file' => $file, 'line' => $line, 'file_link' => $fileLink ?? null];

            if ($this->limit === count($context)) {
                break;
            }
        }

        return $context;
    }
}
