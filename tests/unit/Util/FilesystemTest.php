<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;
use function bin2hex;
use function chmod;
use function fileowner;
use function fileperms;
use function is_dir;
use function is_file;
use function is_link;
use function mkdir;
use function random_bytes;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function touch;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filesystem::class)]
#[Small]
final class FilesystemTest extends TestCase
{
    private string $temporaryDirectory;
    private string $directory;
    private string $target;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-filesystem-test-' . bin2hex(random_bytes(8));
        $this->directory          = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'directory';
        $this->target             = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'target';

        mkdir($this->temporaryDirectory);
    }

    protected function tearDown(): void
    {
        foreach ([$this->directory, $this->target] as $path) {
            if (is_link($path) || is_file($path)) {
                unlink($path);
            } elseif (is_dir($path)) {
                rmdir($path);
            }
        }

        rmdir($this->temporaryDirectory);
    }

    public function testCanResolveStreamOrFile(): void
    {
        $this->assertSame('php://stdout', Filesystem::resolveStreamOrFile('php://stdout'));
        $this->assertSame('socket://hostname:port', Filesystem::resolveStreamOrFile('socket://hostname:port'));
        $this->assertSame(__FILE__, Filesystem::resolveStreamOrFile(__FILE__));
        $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'does-not-exist', Filesystem::resolveStreamOrFile(__DIR__ . '/does-not-exist'));
        $this->assertFalse(Filesystem::resolveStreamOrFile(__DIR__ . '/does-not-exist/does-not-exist'));
    }

    public function testDeterminesIdOfUserThatOwnsFilesCreatedByCurrentProcess(): void
    {
        touch($this->directory);

        $this->assertSame(fileowner($this->directory), Filesystem::currentUserId());
    }

    public function testCreatesPrivateDirectory(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $this->assertTrue(Filesystem::createPrivateDirectory($this->directory, Filesystem::currentUserId()));
        $this->assertDirectoryExists($this->directory);
        $this->assertSame(0, fileperms($this->directory) & 0o077);
    }

    public function testAcceptsExistingPrivateDirectory(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        mkdir($this->directory, 0o700);

        $this->assertTrue(Filesystem::createPrivateDirectory($this->directory, Filesystem::currentUserId()));
    }

    public function testRejectsExistingDirectoryThatIsOwnedByAnotherUser(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        mkdir($this->directory, 0o700);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, Filesystem::currentUserId() + 1));
    }

    #[TestWith([0o720])]
    #[TestWith([0o702])]
    public function testRejectsExistingDirectoryThatOtherUsersCanWriteTo(int $permissions): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        mkdir($this->directory, 0o700);
        chmod($this->directory, $permissions);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, Filesystem::currentUserId()));
    }

    public function testRejectsSymbolicLinkToPrivateDirectory(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        mkdir($this->target, 0o700);
        symlink($this->target, $this->directory);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, Filesystem::currentUserId()));
    }

    public function testRejectsExistingFile(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        touch($this->directory);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, Filesystem::currentUserId()));
    }
}
