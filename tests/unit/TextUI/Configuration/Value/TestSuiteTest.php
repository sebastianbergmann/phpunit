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
use PHPUnit\Framework\TestCase;

#[CoversClass(TestSuite::class)]
#[Small]
final class TestSuiteTest extends TestCase
{
    private readonly string $name;
    private readonly TestDirectoryCollection $directories;
    private readonly TestFileCollection $files;
    private readonly FileCollection $excludedFiles;
    private readonly TestSuite $fixture;

    protected function setUp(): void
    {
        $this->name          = 'name';
        $this->directories   = TestDirectoryCollection::fromArray([]);
        $this->files         = TestFileCollection::fromArray([]);
        $this->excludedFiles = FileCollection::fromArray([]);

        $this->fixture = new TestSuite(
            $this->name,
            $this->directories,
            $this->files,
            $this->excludedFiles,
        );
    }

    public function testHasName(): void
    {
        $this->assertSame($this->name, $this->fixture->name());
    }

    public function testDirectories(): void
    {
        $this->assertSame($this->directories, $this->fixture->directories());
    }

    public function testHasFiles(): void
    {
        $this->assertSame($this->files, $this->fixture->files());
    }

    public function testHasExcludedFiles(): void
    {
        $this->assertSame($this->excludedFiles, $this->fixture->exclude());
    }
}
