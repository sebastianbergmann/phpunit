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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestImpactAnalysis::class)]
#[UsesClass(AddedFilesAreReachedThroughChanges::class)]
#[UsesClass(FilterDirectory::class)]
#[UsesClass(FilterDirectoryCollection::class)]
#[UsesClass(FilterFile::class)]
#[UsesClass(FilterFileCollection::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/value-objects')]
final class TestImpactAnalysisTest extends TestCase
{
    public function testHasWatchedDirectories(): void
    {
        $directories = FilterDirectoryCollection::fromArray([new FilterDirectory('config', '', '.php')]);

        $this->assertSame($directories, new TestImpactAnalysis($directories, FilterFileCollection::fromArray([]), $this->noAddedFilesAreReachedThroughChanges())->watchedDirectories());
    }

    public function testHasWatchedFiles(): void
    {
        $files = FilterFileCollection::fromArray([new FilterFile('.env.testing')]);

        $this->assertSame($files, new TestImpactAnalysis(FilterDirectoryCollection::fromArray([]), $files, $this->noAddedFilesAreReachedThroughChanges())->watchedFiles());
    }

    public function testHasWhereAddedFilesAreReachedThroughChanges(): void
    {
        $addedFilesAreReachedThroughChanges = $this->noAddedFilesAreReachedThroughChanges();

        $this->assertSame($addedFilesAreReachedThroughChanges, new TestImpactAnalysis(FilterDirectoryCollection::fromArray([]), FilterFileCollection::fromArray([]), $addedFilesAreReachedThroughChanges)->addedFilesAreReachedThroughChanges());
    }

    private function noAddedFilesAreReachedThroughChanges(): AddedFilesAreReachedThroughChanges
    {
        return new AddedFilesAreReachedThroughChanges(
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
        );
    }
}
