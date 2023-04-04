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
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceFilter::class)]
#[Small]
final class SourceFilterTest extends TestCase
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

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(bool $expected, string $file, Source $source): void
    {
        $this->assertSame($expected, (new SourceFilter)->includes($source, $file));
    }
}
