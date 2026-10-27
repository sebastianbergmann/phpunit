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
use function bin2hex;
use function chmod;
use function fileperms;
use function is_dir;
use function is_file;
use function is_link;
use function mkdir;
use function posix_geteuid;
use function random_bytes;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function touch;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
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

    #[RequiresPhpExtension('posix')]
    public function testCreatesPrivateDirectory(): void
    {
        $this->assertTrue(Filesystem::createPrivateDirectory($this->directory, posix_geteuid()));
        $this->assertDirectoryExists($this->directory);
        $this->assertSame(0, fileperms($this->directory) & 0o077);
    }

    #[RequiresPhpExtension('posix')]
    public function testAcceptsExistingPrivateDirectory(): void
    {
        mkdir($this->directory, 0o700);

        $this->assertTrue(Filesystem::createPrivateDirectory($this->directory, posix_geteuid()));
    }

    #[RequiresPhpExtension('posix')]
    public function testRejectsExistingDirectoryThatIsOwnedByAnotherUser(): void
    {
        mkdir($this->directory, 0o700);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, posix_geteuid() + 1));
    }

    #[RequiresPhpExtension('posix')]
    #[TestWith([0o720])]
    #[TestWith([0o702])]
    public function testRejectsExistingDirectoryThatOtherUsersCanWriteTo(int $permissions): void
    {
        mkdir($this->directory, 0o700);
        chmod($this->directory, $permissions);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, posix_geteuid()));
    }

    #[RequiresPhpExtension('posix')]
    public function testRejectsSymbolicLinkToPrivateDirectory(): void
    {
        mkdir($this->target, 0o700);
        symlink($this->target, $this->directory);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, posix_geteuid()));
    }

    #[RequiresPhpExtension('posix')]
    public function testRejectsExistingFile(): void
    {
        touch($this->directory);

        $this->assertFalse(Filesystem::createPrivateDirectory($this->directory, posix_geteuid()));
    }
}
