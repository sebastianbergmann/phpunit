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

/**
 * @small
 * @covers \PHPUnit\Util\XdebugFilterScriptGenerator
 */
final class XDebugFilterScriptGeneratorTest extends TestCase
{
    public function testReturnsExpectedScript(): void
    {
        $expectedDirectory = \sprintf('%s/', __DIR__);
        $expected          = <<<EOF
<?php declare(strict_types=1);
if (!\\function_exists('xdebug_set_filter')) {
    return;
}

\\xdebug_set_filter(
    \\XDEBUG_FILTER_CODE_COVERAGE,
    \\XDEBUG_PATH_WHITELIST,
    [
        '$expectedDirectory',
        '$expectedDirectory',
        '$expectedDirectory',
        'src/foo.php',
        'src/bar.php'
    ]
);

EOF;

        $directoryPathThatDoesNotExist = \sprintf('%s/path/that/does/not/exist', __DIR__);
        $this->assertDirectoryNotExists($directoryPathThatDoesNotExist);

        $filterConfiguration = [
            'include' => [
                'directory' => [
                    [
                        'path'   => __DIR__,
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                    [
                        'path'   => \sprintf('%s/', __DIR__),
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                    [
                        'path'   => \sprintf('%s/./%s', \dirname(__DIR__), \basename(__DIR__)),
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                    [
                        'path'   => $directoryPathThatDoesNotExist,
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

        $this->assertSame($expected, $actual);
    }
}
