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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Source::class)]
#[UsesClass(FilterDirectory::class)]
#[UsesClass(FilterDirectoryCollection::class)]
#[UsesClass(FilterDirectoryCollectionIterator::class)]
#[UsesClass(File::class)]
#[UsesClass(FileCollection::class)]
#[UsesClass(FileCollectionIterator::class)]
#[Small]
final class SourceTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'file included using file' => [
                true,
                '/path/to/source.php',
                new Source(
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File('/path/to/source.php'),
                        ]
                    ),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray([]),
                ),
            ],
            'file included using file, but also excluded using file' => [
                false,
                '/path/to/source.php',
                new Source(
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File('/path/to/source.php'),
                        ]
                    ),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File('/path/to/source.php'),
                        ]
                    ),
                ),
            ],
            'file included using directory' => [
                true,
                '/path/to/source.php',
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                '/path',
                                '',
                                '.php'
                            ),
                        ]
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray([]),
                ),
            ],
            'file included using directory, but excluded using directory' => [
                false,
                '/path/to/source.php',
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                '/path',
                                '',
                                '.php'
                            ),
                        ]
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                '/path/to',
                                '',
                                '.php'
                            ),
                        ]
                    ),
                    FileCollection::fromArray([]),
                ),
            ],
            'file included using directory, but excluded using file' => [
                false,
                '/path/to/source.php',
                new Source(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(
                                '/path',
                                '',
                                '.php'
                            ),
                        ]
                    ),
                    FileCollection::fromArray([]),
                    FilterDirectoryCollection::fromArray([]),
                    FileCollection::fromArray(
                        [
                            new File('/path/to/source.php'),
                        ]
                    ),
                ),
            ],
        ];
    }

    public function testHasIncludeDirectories(): void
    {
        $includeDirectories = FilterDirectoryCollection::fromArray([]);

        $source = new Source(
            $includeDirectories,
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
        );

        $this->assertSame($includeDirectories, $source->includeDirectories());
    }

    public function testHasIncludeFiles(): void
    {
        $includeFiles = FileCollection::fromArray([]);

        $source = new Source(
            FilterDirectoryCollection::fromArray([]),
            $includeFiles,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
        );

        $this->assertSame($includeFiles, $source->includeFiles());
    }

    public function testHasExcludeDirectories(): void
    {
        $excludeDirectories = FilterDirectoryCollection::fromArray([]);

        $source = new Source(
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            $excludeDirectories,
            FileCollection::fromArray([]),
        );

        $this->assertSame($excludeDirectories, $source->excludeDirectories());
    }

    public function testHasExcludeFiles(): void
    {
        $excludeFiles = FileCollection::fromArray([]);

        $source = new Source(
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            $excludeFiles,
        );

        $this->assertSame($excludeFiles, $source->excludeFiles());
    }

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(bool $expected, string $file, Source $source): void
    {
        $this->assertSame($expected, $source->includes($file));
    }
}
