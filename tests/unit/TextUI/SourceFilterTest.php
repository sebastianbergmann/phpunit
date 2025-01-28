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
use function realpath;
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
        $fixtureDirectory = realpath(__DIR__ . '/../../_files/source-filter');

        return [
            'file included using file' => [
                true,
                $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php',
                new Source(
                    null,
                    false,
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
                    [
                        'functions' => [],
                        'methods'   => [],
                    ],
                    false,
                    false,
                    false,
                ),
            ],
            'file included using file, but excluded using directory' => [
                false,
                $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php',
                new Source(
                    null,
                    false,
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
                    [
                        'functions' => [],
                        'methods'   => [],
                    ],
                    false,
                    false,
                    false,
                ),
            ],
            'file included using file, but excluded using file' => [
                false,
                $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php',
                new Source(
                    null,
                    false,
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
                    [
                        'functions' => [],
                        'methods'   => [],
                    ],
                    false,
                    false,
                    false,
                ),
            ],
            'file included using directory' => [
                true,
                $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php',
                new Source(
                    null,
                    false,
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
                    [
                        'functions' => [],
                        'methods'   => [],
                    ],
                    false,
                    false,
                    false,
                ),
            ],
            'file included using directory, but excluded using file' => [
                false,
                $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php',
                new Source(
                    null,
                    false,
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
                    [
                        'functions' => [],
                        'methods'   => [],
                    ],
                    false,
                    false,
                    false,
                ),
            ],
            'file included using directory, but excluded using directory' => [
                false,
                $fixtureDirectory . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'PrefixSuffix.php',
                new Source(
                    null,
                    false,
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
                    [
                        'functions' => [],
                        'methods'   => [],
                    ],
                    false,
                    false,
                    false,
                ),
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testDeterminesWhetherFileIsIncluded(bool $expected, string $file, Source $source): void
    {
        $this->assertSame($expected, (new SourceFilter((new SourceMapper)->map($source)))->includes($file));
    }
}
