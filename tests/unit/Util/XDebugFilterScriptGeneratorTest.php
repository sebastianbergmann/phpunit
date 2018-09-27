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
     * @covers \PHPUnit\Util\XdebugFilterScriptGenerator::generate
     */
    public function testReturnsExpectedScript(): void
    {
        $filterConfiguration = [
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
        ];

        $writer = new XdebugFilterScriptGenerator;
        $actual = $writer->generate($filterConfiguration);

        $this->assertStringEqualsFile(__DIR__ . '/_files/expectedXDebugFilterScript.txt', $actual);
    }
}
