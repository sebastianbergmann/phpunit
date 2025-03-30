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

use function json_encode;
use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(SourceFilter::class)]
#[Small]
final class SourceFilterTest extends AbstractSouceFilterTestCase
{
    public static function provider(): array
    {
        return [
            'file included using file' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => true,
                ],
                self::createSource(includeFiles: FileCollection::fromArray(
                    [
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
                    ],
                )),
            ],
            'file included using file, but excluded using directory' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeFiles: FileCollection::fromArray([
                        new File(self::fixturePath('/a/PrefixSuffix.php')),
                    ]),
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
            ],
            'file included using file, but excluded using file' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
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
            ],
            'file included using directory' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), '', '.php'),
                        ],
                    ),
                ),
            ],
            'file included using directory, but excluded using file' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), '', '.php'),
                        ],
                    ),
                    excludeFiles: FileCollection::fromArray(
                        [
                            new File(self::fixturePath('/a/PrefixSuffix.php')),
                        ],
                    ),
                ),
            ],
            'file included using directory, but excluded using directory' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), '', '.php'),
                        ],
                    ),
                    excludeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('/a'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'file included using directory, but wrong suffix' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), '', 'Foobar.php'),
                        ],
                    ),
                ),
            ],
            'file included using directory, but wrong prefix' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), 'WrongPrefix', '.php'),
                        ],
                    ),
                ),
            ],
            'file included using directory, but not excluded by suffix' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), '', '.php'),
                        ],
                    ),
                    excludeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), '', 'WrongSuffix.php'),
                        ],
                    ),
                ),
            ],
            'file included using directory, but not excluded by prefix' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), 'BadPrefix', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcard does not include files at same level' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath(), 'a/*', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcard with suffix does not match files' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a/Pre*'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcard with suffix matches directories' => [
                [
                    self::fixturePath('a/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a*'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcard with prefix matches directories' => [
                [
                    self::fixturePath('a/PrefixSuffix.php')   => true,
                    self::fixturePath('a/c/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('*a'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcards with prefix and suffix matches directories' => [
                [
                    self::fixturePath('a/PrefixSuffix.php')   => false,
                    self::fixturePath('a/c/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('*a/c*'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcard includes files' => [
                [
                    self::fixturePath('a/c/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a/*'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'directory wildcard includes files in sub-directories' => [
                [
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a/*'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'wildcard includes all files' => [
                [
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                    self::fixturePath('a/c/PrefixSuffix.php')   => true,
                    self::fixturePath('a/PrefixSuffix.php')     => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('*'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'globstar includes files at globstar\'s level' => [
                [
                    self::fixturePath('a/c/PrefixSuffix.php') => true,
                    self::fixturePath('a/PrefixSuffix.php')   => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a/**'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'globstar includes files at globstar\'s level (2)' => [
                [
                    self::fixturePath('a/c/PrefixSuffix.php') => true,
                    self::fixturePath('a/PrefixSuffix.php')   => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a/c/**'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'globstar includes all files' => [
                [
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                    self::fixturePath('a/c/PrefixSuffix.php')   => true,
                    self::fixturePath('a/PrefixSuffix.php')     => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                self::fixturePath('**'),
                                '',
                                '.php',
                            ),
                        ],
                    ),
                ),
            ],
            'globstar with any single char prefix includes sibling files' => [
                [
                    self::fixturePath('a/PrefixSuffix.php')     => false,
                    self::fixturePath('a/c/PrefixSuffix.php')   => true,
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                self::fixturePath('a/c/Z**'),
                                '',
                                '.php',
                            ),
                        ],
                    ),
                ),
            ],
            'globstar with any more than a single char prefix does not include sibling files' => [
                [
                    self::fixturePath('a/PrefixSuffix.php')     => false,
                    self::fixturePath('a/c/PrefixSuffix.php')   => false,
                    self::fixturePath('a/c/d/PrefixSuffix.php') => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                self::fixturePath('a/c/ZZ**'),
                                '',
                                '.php',
                            ),
                        ],
                    ),
                ),
            ],
            'globstar includes zero directories' => [
                [
                    self::fixturePath('a/PrefixSuffix.php')     => true,
                    self::fixturePath('a/c/PrefixSuffix.php')   => true,
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                self::fixturePath('**/a'),
                                '',
                                '.php',
                            ),
                        ],
                    ),
                ),
            ],
            'globstar with trailing directory' => [
                [
                    self::fixturePath('a/PrefixSuffix.php')     => false,
                    self::fixturePath('a/c/PrefixSuffix.php')   => false,
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                self::fixturePath('/a/**/d'),
                                '',
                                '.php',
                            ),
                        ],
                    ),
                ),
            ],
            'question mark' => [
                [
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                    self::fixturePath('a/c/PrefixSuffix.php')   => false,
                    self::fixturePath('a/PrefixSuffix.php')     => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('a/?/d'), '', '.php'),
                        ],
                    ),
                ),
            ],
            'multiple question marks' => [
                [
                    self::fixturePath('a/c/d/PrefixSuffix.php') => true,
                    self::fixturePath('b/e/PrefixSuffix.php')   => false,
                    self::fixturePath('a/c/PrefixSuffix.php')   => false,
                    self::fixturePath('a/PrefixSuffix.php')     => false,
                ],
                self::createSource(
                    includeDirectories: FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::fixturePath('?/?/d'), '', '.php'),
                        ],
                    ),
                ),
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(array $expectations, Source $source): void
    {
        foreach ($expectations as $file => $shouldInclude) {
            $this->assertFileExists($file);
            $this->assertSame(
                $shouldInclude,
                (new SourceFilter((new SourceMapper)->map($source)))->includes($file),
                sprintf('expected match to return %s for: %s', json_encode($shouldInclude), $file),
            );
        }
    }
}
