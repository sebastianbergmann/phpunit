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

use const DIRECTORY_SEPARATOR;
use function addslashes;
use function basename;
use function dirname;
use function sprintf;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\CodeCoverage;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\Directory;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\DirectoryCollection;
use PHPUnit\TextUI\XmlConfiguration\File;
use PHPUnit\TextUI\XmlConfiguration\FileCollection;

/**
 * @small
 * @covers \PHPUnit\Util\XdebugFilterScriptGenerator
 */
final class XDebugFilterScriptGeneratorTest extends TestCase
{
    public function testReturnsExpectedScript(): void
    {
        $expectedDirectory = sprintf(addslashes('%s' . DIRECTORY_SEPARATOR), __DIR__);
        $expected          = <<<EOF
<?php declare(strict_types=1);
if (!\\function_exists('xdebug_set_filter')) {
    return;
}

\\xdebug_set_filter(
    \\XDEBUG_FILTER_CODE_COVERAGE,
    \\XDEBUG_PATH_WHITELIST,
    [
        '{$expectedDirectory}',
        '{$expectedDirectory}',
        '{$expectedDirectory}',
        'src/foo.php',
        'src/bar.php'
    ]
);

EOF;

        $directoryPathThatDoesNotExist = sprintf('%s/path/that/does/not/exist', __DIR__);
        $this->assertDirectoryDoesNotExist($directoryPathThatDoesNotExist);

        $filterConfiguration = new CodeCoverage(
            null,
            DirectoryCollection::fromArray(
                [
                    new Directory(
                        __DIR__,
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                    new Directory(
                        sprintf('%s/', __DIR__),
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                    new Directory(
                        sprintf('%s/./%s', dirname(__DIR__), basename(__DIR__)),
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                    new Directory(
                        $directoryPathThatDoesNotExist,
                        '',
                        '.php',
                        'DEFAULT'
                    ),
                ]
            ),
            FileCollection::fromArray(
                [
                    new File('src/foo.php'),
                    new File('src/bar.php'),
                ]
            ),
            DirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            true,
            true,
            false,
            false,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );

        $writer = new XdebugFilterScriptGenerator;
        $actual = $writer->generate($filterConfiguration);

        $this->assertSame($expected, $actual);
    }
}
