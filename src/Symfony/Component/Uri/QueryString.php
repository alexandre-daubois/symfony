<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Uri;

/**
 * @experimental
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
final class QueryString implements \Stringable
{
    /**
     * @var array<string, string|string[]>
     */
    private array $parameters = [];

    /**
     * Parses a URI.
     *
     * Unlike `parse_str()`, this method does not overwrite duplicate keys but instead
     * returns an array of all values for each key:
     *
     * QueryString::parse('foo=1&foo=2&bar=3'); // stored as ['foo' => ['1', '2'], 'bar' => '3']
     *
     * `+` are supported in parameter keys and not replaced by an underscore:
     *
     * QueryString::parse('foo+bar=1'); // stored as ['foo bar' => '1']
     *
     * `.` and `_` are supported distinct in parameter keys:
     *
     * QueryString::parse('foo.bar=1'); // stored as ['foo.bar' => '1']
     * QueryString::parse('foo_bar=1'); // stored as ['foo_bar' => '1']
     */
    public static function parse(string $query): self
    {
        $parts = explode('&', $query);
        $queryString = new self();

        foreach ($parts as $part) {
            if ('' === $part) {
                continue;
            }

            $part = explode('=', $part, 2);
            $key = urldecode($part[0]);
            // keys without value will be stored as empty strings, as "parse_str()" does
            $value = isset($part[1]) ? urldecode($part[1]) : '';

            // take care of nested arrays
            if (preg_match_all('/\[(.*?)]/', $key, $matches)) {
                $nestedKeys = $matches[1];
                // nest the value inside the extracted keys
                $value = array_reduce(array_reverse($nestedKeys), static function ($carry, $key) {
                    return [$key => $carry];
                }, $value);

                $key = strstr($key, '[', true);
            }

            if ($queryString->has($key)) {
                $queryString->set($key, array_merge((array) $queryString->get($key), (array) $value));
            } else {
                $queryString->set($key, $value);
            }
        }

        return $queryString;
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->parameters);
    }

    /**
     * @return string|string[]|null
     */
    public function get(string $key): string|array|null
    {
        return $this->parameters[$key] ?? null;
    }

    public function set(string $key, array|string|null $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function remove(string $key): self
    {
        unset($this->parameters[$key]);

        return $this;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    public function __toString(): string
    {
        $parts = [];
        foreach ($this->parameters as $key => $values) {
            foreach ((array) $values as $value) {
                $parts[] = urlencode($key).'='.urlencode($value);
            }
        }

        return implode('&', $parts);
    }
}
