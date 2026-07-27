<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use const DIRECTORY_SEPARATOR;
use function file_put_contents;
use function is_file;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class FileHasherTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->files = [];
    }

    public function testHashesTheContentsOfAFile(): void
    {
        $hasher = new FileHasher;

        $this->assertSame($hasher->hash(__FILE__), $hasher->hash(__FILE__));
    }

    #[TestDox('Hashes files with different contents differently')]
    public function testHashesFilesWithDifferentContentsDifferently(): void
    {
        $hasher = new FileHasher;

        $this->assertNotSame($hasher->hash(__FILE__), $hasher->hash($this->file('contents')));
    }

    #[TestDox('Hashes files with the same size but different contents differently')]
    public function testHashesFilesWithSameSizeButDifferentContentsDifferently(): void
    {
        $hasher = new FileHasher;

        $this->assertNotSame(
            $hasher->hash($this->file('a-group')),
            $hasher->hash($this->file('b-group')),
        );
    }

    public function testDoesNotHashFileThatDoesNotExist(): void
    {
        $hasher = new FileHasher;

        $this->assertNull($hasher->hash(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-does-not-exist-' . uniqid()));
    }

    #[TestDox('Remembers that a file does not exist')]
    public function testRemembersThatFileDoesNotExist(): void
    {
        $file   = $this->file('contents');
        $hasher = new FileHasher;

        $this->assertNotNull($hasher->hash($file));

        unlink($file);

        $this->assertNotNull($hasher->hash($file));
    }

    /**
     * @param non-empty-string $contents
     *
     * @return non-empty-string
     */
    private function file(string $contents): string
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-file-hasher-' . uniqid();

        file_put_contents($file, $contents);

        $this->files[] = $file;

        return $file;
    }
}
