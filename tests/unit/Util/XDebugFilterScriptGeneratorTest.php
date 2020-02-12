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
use PHPUnit\TextUI\Configuration\Filter as FilterConfiguration;
use PHPUnit\TextUI\Configuration\FilterDirectory;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFile;
use PHPUnit\TextUI\Configuration\FilterFileCollection;

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
        $this->assertDirectoryDoesNotExist($directoryPathThatDoesNotExist);

        $filterConfiguration = new FilterConfiguration(
            FilterDirectoryCollection::fromArray(
                [
                    new FilterDirectory(
                        __DIR__,
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                    new FilterDirectory(
                        \sprintf('%s/', __DIR__),
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                    new FilterDirectory(
                        \sprintf('%s/./%s', \dirname(__DIR__), \basename(__DIR__)),
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                    new FilterDirectory(
                        $directoryPathThatDoesNotExist,
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                ]
            ),
            FilterFileCollection::fromArray(
                [
                    new FilterFile('src/foo.php'),
                    new FilterFile('src/bar.php'),
                ]
            ),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            true,
            true
        );

        $writer = new XdebugFilterScriptGenerator;
        $actual = $writer->generate($filterConfiguration);

        $this->assertSame($expected, $actual);
    }
}
