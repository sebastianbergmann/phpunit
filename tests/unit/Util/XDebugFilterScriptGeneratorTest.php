<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\TestCase;

class XDebugFilterScriptGeneratorTest extends TestCase
{
    /**
     * @covers \PHPUnit\Util\XDebugFilterScriptGenerator::generate
     *
     * @dataProvider scriptGeneratorTestDataProvider
     */
    public function testReturnsExpectedScript(array $filterConfiguration, array $resolvedWhitelist): void
    {
        $writer = new XDebugFilterScriptGenerator();
        $actual = $writer->generate($filterConfiguration, $resolvedWhitelist);

        $this->assertStringEqualsFile(__DIR__ . '/_files/expectedXDebugFilterScript.txt', $actual);
    }

    public function scriptGeneratorTestDataProvider(): array
    {
        return [
            [
                [
                    'include' => [
                        'directory' => [
                            [
                                'path'   => 'src/somePath',
                                'suffix' => '.php',
                                'prefix' => '',
                            ],
                        ],
                        'file' => [
                            'src/foo.php',
                            'src/bar.php',
                        ],
                    ],
                    'exclude' => [
                        'directory' => [],
                        'file'      => [],
                    ],
                ],
                [],
                __DIR__ . '/_files/expectedXDebugFilterScript.php',
            ],
            [
                [
                    'include' => [
                        'directory' => ['src/'],
                        'file'      => ['src/foo.php'],
                    ],
                    'exclude' => [
                        'directory' => [],
                        'file'      => ['src/baz.php'],
                    ],
                ],
                [
                    'src/somePath',
                    'src/foo.php',
                    'src/bar.php',
                ],
                __DIR__ . '/_files/expectedXDebugFilterScript.php',
            ],
        ];
    }
}
