<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonPath\Tokenizer;

use Symfony\Component\JsonPath\Exception\InvalidJsonPathException;
use Symfony\Component\JsonPath\JsonPath;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
final class JsonPathTokenizer
{
    private const RFC9535_WHITESPACE_CHARS = [' ', "\t", "\n", "\r"];
    private const BARE_LITERAL_REGEX = '(true|false|null|\d+(\.\d+)?([eE][+-]?\d+)?|\'[^\']*\'|"[^"]*")';

    /**
     * @return JsonPathToken[]
     */
    public static function tokenize(JsonPath $query): array
    {
        $tokens = [];
        $current = '';
        $inBracket = false;
        $bracketDepth = 0;
        $inFilter = false;
        $inQuote = false;
        $quoteChar = '';
        $filterParenthesisDepth = 0;
        $filterBracketDepth = 0;
        $hasContentAfterRoot = false;

        $chars = mb_str_split((string) $query);
        $length = \count($chars);

        if (0 === $length) {
            throw new InvalidJsonPathException('empty JSONPath expression.');
        }

        $i = self::skipWhitespace($chars, 0, $length);
        if ($i >= $length || '$' !== $chars[$i]) {
            throw new InvalidJsonPathException('expression must start with $.');
        }

        $rootIndex = $i;
        if ($rootIndex + 1 < $length) {
            $hasContentAfterRoot = true;
        }

        for ($i = 0; $i < $length; ++$i) {
            $char = $chars[$i];
            $position = $i;

            if (!$inQuote && !$inBracket && self::isWhitespace($char)) {
                if ('' !== $current) {
                    $tokens[] = new JsonPathToken(TokenType::Name, $current);
                    $current = '';
                }

                $nextNonWhitespaceIndex = self::skipWhitespace($chars, $i, $length);
                if ($nextNonWhitespaceIndex < $length && '[' !== $chars[$nextNonWhitespaceIndex] && '.' !== $chars[$nextNonWhitespaceIndex]) {
                    throw new InvalidJsonPathException('whitespace is not allowed in property names.', $i);
                }

                $i = $nextNonWhitespaceIndex - 1;

                continue;
            }

            if (('"' === $char || "'" === $char) && !$inQuote) {
                $inQuote = true;
                $quoteChar = $char;
                $current .= $char;
                continue;
            }

            if ($inQuote) {
                // Check for newlines in quoted strings within brackets (both literal and escaped)
                if ($inBracket && "\n" === $char) {
                    throw new InvalidJsonPathException('newlines are not allowed in quoted strings.', $position);
                }

                // Check for escaped newlines
                if ($inBracket && 'n' === $char && $i > 0 && '\\' === $chars[$i - 1]) {
                    throw new InvalidJsonPathException('escaped newlines are not allowed in quoted strings.', $position);
                }

                $current .= $char;
                if ($char === $quoteChar && (0 === $i || '\\' !== $chars[$i - 1])) {
                    $inQuote = false;
                }
                if ($i === $length - 1 && $inQuote) {
                    throw new InvalidJsonPathException('unclosed string literal.', $position);
                }
                continue;
            }

            if ('$' === $char && 0 === $i) {
                continue;
            }

            if ('[' === $char && !$inFilter) {
                if ('' !== $current) {
                    $tokens[] = new JsonPathToken(TokenType::Name, $current);
                    $current = '';
                }

                $inBracket = true;
                ++$bracketDepth;
                $i = self::skipWhitespace($chars, $i + 1, $length) - 1; // -1 because loop will increment

                continue;
            }

            if ('[' === $char && $inFilter) {
                // Inside filter expressions, brackets are part of the filter content
                ++$filterBracketDepth;
                $current .= $char;
                continue;
            }

            if (']' === $char) {
                if ($inFilter && $filterBracketDepth > 0) {
                    // Inside filter expressions, brackets are part of the filter content
                    --$filterBracketDepth;
                    $current .= $char;
                    continue;
                }

                if (--$bracketDepth < 0) {
                    throw new InvalidJsonPathException('unmatched closing bracket.', $position);
                }

                if (0 === $bracketDepth) {
                    if ('' === $current = trim($current)) {
                        throw new InvalidJsonPathException('empty brackets are not allowed.', $position);
                    }

                    // validate filter expressions
                    if (str_starts_with($current, '?')) {
                        // Check for unclosed parentheses in filter
                        if ($filterParenthesisDepth > 0) {
                            throw new InvalidJsonPathException('unclosed bracket.', $position);
                        }
                        self::validateFilterExpression($current, $position);
                    }

                    $tokens[] = new JsonPathToken(TokenType::Bracket, $current);
                    $current = '';
                    $inBracket = false;
                    $inFilter = false;
                    $filterParenthesisDepth = 0;
                    $filterBracketDepth = 0;
                    continue;
                }
            }

            if ('?' === $char && $inBracket && !$inFilter) {
                if ('' !== trim($current)) {
                    throw new InvalidJsonPathException('unexpected characters before filter expression.', $position);
                }

                $current = '?';
                $inFilter = true;
                $filterParenthesisDepth = 0;
                $filterBracketDepth = 0;

                continue;
            }

            if ($inFilter) {
                if ('(' === $char) {
                    // Check for any whitespace before parentheses in function calls
                    if (preg_match('/\w\s+$/', $current)) {
                        throw new InvalidJsonPathException('whitespace is not allowed between function name and parenthesis.', $position);
                    }
                    ++$filterParenthesisDepth;
                } elseif (')' === $char) {
                    if (--$filterParenthesisDepth < 0) {
                        throw new InvalidJsonPathException('unmatched closing parenthesis in filter.', $position);
                    }
                }
                $current .= $char;

                continue;
            }

            if ($inBracket && self::isWhitespace($char)) {
                $current .= $char;

                continue;
            }

            // recursive descent
            if ('.' === $char && !$inBracket) {
                if ('' !== $current) {
                    $tokens[] = new JsonPathToken(TokenType::Name, $current);
                    $current = '';
                }

                if ($i + 1 < $length && '.' === $chars[$i + 1]) {
                    // more than two consecutive dots?
                    if ($i + 2 < $length && '.' === $chars[$i + 2]) {
                        throw new InvalidJsonPathException('invalid character "." in property name.', $i + 2);
                    }

                    $tokens[] = new JsonPathToken(TokenType::Recursive, '..');
                    ++$i;
                } elseif ($i + 1 >= $length) {
                    throw new InvalidJsonPathException('path cannot end with a dot.', $position);
                }

                continue;
            }

            $current .= $char;
        }

        if ($inBracket) {
            throw new InvalidJsonPathException('unclosed bracket.', $length - 1);
        }

        if ($inQuote) {
            throw new InvalidJsonPathException('unclosed string literal.', $length - 1);
        }

        if ('' !== $current = trim($current)) {
            // final validation of the whole name
            if (!preg_match('/^(?:\*|[a-zA-Z_\x{0080}-\x{D7FF}\x{E000}-\x{10FFFF}][a-zA-Z0-9_\x{0080}-\x{D7FF}\x{E000}-\x{10FFFF}]*)$/u', $current)) {
                throw new InvalidJsonPathException(\sprintf('invalid character in property name "%s"', $current));
            }

            $tokens[] = new JsonPathToken(TokenType::Name, $current);
        }

        if ($hasContentAfterRoot && !$tokens) {
            throw new InvalidJsonPathException('invalid JSONPath expression.');
        }

        return $tokens;
    }

    private static function isWhitespace(string $char): bool
    {
        return \in_array($char, self::RFC9535_WHITESPACE_CHARS, true);
    }

    private static function skipWhitespace(array $chars, int $index, int $length): int
    {
        while ($index < $length && self::isWhitespace($chars[$index])) {
            ++$index;
        }

        return $index;
    }

    private static function validateFilterExpression(string $expr, int $position): void
    {
        // Check for bare literal expressions (literals without comparison operators)
        self::validateBareLiterals($expr, $position);

        // Remove the leading '?' if present for comparison parsing
        $filterExpr = ltrim($expr, '?');
        $filterExpr = trim($filterExpr);
        
        $comparisonOps = ['==', '!=', '>=', '<=', '>', '<'];
        foreach ($comparisonOps as $op) {
            if (str_contains($filterExpr, $op)) {
                [$left, $right] = array_map('trim', explode($op, $filterExpr, 2));

                // check if either side contains non-singular queries
                if (self::isNonSingularQuery($left) || self::isNonSingularQuery($right)) {
                    throw new InvalidJsonPathException('Non-singular query is not comparable.', $position);
                }

                break;
            }
        }

        // look for invalid number formats in filter expressions
        $operators = ['==', '!=', '>=', '<=', '>', '<', '&&', '||'];
        $tokens = [$filterExpr];

        foreach ($operators as $op) {
            $newTokens = [];
            foreach ($tokens as $token) {
                $newTokens = array_merge($newTokens, explode($op, $token));
            }

            $tokens = $newTokens;
        }

        foreach ($tokens as $token) {
            $token = trim($token);
            if ('' === $token) continue;

            if (str_starts_with($token, '@') || str_starts_with($token, '"') || str_starts_with($token, "'")) {
                continue;
            }

            if (in_array($token, ['true', 'false', 'null'], true)) {
                continue;
            }

            if (str_contains($token, '(') || str_contains($token, ')')) {
                continue;
            }

            // allow number-like tokens with dots: .1, -.1, 1., 1.2, 1.e1, etc.
            if (str_contains($token, '.') && !preg_match('/^[\d+\-.eE\s]*\./', $token)) {
                continue;
            }
            if (str_contains($token, '[') || str_contains($token, ']')) {
                continue;
            }

            if (str_contains($token, '$')) {
                continue;
            }

            if (preg_match('/^[\d+\-.eE\s]+$/', $token) && preg_match('/\d/', $token)) {
                // strict JSON number format validation
                if (!preg_match('/^-?(0|[1-9]\d*)(\.\d+)?([eE][+-]?\d+)?$/', $token)) {
                    throw new InvalidJsonPathException(\sprintf('Invalid number format "%s" in filter expression.', $token), $position);
                }
            }
        }
    }

    private static function validateBareLiterals(string $expr, int $position): void
    {
        $filterExpr = ltrim($expr, '?');
        $filterExpr = trim($filterExpr);

        if (preg_match('/\b(True|False|Null)\b/', $filterExpr)) {
            throw new InvalidJsonPathException('Incorrectly capitalized literal in filter expression.', $position);
        }

        // Check for bare function calls without comparison (only for functions that don't return boolean)
        if (preg_match('/^(length|count|value)\s*\([^)]*\)$/', $filterExpr)) {
            throw new InvalidJsonPathException('Function result must be compared.', $position);
        }

        // Check for function calls with invalid arguments
        if (preg_match('/\b(length|count|value)\s*\(([^)]*)\)/', $filterExpr, $matches)) {
            $functionName = $matches[1];
            $args = trim($matches[2]);
            if (empty($args)) {
                throw new InvalidJsonPathException('Function requires exactly one argument.', $position);
            }
            
            // Parse arguments respecting brackets and quotes
            $argParts = self::parseArguments($args);
            if (count($argParts) !== 1) {
                throw new InvalidJsonPathException('Function requires exactly one argument.', $position);
            }
            
            $arg = trim($argParts[0]);
            
            // count() function requires query arguments, not literals
            if ('count' === $functionName && preg_match('/^'.self::BARE_LITERAL_REGEX.'$/', $arg)) {
                throw new InvalidJsonPathException('count() function requires a query argument, not a literal.', $position);
            }
            
            // Check for non-singular queries like @.* (only for length function)
            if ('length' === $functionName && preg_match('/@\.\*/', $arg)) {
                throw new InvalidJsonPathException('Function argument must be a singular query.', $position);
            }
        }
        
        // Check for match/search functions with invalid arguments
        if (preg_match('/\b(match|search)\s*\(([^)]*)\)/', $filterExpr, $matches)) {
            $args = trim($matches[2]);
            if (empty($args)) {
                throw new InvalidJsonPathException('Function requires exactly two arguments.', $position);
            }
            
            // Parse arguments respecting brackets and quotes
            $argParts = self::parseArguments($args);
            if (count($argParts) !== 2) {
                throw new InvalidJsonPathException('Function requires exactly two arguments.', $position);
            }
            
            // Note: Arguments can be any type - let the function evaluation handle type mismatches
        }

        if (preg_match('/^'.self::BARE_LITERAL_REGEX.'$/', $filterExpr)) {
            throw new InvalidJsonPathException('Bare literal in filter expression - literals must be compared.', $position);
        }

        if (preg_match('/\b'.self::BARE_LITERAL_REGEX.'\s*(&&|\|\|)\s*'.self::BARE_LITERAL_REGEX.'\b/', $filterExpr)) {
            throw new InvalidJsonPathException('Bare literals in logical expression - literals must be compared.', $position);
        }

        // Check for function result compared to boolean literals (not allowed)
        if (preg_match('/\b(match|search|length|count|value)\s*\([^)]*\)\s*[=!]=\s*(true|false)\b/', $filterExpr) ||
            preg_match('/\b(true|false)\s*[=!]=\s*(match|search|length|count|value)\s*\([^)]*\)/', $filterExpr)) {
            throw new InvalidJsonPathException('Function result cannot be compared to boolean literal.', $position);
        }

        // check for mixed expressions where one side has comparison and other side is bare literal
        if (preg_match('/\b'.self::BARE_LITERAL_REGEX.'\s*(&&|\|\|)/', $filterExpr) ||
            preg_match('/(&&|\|\|)\s*'.self::BARE_LITERAL_REGEX.'\b/', $filterExpr)) {
            // check if the literal is not part of a comparison
            if (!preg_match('/(@[^=<>!]*|[^=<>!@]+)\s*[=<>!]+\s*'.self::BARE_LITERAL_REGEX.'/', $filterExpr) &&
                !preg_match('/'.self::BARE_LITERAL_REGEX.'\s*[=<>!]+\s*(@[^=<>!]*|[^=<>!@]+)/', $filterExpr)) {
                throw new InvalidJsonPathException('Bare literal in logical expression - literals must be compared.', $position);
            }
        }
    }

    private static function parseArguments(string $args): array
    {
        $parts = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;
        $bracketDepth = 0;

        for ($i = 0; $i < strlen($args); ++$i) {
            $char = $args[$i];

            if ('\\' === $char && $i + 1 < strlen($args)) {
                $current .= $char.$args[++$i];
                continue;
            }

            if ('"' === $char || "'" === $char) {
                if (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                }
            } elseif (!$inQuotes && '[' === $char) {
                ++$bracketDepth;
            } elseif (!$inQuotes && ']' === $char) {
                --$bracketDepth;
            } elseif (!$inQuotes && 0 === $bracketDepth && ',' === $char) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if ('' !== $current) {
            $parts[] = trim($current);
        }

        return $parts;
    }

    private static function isNonSingularQuery(string $query): bool
    {
        $query = trim($query);
        
        // Must start with @ to be a query
        if (!str_starts_with($query, '@')) {
            return false;
        }
        
        // Check for recursive descent patterns: @.. or @.foo..bar
        if (preg_match('/@\.\./', $query)) {
            return true;
        }
        
        // Check for wildcard patterns: @[*], @.*, @.foo[*], @.foo.*.bar
        if (preg_match('/@.*\[\*\]/', $query) || preg_match('/@.*\.\*/', $query)) {
            return true;
        }
        
        // Check for slice patterns: @[0:1], @[1:], @[:5], etc.
        if (preg_match('/@.*\[.*:.*\]/', $query)) {
            return true;
        }
        
        // Check for multiple selectors: @[1,2,3]
        if (preg_match('/@.*\[.*,.*\]/', $query)) {
            return true;
        }
        
        return false;
    }
}
