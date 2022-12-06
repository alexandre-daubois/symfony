<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Dumper\VarDumperOptions;
use Symfony\Component\VarDumper\Factory\VarClonerFactory;
use Symfony\Component\VarDumper\Tests\Cloner\VarClonerTest;

class VarClonerFactoryTest extends TestCase
{
    /**
     * This is the same test as {@see VarClonerTest::testLimits()}, but the factory is setting the limits.
     */
    public function testCreateClonerWithOptions()
    {
        $options = new VarDumperOptions();
        $options
            ->minDepth(2)
            ->maxItems(5)
            ->maxString(20)
        ;

        $cloner = VarClonerFactory::withOptions($options);

        // Level 0:
        $data = [
            // Level 1:
            [
                // Level 2:
                [
                    // Level 3:
                    'Level 3 Item 0',
                    'Level 3 Item 1',
                    'Level 3 Item 2',
                    'Level 3 Item 3',
                ],
                [
                    999 => 'Level 3 Item 4',
                    'Level 3 Item 5',
                    'Level 3 Item 6',
                ],
                [
                    'Level 3 Item 7',
                ],
            ],
            [
                [
                    'Level 3 Item 8',
                ],
                'Level 2 Item 0',
            ],
            [
                'Level 2 Item 1',
            ],
            'Level 1 Item 0',
            [
                // Test setMaxString:
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'SHORT',
            ],
        ];

        $clone = $cloner->cloneVar($data);

        $expected = <<<EOTXT
Symfony\Component\VarDumper\Cloner\Data Object
(
    [data:Symfony\Component\VarDumper\Cloner\Data:private] => Array
        (
            [0] => Array
                (
                    [0] => Array
                        (
                            [2] => 1
                        )

                )

            [1] => Array
                (
                    [0] => Array
                        (
                            [2] => 2
                        )

                    [1] => Array
                        (
                            [2] => 3
                        )

                    [2] => Array
                        (
                            [2] => 4
                        )

                    [3] => Level 1 Item 0
                    [4] => Array
                        (
                            [2] => 5
                        )

                )

            [2] => Array
                (
                    [0] => Array
                        (
                            [2] => 6
                        )

                    [1] => Array
                        (
                            [0] => 2
                            [1] => 7
                        )

                    [2] => Array
                        (
                            [0] => 1
                            [2] => 0
                        )

                )

            [3] => Array
                (
                    [0] => Array
                        (
                            [0] => 1
                            [2] => 0
                        )

                    [1] => Level 2 Item 0
                )

            [4] => Array
                (
                    [0] => Level 2 Item 1
                )

            [5] => Array
                (
                    [0] => Symfony\Component\VarDumper\Cloner\Stub Object
                        (
                            [type] => 2
                            [class] => 2
                            [value] => ABCDEFGHIJKLMNOPQRST
                            [cut] => 6
                            [handle] => 0
                            [refCount] => 0
                            [position] => 0
                            [attr] => Array
                                (
                                )

                        )

                    [1] => SHORT
                )

            [6] => Array
                (
                    [0] => Level 3 Item 0
                    [1] => Level 3 Item 1
                    [2] => Level 3 Item 2
                    [3] => Level 3 Item 3
                )

            [7] => Array
                (
                    [999] => Level 3 Item 4
                )

        )

    [position:Symfony\Component\VarDumper\Cloner\Data:private] => 0
    [key:Symfony\Component\VarDumper\Cloner\Data:private] => 0
    [maxDepth:Symfony\Component\VarDumper\Cloner\Data:private] => 20
    [maxItemsPerDepth:Symfony\Component\VarDumper\Cloner\Data:private] => -1
    [useRefHandles:Symfony\Component\VarDumper\Cloner\Data:private] => -1
    [context:Symfony\Component\VarDumper\Cloner\Data:private] => Array
        (
        )

)

EOTXT;
        $this->assertStringMatchesFormat($expected, print_r($clone, true));
    }
}
