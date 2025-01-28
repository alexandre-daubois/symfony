<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonCrawler\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\JsonCrawler\JsonPath;
use Symfony\Component\JsonCrawler\Tokenizer\JsonPathTokenizer;
use Symfony\Component\JsonCrawler\Tokenizer\TokenType;

class JsonPathTokenizerTest extends TestCase
{
    /**
     * @dataProvider simplePathProvider
     */
    public function testSimplePath(string $path, array $expectedTokens): void
    {
        $jsonPath = new JsonPath($path);
        $tokens = JsonPathTokenizer::tokenize($jsonPath);

        $this->assertCount(count($expectedTokens), $tokens);
        foreach ($tokens as $i => $token) {
            $this->assertSame($expectedTokens[$i][0], $token->type);
            $this->assertSame($expectedTokens[$i][1], $token->value);
        }
    }

    public function simplePathProvider(): array
    {
        return [
            'root only' => [
                '$',
                []
            ],
            'simple property' => [
                '$.store',
                [[TokenType::Name, 'store']]
            ],
            'nested property' => [
                '$.store.book',
                [
                    [TokenType::Name, 'store'],
                    [TokenType::Name, 'book']
                ]
            ],
            'recursive descent' => [
                '$..book',
                [
                    [TokenType::Recursive, '..'],
                    [TokenType::Name, 'book']
                ]
            ]
        ];
    }

    /**
     * @dataProvider bracketNotationProvider
     */
    public function testBracketNotation(string $path, array $expectedTokens): void
    {
        $jsonPath = new JsonPath($path);
        $tokens = JsonPathTokenizer::tokenize($jsonPath);

        $this->assertCount(count($expectedTokens), $tokens);
        foreach ($tokens as $i => $token) {
            $this->assertSame($expectedTokens[$i][0], $token->type);
            $this->assertSame($expectedTokens[$i][1], $token->value);
        }
    }

    public function bracketNotationProvider(): array
    {
        return [
            'bracket with quotes' => [
                "$['store']",
                [[TokenType::Bracket, "'store'"]]
            ],
            'multiple brackets' => [
                "$['store']['book']",
                [
                    [TokenType::Bracket, "'store'"],
                    [TokenType::Bracket, "'book'"]
                ]
            ],
            'mixed notation' => [
                "$.store['book'][0]",
                [
                    [TokenType::Name, 'store'],
                    [TokenType::Bracket, "'book'"],
                    [TokenType::Bracket, '0']
                ]
            ]
        ];
    }

    /**
     * @dataProvider filterExpressionProvider
     */
    public function testFilterExpressions(string $path, array $expectedTokens): void
    {
        $jsonPath = new JsonPath($path);
        $tokens = JsonPathTokenizer::tokenize($jsonPath);

        $this->assertCount(count($expectedTokens), $tokens);
        foreach ($tokens as $i => $token) {
            $this->assertSame($expectedTokens[$i][0], $token->type);
            $this->assertSame($expectedTokens[$i][1], $token->value);
        }
    }

    public function filterExpressionProvider(): array
    {
        return [
            'simple filter' => [
                '$.store.book[?(@.price < 10)]',
                [
                    [TokenType::Name, 'store'],
                    [TokenType::Name, 'book'],
                    [TokenType::Bracket, '?(@.price < 10)']
                ]
            ],
            'nested filter' => [
                '$.store.book[?(@.price < 10 && @.category == "fiction")]',
                [
                    [TokenType::Name, 'store'],
                    [TokenType::Name, 'book'],
                    [TokenType::Bracket, '?(@.price < 10 && @.category == "fiction")']
                ]
            ],
            'filter with nested brackets' => [
                '$.store.book[?(@.authors[0] == "John Smith")]',
                [
                    [TokenType::Name, 'store'],
                    [TokenType::Name, 'book'],
                    [TokenType::Bracket, '?(@.authors[0] == "John Smith")']
                ]
            ]
        ];
    }

    /**
     * @dataProvider complexPathProvider
     */
    public function testComplexPaths(string $path, array $expectedTokens): void
    {
        $jsonPath = new JsonPath($path);
        $tokens = JsonPathTokenizer::tokenize($jsonPath);

        $this->assertCount(count($expectedTokens), $tokens);
        foreach ($tokens as $i => $token) {
            $this->assertSame($expectedTokens[$i][0], $token->type);
            $this->assertSame($expectedTokens[$i][1], $token->value);
        }
    }

    public function complexPathProvider(): array
    {
        return [
            'mixed with recursive' => [
                '$..book[?(@.price < 10)].title',
                [
                    [TokenType::Recursive, '..'],
                    [TokenType::Name, 'book'],
                    [TokenType::Bracket, '?(@.price < 10)'],
                    [TokenType::Name, 'title']
                ]
            ],
            'multiple filters' => [
                '$.store.book[?(@.price < 10)][?(@.category == "fiction")]',
                [
                    [TokenType::Name, 'store'],
                    [TokenType::Name, 'book'],
                    [TokenType::Bracket, '?(@.price < 10)'],
                    [TokenType::Bracket, '?(@.category == "fiction")']
                ]
            ],
            'everything combined' => [
                '$..store[*].book[?(@.price < 10)].author["lastName"]',
                [
                    [TokenType::Recursive, '..'],
                    [TokenType::Name, 'store'],
                    [TokenType::Bracket, '*'],
                    [TokenType::Name, 'book'],
                    [TokenType::Bracket, '?(@.price < 10)'],
                    [TokenType::Name, 'author'],
                    [TokenType::Bracket, '"lastName"']
                ]
            ]
        ];
    }

    public function testConsecutiveDots(): void
    {
        $jsonPath = new JsonPath('$...book');
        $tokens = JsonPathTokenizer::tokenize($jsonPath);

        $this->assertCount(2, $tokens);
        $this->assertSame(TokenType::Recursive, $tokens[0]->type);
        $this->assertSame('..', $tokens[0]->value);
        $this->assertSame(TokenType::Name, $tokens[1]->type);
        $this->assertSame('book', $tokens[1]->value);
    }
}
