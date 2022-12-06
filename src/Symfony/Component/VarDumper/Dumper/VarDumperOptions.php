<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Dumper;

use Traversable;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class VarDumperOptions
{
    public const FORMAT = '_format';
    public const TRACE = '_trace';
    public const TRACE_LIMIT = '_trace_limit';
    public const MAX_ITEMS = '_max_items';
    public const MIN_DEPTH = '_min_depth';
    public const MAX_STRING = '_max_string';
    public const MAX_DEPTH = '_max_depth';
    public const MAX_ITEMS_PER_DEPTH = '_max_items_per_depth';
    public const THEME = '_theme';
    public const FLAGS = '_flags';
    public const CHARSET = '_charset';

    private array $options = [
        self::FORMAT => null,
        self::TRACE => false,
        self::TRACE_LIMIT => 0,
        self::MAX_ITEMS => null,
        self::MIN_DEPTH => null,
        self::MAX_STRING => null,
        self::MAX_DEPTH => null,
        self::MAX_ITEMS_PER_DEPTH => null,
        self::THEME => HtmlDumperTheme::Dark,
        self::FLAGS => 0,
        self::CHARSET => null,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_replace($this->options, $options);
    }

    public function format(?string $format): static
    {
        $this->options[self::FORMAT] = $format;

        return $this;
    }

    public function trace(): static
    {
        $this->options[self::TRACE] = true;

        return $this;
    }

    public function traceLimit(int $limit): static
    {
        $this->options[self::TRACE_LIMIT] = max(0, $limit);

        return $this;
    }

    public function maxItems(int $maxItems): static
    {
        $this->options[self::MAX_ITEMS] = $maxItems;

        return $this;
    }

    public function minDepth(int $minDepth): static
    {
        $this->options[self::MIN_DEPTH] = $minDepth;

        return $this;
    }

    public function maxString(int $maxString): static
    {
        $this->options[self::MAX_STRING] = $maxString;

        return $this;
    }

    public function maxDepth(int $maxDepth): static
    {
        $this->options[self::MAX_DEPTH] = $maxDepth;

        return $this;
    }

    public function maxItemsPerDepth(int $maxItemsPerDepth): static
    {
        $this->options[self::MAX_ITEMS_PER_DEPTH] = $maxItemsPerDepth;

        return $this;
    }

    public function theme(?HtmlDumperTheme $theme): static
    {
        $this->options[self::THEME] = $theme ?? HtmlDumperTheme::Dark;

        return $this;
    }

    /**
     * Set flags manually. Valid flags are {@see AbstractDumper::DUMP_*} constants.
     */
    public function flags(int $flags): static
    {
        $this->options[self::FLAGS] = $flags;

        return $this;
    }

    /**
     * Display arrays with short form (omitting elements count and `array` prefix).
     */
    public function lightArray(): static
    {
        $this->options[self::FLAGS] |= AbstractDumper::DUMP_LIGHT_ARRAY;

        return $this;
    }

    /**
     * Display string lengths, just before its value.
     */
    public function stringLength(): static
    {
        $this->options[self::FLAGS] |= AbstractDumper::DUMP_STRING_LENGTH;

        return $this;
    }

    /**
     * Display a comma at the end of the line of an array element.
     */
    public function commaSeparator(): static
    {
        $this->options[self::FLAGS] |= AbstractDumper::DUMP_COMMA_SEPARATOR;

        return $this;
    }

    /**
     * Display a trailing comma after the last element of an array.
     */
    public function trailingComma(): static
    {
        $this->options[self::FLAGS] |= AbstractDumper::DUMP_TRAILING_COMMA;

        return $this;
    }

    public function charset(string $charset): static
    {
        $this->options[self::CHARSET] = $charset;

        return $this;
    }

    public function get(string $option): mixed
    {
        return $this->options[$option] ?? null;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function set(string $option, mixed $value): static
    {
        $this->options[$option] = $value;

        return $this;
    }
}
