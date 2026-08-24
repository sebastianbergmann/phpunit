<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use const DIRECTORY_SEPARATOR;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestIndex\FileHasher;

#[CoversClass(PathHasher::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class PathHasherTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            $this->deleteDirectory($directory);
        }

        $this->directories = [];
    }

    public function testHashesTheContentsOfAFile(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeFile($directory, 'a.txt', 'first');

        $hasher = new PathHasher;
        $before = $hasher->hash($file);

        $this->assertIsString($before);

        $this->writeFile($directory, 'a.txt', 'second');

        $this->assertNotSame($before, (new PathHasher)->hash($file));
    }

    public function testHashesNothingThatIsNeitherAFileNorADirectory(): void
    {
        $directory = $this->temporaryDirectory();

        $this->assertNull((new PathHasher)->hash($directory . DIRECTORY_SEPARATOR . 'does-not-exist'));
    }

    public function testHashesADirectoryFromTheFilesInIt(): void
    {
        $directory = $this->temporaryDirectory();
        $this->writeFile($directory, 'a.txt', 'first');

        $before = (new PathHasher)->hash($directory);

        $this->assertIsString($before);

        $this->writeFile($directory, 'a.txt', 'second');

        $this->assertNotSame($before, (new PathHasher)->hash($directory));
    }

    public function testHashesADirectoryDifferentlyWhenAFileIsAddedToIt(): void
    {
        $directory = $this->temporaryDirectory();
        $this->writeFile($directory, 'a.txt', 'first');

        $before = (new PathHasher)->hash($directory);

        $this->writeFile($directory, 'b.txt', 'first');

        $this->assertNotSame($before, (new PathHasher)->hash($directory));
    }

    public function testHashesADirectoryTheSameWhenNothingInItChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $this->writeFile($directory, 'a.txt', 'first');

        $this->assertSame((new PathHasher)->hash($directory), (new PathHasher)->hash($directory));
    }

    public function testHashesTheFilesInSubdirectoriesOfADirectory(): void
    {
        $directory = $this->temporaryDirectory();

        mkdir($directory . DIRECTORY_SEPARATOR . 'nested');

        $this->writeFile($directory . DIRECTORY_SEPARATOR . 'nested', 'a.txt', 'first');

        $before = (new PathHasher)->hash($directory);

        $this->writeFile($directory . DIRECTORY_SEPARATOR . 'nested', 'a.txt', 'second');

        $this->assertNotSame($before, (new PathHasher)->hash($directory));
    }

    public function testHashesADirectoryOnlyOnce(): void
    {
        $directory = $this->temporaryDirectory();
        $this->writeFile($directory, 'a.txt', 'first');

        $hasher = new PathHasher;
        $before = $hasher->hash($directory);

        $this->writeFile($directory, 'a.txt', 'second');

        $this->assertSame($before, $hasher->hash($directory));
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-path-hasher-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @return non-empty-string
     */
    private function writeFile(string $directory, string $name, string $contents): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name;

        file_put_contents($file, $contents);

        return $file;
    }

    private function deleteDirectory(string $directory): void
    {
        $entries = scandir($directory);

        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $path = $directory . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($path)) {
                    $this->deleteDirectory($path);

                    continue;
                }

                unlink($path);
            }
        }

        rmdir($directory);
    }
}
