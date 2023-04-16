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
    public function testHasIncludeDirectories(): void
    {
        $includeDirectories = FilterDirectoryCollection::fromArray([]);

        $source = new Source(
            $includeDirectories,
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
        );

        $this->assertSame($excludeFiles, $source->excludeFiles());
    }
}
