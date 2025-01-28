<?php

namespace Symfony\Component\JsonCrawler;

use Symfony\Component\JsonCrawler\Tokenizer\JsonPathToken;
use Symfony\Component\JsonCrawler\Tokenizer\JsonPathTokenizer;
use Symfony\Component\JsonCrawler\Tokenizer\TokenType;

final class JsonCrawler implements JsonCrawlerInterface
{
    private const FUNCTIONS = [
        'length' => true,
        'count' => true,
        'match' => true,
        'search' => true,
        'value' => true
    ];

    private mixed $data;

    public function __construct(string $json)
    {
        $this->data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    public function find(string|JsonPath $query): array
    {
        if (\is_string($query)) {
            $query = new JsonPath($query);
        }

        return $this->evaluate($query);
    }

    private function evaluate(JsonPath $query): array
    {
        $tokens = JsonPathTokenizer::tokenize($query);
        $current = [$this->data];

        foreach ($tokens as $token) {
            $next = [];
            foreach ($current as $value) {
                $result = $this->evaluateToken($token, $value);
                $next = array_merge($next, $result);
            }

            $current = $next;
        }

        return $current;
    }

    private function evaluateToken(JsonPathToken $token, mixed $value): array
    {
        return match ($token->type) {
            TokenType::Name => $this->evaluateName($token->value, $value),
            TokenType::Bracket => $this->evaluateBracket($token->value, $value),
            TokenType::Recursive => $this->evaluateRecursive($value),
        };
    }

    private function evaluateName(string $name, mixed $value): array
    {
        if ($value === null || !is_array($value)) {
            return [];
        }

        if ($name === '*') {
            return array_values($value);
        }

        return array_key_exists($name, $value) ? [$value[$name]] : [];
    }

    private function evaluateBracket(string $expr, mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        if ($expr === '*') {
            return array_values($value);
        }

        // single negative index
        if (preg_match('/^-\d+$/', $expr)) {
            if (!array_is_list($value)) {
                return [];
            }
            $index = count($value) + (int)$expr;
            return isset($value[$index]) ? [$value[$index]] : [];
        }

        // start and end index
        if (preg_match('/^-?\d+(?:\s*,\s*-?\d+)*$/', $expr)) {
            $indices = array_map('trim', explode(',', $expr));
            if (!array_is_list($value)) {
                return [];
            }
            $result = [];
            foreach ($indices as $index) {
                $index = (int)$index;
                if ($index < 0) {
                    $index = count($value) + $index;
                }
                if (isset($value[$index])) {
                    $result[] = $value[$index];
                }
            }
            return $result;
        }

        // start, end and step
        if (preg_match('/^(-?\d*):(-?\d*)(?::(-?\d+))?$/', $expr, $matches)) {
            if (!array_is_list($value)) {
                return [];
            }

            $length = count($value);
            $start = $matches[1] !== '' ? (int)$matches[1] : null;
            $end = $matches[2] !== '' ? (int)$matches[2] : null;
            $step = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : 1;

            if ($step === 0 || $start > $length) {
                return [];
            }

            if ($start === null) {
                $start = $step > 0 ? 0 : $length - 1;
            } else {
                if ($start < 0) {
                    $start = $length + $start;
                }
                $start = max(0, min($start, $length - 1));
            }

            if ($end === null) {
                $end = $step > 0 ? $length : -1;
            } else {
                if ($end < 0) {
                    $end = $length + $end;
                }
                if ($step > 0) {
                    $end = max(0, min($end, $length));
                } else {
                    $end = max(-1, min($end, $length - 1));
                }
            }

            $result = [];
            for ($i = $start; $step > 0 ? $i < $end : $i > $end; $i += $step) {
                if (isset($value[$i])) {
                    $result[] = $value[$i];
                }
            }
            return $result;
        }

        // filter expressions
        if (preg_match('/^\?(.*)$/', $expr, $matches)) {
            $filterExpr = $matches[1];

            // First, handle function calls without any parentheses
            if (preg_match('/^(\w+)\s*\([^()]*\)\s*([<>=!]+.*)?$/', $filterExpr)) {
                $filterExpr = "($filterExpr)";
            }

            // Next, check if we need to add outer parentheses
            if (!str_starts_with($filterExpr, '(')) {
                return [];
            }

            // Get the inner expression by removing exactly one set of outer parentheses
            $innerExpr = substr($filterExpr, 1);
            if (str_ends_with($innerExpr, ')')) {
                $innerExpr = substr($innerExpr, 0, -1);
            }

            return $this->evaluateFilter($innerExpr, $value);
        }

        // quoted strings for object keys
        if (preg_match('/^([\'"])(.*)\1$/', $expr, $matches)) {
            $key = $matches[2];
            return array_key_exists($key, $value) ? [$value[$key]] : [];
        }

        return [];
    }

    private function evaluateFilter(string $expr, mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->evaluateFilterExpression($expr, $item)) {
                $result[] = $item;
            }
        }
        return $result;
    }

    private function evaluateFilterExpression(string $expr, array $context): bool
    {
        $expr = trim($expr);

        if (str_contains($expr, '&&')) {
            $parts = array_map('trim', explode('&&', $expr));
            foreach ($parts as $part) {
                if (!$this->evaluateFilterExpression($part, $context)) {
                    return false;
                }
            }
            return true;
        }

        if (str_contains($expr, '||')) {
            $parts = array_map('trim', explode('||', $expr));
            $result = false;
            foreach ($parts as $part) {
                $result = $result || $this->evaluateFilterExpression($part, $context);
            }
            return $result;
        }

        $operators = ['!=', '==', '>=', '<=', '>', '<'];
        foreach ($operators as $op) {
            if (str_contains($expr, $op)) {
                [$left, $right] = array_map('trim', explode($op, $expr, 2));
                $leftValue = $this->evaluateScalar($left, $context);
                $rightValue = $this->evaluateScalar($right, $context);
                return $this->compare($leftValue, $rightValue, $op);
            }
        }

        if (str_starts_with($expr, '@.')) {
            $path = substr($expr, 2);
            return array_key_exists($path, $context);
        }

        // function calls
        if (preg_match('/^(\w+)\((.*)\)$/', $expr, $matches)) {
            $functionResult = $this->evaluateFunction($matches[1], $matches[2], $context);
            return \is_numeric($functionResult) ? $functionResult > 0 : (bool) $functionResult;
        }

        return false;
    }

    private function evaluateScalar(string $expr, array $context): mixed
    {
        if (\is_numeric($expr)) {
            return str_contains($expr, '.') ? (float) $expr : (int) $expr;
        }

        if ('true' === $expr) {
            return true;
        }

        if ('false' === $expr) {
            return false;
        }

        if ('null' === $expr) {
            return null;
        }

        // string literals
        if (preg_match('/^([\'"])(.*)\1$/', $expr, $matches)) {
            return $matches[2];
        }

        // current node references
        if (str_starts_with($expr, '@.')) {
            $path = substr($expr, 2);
            return $context[$path] ?? null;
        }

        // function calls
        if (preg_match('/^(\w+)\((.*)\)$/', $expr, $matches)) {
            return $this->evaluateFunction($matches[1], $matches[2], $context);
        }

        return null;
    }

    private function evaluateFunction(string $name, string $args, array $context): mixed
    {
        if (!isset(self::FUNCTIONS[$name])) {
            return null;
        }

        $args = array_map(
            fn($arg) => $this->evaluateScalar(trim($arg), $context),
            explode(',', $args)
        );

        $value = $args[0] ?? null;
        switch ($name) {
            case 'length':
                if (is_string($value)) {
                    return mb_strlen($value);
                }

                if (is_array($value)) {
                    return count($value);
                }

                return 0;

            case 'count':
                return is_array($value) ? count($value) : 0;

            case 'match':
                $pattern = $args[1] ?? '';
                if (!is_string($value) || !is_string($pattern)) {
                    return false;
                }

                return (bool)@preg_match("/$pattern/", $value);

            case 'search':
                $search = $args[1] ?? '';
                if (!is_string($value) || !is_string($search)) {
                    return false;
                }
                return str_contains($value, $search);

            case 'value':
                return $value;
        }

        return null;
    }

    private function evaluateRecursive(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [$value];
        foreach ($value as $item) {
            if (\is_array($item)) {
                $result = array_merge($result, $this->evaluateRecursive($item));
            }
        }

        return $result;
    }

    private function compare(mixed $left, mixed $right, string $operator): bool
    {
        return match($operator) {
            '==' => $left === $right,
            '!=' => $left !== $right,
            '>' => $left > $right,
            '>=' => $left >= $right,
            '<' => $left < $right,
            '<=' => $left <= $right,
            default => false,
        };
    }
}
