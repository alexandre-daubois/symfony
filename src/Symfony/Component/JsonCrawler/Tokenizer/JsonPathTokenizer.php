<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonCrawler\Tokenizer;

use Symfony\Component\JsonCrawler\JsonPath;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
final class JsonPathTokenizer
{
    /**
     * @return list<JsonPathToken>
     */
    public static function tokenize(JsonPath $query): array
    {
        $tokens = [];
        $current = '';
        $inBracket = false;
        $bracketDepth = 0;
        $inFilter = false;

        $chars = str_split((string) $query);
        for ($i = 0; $i < count($chars); $i++) {
            $char = $chars[$i];

            if ($char === '$' && $i === 0) {
                continue;
            }

            if ($char === '[' && !$inFilter) {
                if ($current !== '') {
                    $tokens[] = new JsonPathToken(TokenType::Name, $current);
                    $current = '';
                }
                $inBracket = true;
                $bracketDepth++;
                continue;
            }

            if ($char === ']' && !$inFilter) {
                $bracketDepth--;
                if ($bracketDepth === 0) {
                    $tokens[] = new JsonPathToken(TokenType::Bracket, $current);
                    $current = '';
                    $inBracket = false;
                    continue;
                }
            }

            // filter expressions
            if ($char === '?' && $inBracket) {
                $inFilter = true;
            }

            if ($inFilter) {
                if ($char === '(') {
                    $bracketDepth++;
                } elseif ($char === ')') {
                    if (--$bracketDepth === 1) {
                        $inFilter = false;
                    }
                }
            }

            // recursive descent
            if ($char === '.' && !$inBracket) {
                if ($current !== '') {
                    $tokens[] = new JsonPathToken(TokenType::Name, $current);
                    $current = '';
                }
                if ($i + 1 < count($chars) && $chars[$i + 1] === '.') {
                    $tokens[] = new JsonPathToken(TokenType::Recursive, '..');
                    $i++;
                }
                continue;
            }

            $current .= $char;
        }

        if ('' !== $current) {
            $tokens[] = new JsonPathToken($inBracket ? TokenType::Bracket : TokenType::Name, $current);
        }

        return $tokens;
    }
}
