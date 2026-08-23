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
use function chmod;
use function file_put_contents;
use function is_readable;
use function mkdir;
use function octdec;
use function realpath;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use DirectoryIterator;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(SourceMapper::class)]
#[Small]
final class SourceMapperTest extends AbstractSourceFilterTestCase
{
    private ?string $directory = null;

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
                excludeDirectories: FilterDirectoryCollection::fromArray(
                    [
                        new FilterDirectory(
                            self::fixturePath('/a'),
                            '',
                            '.php',
                        ),
                    ],
                ),
                includeFiles: FileCollection::fromArray(
                    [
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
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

    protected function tearDown(): void
    {
        if ($this->directory === null) {
            return;
        }

        $this->removeDirectory($this->directory);

        $this->directory = null;
    }

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(array $expected, Source $source): void
    {
        $this->assertEquals($expected, (new SourceMapper)->map($source));
    }

    public function testDoesNotSearchExcludedDirectory(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('source-mapper_', true);

        mkdir($this->directory . DIRECTORY_SEPARATOR . 'excluded', octdec('777'), true);
        mkdir($this->directory . DIRECTORY_SEPARATOR . 'excluded' . DIRECTORY_SEPARATOR . 'unreadable', octdec('0'));
        file_put_contents($this->directory . DIRECTORY_SEPARATOR . 'Included.php', '<?php');

        if (is_readable($this->directory . DIRECTORY_SEPARATOR . 'excluded' . DIRECTORY_SEPARATOR . 'unreadable')) {
            $this->markTestSkipped('Cannot make a directory unreadable in this environment');
        }

        $source = self::createSource(
            includeDirectories: FilterDirectoryCollection::fromArray([
                new FilterDirectory($this->directory, '', '.php'),
            ]),
            excludeDirectories: FilterDirectoryCollection::fromArray([
                new FilterDirectory($this->directory . DIRECTORY_SEPARATOR . 'excluded', '', '.php'),
            ]),
        );

        $this->assertSame(
            [
                realpath($this->directory . DIRECTORY_SEPARATOR . 'Included.php') => true,
            ],
            (new SourceMapper)->map($source),
        );
    }

    public function testDoesNotExcludeDirectoryWhoseNameBeginsWithTheNameOfAnExcludedDirectory(): void
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('source-mapper_', true);

        mkdir($this->directory . DIRECTORY_SEPARATOR . 'tests', octdec('777'), true);
        mkdir($this->directory . DIRECTORY_SEPARATOR . 'tests-integration');
        file_put_contents($this->directory . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Excluded.php', '<?php');
        file_put_contents($this->directory . DIRECTORY_SEPARATOR . 'tests-integration' . DIRECTORY_SEPARATOR . 'Included.php', '<?php');

        $source = self::createSource(
            includeDirectories: FilterDirectoryCollection::fromArray([
                new FilterDirectory($this->directory, '', '.php'),
            ]),
            excludeDirectories: FilterDirectoryCollection::fromArray([
                new FilterDirectory($this->directory . DIRECTORY_SEPARATOR . 'tests', '', '.php'),
            ]),
        );

        $this->assertSame(
            [
                realpath($this->directory . DIRECTORY_SEPARATOR . 'tests-integration' . DIRECTORY_SEPARATOR . 'Included.php') => true,
            ],
            (new SourceMapper)->map($source),
        );
    }

    public function testIgnoresExcludedDirectoryThatDoesNotExist(): void
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('source-mapper_', true);

        mkdir($this->directory);
        file_put_contents($this->directory . DIRECTORY_SEPARATOR . 'Included.php', '<?php');

        $source = self::createSource(
            includeDirectories: FilterDirectoryCollection::fromArray([
                new FilterDirectory($this->directory, '', '.php'),
            ]),
            excludeDirectories: FilterDirectoryCollection::fromArray([
                new FilterDirectory($this->directory . DIRECTORY_SEPARATOR . 'does-not-exist', '', '.php'),
            ]),
        );

        $this->assertSame(
            [
                realpath($this->directory . DIRECTORY_SEPARATOR . 'Included.php') => true,
            ],
            (new SourceMapper)->map($source),
        );
    }

    private function removeDirectory(string $directory): void
    {
        chmod($directory, octdec('777'));

        foreach (new DirectoryIterator($directory) as $entry) {
            if ($entry->isDot()) {
                continue;
            }

            if ($entry->isDir()) {
                $this->removeDirectory($entry->getPathname());

                continue;
            }

            unlink($entry->getPathname());
        }

        rmdir($directory);
    }
}
