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

use const PHP_OS_FAMILY;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(SourceMapper::class)]
#[Small]
final class SourceMapperTest extends AbstractSouceFilterTestCase
{
    public static function provider(): Generator
    {
        yield 'file included using file' => [
            [
                self::fixturePath('a/PrefixSuffix.php') => true,
            ],
            self::createSource(
                includeFiles: FileCollection::fromArray([
                    new File(self::fixturePath('a/PrefixSuffix.php')),
                ]),
            ),
        ];

        yield 'file included using file, but excluded using directory' => [
            [
            ],
            self::createSource(
                includeFiles: FileCollection::fromArray(
                    [
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
                    ],
                ),
                excludeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath('/a'),
                            '',
                            '.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'file included using file, but excluded using file' => [
            [
            ],
            self::createSource(
                includeFiles: FileCollection::fromArray(
                    [
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
                    ],
                ),
                excludeFiles: FileCollection::fromArray(
                    [
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
                    ],
                ),
            ),
        ];

        $fileHiddenOnUnix = self::fixturePath('a/c/.hidden/PrefixSuffix.php');

        $expectedFiles = [
            $fileHiddenOnUnix                                => true,
            self::fixturePath('a/PrefixSuffix.php')          => true,
            self::fixturePath('a/c/Prefix.php')              => true,
            self::fixturePath('a/c/PrefixSuffix.php')        => true,
            self::fixturePath('a/c/Suffix.php')              => true,
            self::fixturePath('a/c/d/Prefix.php')            => true,
            self::fixturePath('a/c/d/PrefixSuffix.php')      => true,
            self::fixturePath('a/c/d/Suffix.php')            => true,
            self::fixturePath('b/PrefixSuffix.php')          => true,
            self::fixturePath('b/e/PrefixSuffix.php')        => true,
            self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
            self::fixturePath('b/e/g/PrefixSuffix.php')      => true,
            self::fixturePath('b/f/PrefixSuffix.php')        => true,
            self::fixturePath('b/f/h/PrefixSuffix.php')      => true,
        ];

        if (PHP_OS_FAMILY !== 'Windows') {
            unset($expectedFiles[$fileHiddenOnUnix]);
        }

        yield 'file included using directory' => [
            $expectedFiles,
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            '.php',
                        ),
                    ],
                ),
            ),
        ];

        $expectedFiles = [
            $fileHiddenOnUnix                                => true,
            self::fixturePath('a/c/Prefix.php')              => true,
            self::fixturePath('a/c/PrefixSuffix.php')        => true,
            self::fixturePath('a/c/Suffix.php')              => true,
            self::fixturePath('a/c/d/Prefix.php')            => true,
            self::fixturePath('a/c/d/PrefixSuffix.php')      => true,
            self::fixturePath('a/c/d/Suffix.php')            => true,
            self::fixturePath('b/PrefixSuffix.php')          => true,
            self::fixturePath('b/e/PrefixSuffix.php')        => true,
            self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
            self::fixturePath('b/e/g/PrefixSuffix.php')      => true,
            self::fixturePath('b/f/PrefixSuffix.php')        => true,
            self::fixturePath('b/f/h/PrefixSuffix.php')      => true,
        ];

        if (PHP_OS_FAMILY !== 'Windows') {
            unset($expectedFiles[$fileHiddenOnUnix]);
        }

        yield 'file included using directory, but excluded using file' => [
            $expectedFiles,
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            '.php',
                        ),
                    ],
                ),
                excludeFiles: FileCollection::fromArray(
                    [
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
                    ],
                ),
            ),
        ];

        yield 'file included using directory, but excluded using directory' => [
            [
                self::fixturePath('b/PrefixSuffix.php')          => true,
                self::fixturePath('b/e/PrefixSuffix.php')        => true,
                self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
                self::fixturePath('b/e/g/PrefixSuffix.php')      => true,
                self::fixturePath('b/f/PrefixSuffix.php')        => true,
                self::fixturePath('b/f/h/PrefixSuffix.php')      => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            '.php',
                        ),
                    ],
                ),
                excludeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath('/a'),
                            '',
                            '.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files included using directory and prefix' => [
            [
                self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            path: self::fixturePath(),
                            prefix: 'PrefixExample',
                            suffix: '.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files included using directory and suffix' => [
            [
                self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            path: self::fixturePath(),
                            prefix: '',
                            suffix: 'ExampleSuffix.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files excluded using directory and prefix' => [
            [
                self::fixturePath('a/c/Suffix.php')   => true,
                self::fixturePath('a/c/d/Suffix.php') => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            '.php',
                        ),
                    ],
                ),
                excludeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            path: self::fixturePath(),
                            prefix: 'Prefix',
                            suffix: '.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files excluded using directory and suffix' => [
            [
                self::fixturePath('a/c/Prefix.php')   => true,
                self::fixturePath('a/c/d/Prefix.php') => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            '.php',
                        ),
                    ],
                ),
                excludeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            path: self::fixturePath(),
                            prefix: '',
                            suffix: 'Suffix.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files included using same directory and different suffixes' => [
            [
                self::fixturePath('a/c/Prefix.php')              => true,
                self::fixturePath('a/c/d/Prefix.php')            => true,
                self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            'ExampleSuffix.php',
                        ),
                        new FilterDirectory(
                            self::fixturePath(),
                            '',
                            'Prefix.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files included using same directory and different prefixes' => [
            [
                self::fixturePath('a/c/Suffix.php')              => true,
                self::fixturePath('a/c/d/Suffix.php')            => true,
                self::fixturePath('b/e/PrefixExampleSuffix.php') => true,
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            'Suffix',
                            '.php',
                        ),
                        new FilterDirectory(
                            self::fixturePath(),
                            'PrefixExample',
                            '.php',
                        ),
                    ],
                ),
            ),
        ];

        yield 'files excluded using same directory and different prefixes' => [
            [
            ],
            self::createSource(
                includeDirectories: FilterDirectoryCollection::fromArray([
                    new FilterDirectory(
                        self::fixturePath(),
                        '',
                        '.php',
                    ),
                ]),
                excludeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath(),
                            'Prefix',
                            '.php',
                        ),
                        new FilterDirectory(
                            self::fixturePath(),
                            'Suffix',
                            '.php',
                        ),
                    ],
                ),
            ),
        ];
    }

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(array $expected, Source $source): void
    {
        $this->assertEquals($expected, (new SourceMapper)->map($source));
    }
}
