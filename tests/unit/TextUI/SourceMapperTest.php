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
                    $fixtureDirectory . '/a/PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ]
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
                        ]
                    ),
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory . '/a',
                                '',
                                '.php'
                            ),
                        ]
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
                        ]
                    ),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ]
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
                    $fixtureDirectory . '/a/PrefixSuffix.php'     => true,
                    $fixtureDirectory . '/a/c/Prefix.php'         => true,
                    $fixtureDirectory . '/a/c/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/a/c/Suffix.php'         => true,
                    $fixtureDirectory . '/a/c/d/Prefix.php'       => true,
                    $fixtureDirectory . '/a/c/d/PrefixSuffix.php' => true,
                    $fixtureDirectory . '/a/c/d/Suffix.php'       => true,
                    $fixtureDirectory . '/b/PrefixSuffix.php'     => true,
                    $fixtureDirectory . '/b/e/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/b/e/g/PrefixSuffix.php' => true,
                    $fixtureDirectory . '/b/f/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/b/f/h/PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory,
                                '',
                                '.php'
                            ),
                        ]
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
                    $fixtureDirectory . '/a/c/Prefix.php'         => true,
                    $fixtureDirectory . '/a/c/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/a/c/Suffix.php'         => true,
                    $fixtureDirectory . '/a/c/d/Prefix.php'       => true,
                    $fixtureDirectory . '/a/c/d/PrefixSuffix.php' => true,
                    $fixtureDirectory . '/a/c/d/Suffix.php'       => true,
                    $fixtureDirectory . '/b/PrefixSuffix.php'     => true,
                    $fixtureDirectory . '/b/e/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/b/e/g/PrefixSuffix.php' => true,
                    $fixtureDirectory . '/b/f/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/b/f/h/PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory,
                                '',
                                '.php'
                            ),
                        ]
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File($fixtureDirectory . '/a/PrefixSuffix.php'),
                        ]
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
                    $fixtureDirectory . '/b/PrefixSuffix.php'     => true,
                    $fixtureDirectory . '/b/e/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/b/e/g/PrefixSuffix.php' => true,
                    $fixtureDirectory . '/b/f/PrefixSuffix.php'   => true,
                    $fixtureDirectory . '/b/f/h/PrefixSuffix.php' => true,
                ],
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory,
                                '',
                                '.php'
                            ),
                        ]
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                $fixtureDirectory . '/a',
                                '',
                                '.php'
                            ),
                        ]
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
        $this->assertSame($expected, (new SourceMapper)->map($source));
    }
}
