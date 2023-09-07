<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;
use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceMapper::class)]
#[Small]
final class SourceMapperTest extends TestCase
{
    public static function provider(): array
    {
        $fixtureDirectory = realpath(__DIR__ . '/../../_files/source-filter');

        return [
            'file included using file' => [
                [
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ],
                    ),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray([]),
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
            ],
            'file included using file, but excluded using directory' => [
                [
                ],
                new Source(
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ],
                    ),
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory . '/a',
                                '',
                                '.php',
                            ),
                        ],
                    ),
                    FileCollection::fromArray([]),
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
            ],
            'file included using file, but excluded using file' => [
                [
                ],
                new Source(
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ],
                    ),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ],
                    ),
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
            ],
            'file included using directory' => [
                [
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                                                         => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'Prefix.php'                                   => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'Suffix.php'                                   => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR . 'Prefix.php'       => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR . 'Suffix.php'       => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                                                         => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'e' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'e' . DIRECTORY_SEPARATOR . 'g' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . 'h' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory,
                                '',
                                '.php',
                            ),
                        ],
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray([]),
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
            ],
            'file included using directory, but excluded using file' => [
                [
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'Prefix.php'                                   => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'Suffix.php'                                   => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR . 'Prefix.php'       => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR . 'Suffix.php'       => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                                                         => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'e' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'e' . DIRECTORY_SEPARATOR . 'g' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . 'h' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory,
                                '',
                                '.php',
                            ),
                        ],
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ],
                    ),
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
            ],
            'file included using directory, but excluded using directory' => [
                [
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                                                         => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'e' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'e' . DIRECTORY_SEPARATOR . 'g' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php'                             => true,
                    $fixtureDirectory . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . 'h' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory,
                                '',
                                '.php',
                            ),
                        ],
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory . '/a',
                                '',
                                '.php',
                            ),
                        ],
                    ),
                    FileCollection::fromArray([]),
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(array $expected, Source $source): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestIncomplete('https://github.com/sebastianbergmann/phpunit/issues/5503');
        }

        $this->assertSame($expected, (new SourceMapper)->map($source));
    }
}
