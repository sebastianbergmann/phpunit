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

#[CoversClass(AddedFilesAreReachedThroughChanges::class)]
#[UsesClass(FilterDirectory::class)]
#[UsesClass(FilterDirectoryCollection::class)]
#[UsesClass(FilterFile::class)]
#[UsesClass(FilterFileCollection::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/value-objects')]
final class AddedFilesAreReachedThroughChangesTest extends TestCase
{
    public function testHasDirectoriesThatAreIncluded(): void
    {
        $directories = FilterDirectoryCollection::fromArray([new FilterDirectory('app', '', '.php')]);

        $this->assertSame($directories, new AddedFilesAreReachedThroughChanges($directories, FilterFileCollection::fromArray([]), FilterDirectoryCollection::fromArray([]), FilterFileCollection::fromArray([]))->includeDirectories());
    }

    public function testHasFilesThatAreIncluded(): void
    {
        $files = FilterFileCollection::fromArray([new FilterFile('app/helpers.php')]);

        $this->assertSame($files, new AddedFilesAreReachedThroughChanges(FilterDirectoryCollection::fromArray([]), $files, FilterDirectoryCollection::fromArray([]), FilterFileCollection::fromArray([]))->includeFiles());
    }

    public function testHasDirectoriesThatAreExcluded(): void
    {
        $directories = FilterDirectoryCollection::fromArray([new FilterDirectory('app/Listeners', '', '.php')]);

        $this->assertSame($directories, new AddedFilesAreReachedThroughChanges(FilterDirectoryCollection::fromArray([]), FilterFileCollection::fromArray([]), $directories, FilterFileCollection::fromArray([]))->excludeDirectories());
    }

    public function testHasFilesThatAreExcluded(): void
    {
        $files = FilterFileCollection::fromArray([new FilterFile('app/Kernel.php')]);

        $this->assertSame($files, new AddedFilesAreReachedThroughChanges(FilterDirectoryCollection::fromArray([]), FilterFileCollection::fromArray([]), FilterDirectoryCollection::fromArray([]), $files)->excludeFiles());
    }
}
