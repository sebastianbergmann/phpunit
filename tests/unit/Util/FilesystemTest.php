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
use function dirname;
use function file_put_contents;
use function getcwd;
use function mkdir;
use function realpath;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filesystem::class)]
#[Small]
final class FilesystemTest extends TestCase
{
    public function testCanResolveStreamOrFile(): void
    {
        $this->assertSame('php://stdout', Filesystem::resolveStreamOrFile('php://stdout'));
        $this->assertSame('socket://hostname:port', Filesystem::resolveStreamOrFile('socket://hostname:port'));
        $this->assertSame(__FILE__, Filesystem::resolveStreamOrFile(__FILE__));
        $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'does-not-exist', Filesystem::resolveStreamOrFile(__DIR__ . '/does-not-exist'));
        $this->assertFalse(Filesystem::resolveStreamOrFile(__DIR__ . '/does-not-exist/does-not-exist'));
    }

    public function testCanDetectStreams(): void
    {
        $this->assertTrue(Filesystem::isStream('php://stdout'));
        $this->assertTrue(Filesystem::isStream('socket://localhost:1234'));
        $this->assertFalse(Filesystem::isStream(__FILE__));
        $this->assertFalse(Filesystem::isStream('relative/path'));
    }

    public function testCanDetectAbsolutePaths(): void
    {
        $this->assertTrue(Filesystem::isAbsolutePath('/absolute/path'));
        $this->assertFalse(Filesystem::isAbsolutePath('relative/path'));
        $this->assertFalse(Filesystem::isAbsolutePath(''));
    }

    public function testResolvesRelativePathAgainstWorkingDirectory(): void
    {
        $this->assertSame(
            $this->workingDirectory() . DIRECTORY_SEPARATOR . 'does-not-exist' . DIRECTORY_SEPARATOR . 'file.txt',
            Filesystem::resolvePath('does-not-exist/file.txt'),
        );

        $this->assertSame(
            $this->workingDirectory() . DIRECTORY_SEPARATOR . 'file.txt',
            Filesystem::resolvePath('./file.txt'),
        );
    }

    public function testResolvesCurrentAndParentDirectoryReferences(): void
    {
        $directory = realpath(__DIR__);

        $this->assertNotFalse($directory);

        $this->assertSame(
            $directory . DIRECTORY_SEPARATOR . 'file.txt',
            Filesystem::resolvePath(__DIR__ . '/./does-not-exist/../file.txt'),
        );

        $this->assertSame(
            dirname($directory) . DIRECTORY_SEPARATOR . 'file.txt',
            Filesystem::resolvePath(__DIR__ . '/../file.txt'),
        );

        $this->assertSame(
            $directory,
            Filesystem::resolvePath(__DIR__ . '/does-not-exist/..'),
        );
    }

    public function testDoesNotResolveAboveTheRootDirectory(): void
    {
        $this->assertSame(
            DIRECTORY_SEPARATOR . 'file.txt',
            Filesystem::resolvePath(DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'file.txt'),
        );

        $this->assertSame(DIRECTORY_SEPARATOR, Filesystem::resolvePath(DIRECTORY_SEPARATOR));
    }

    #[RequiresOperatingSystem('Linux|Darwin|BSD')]
    public function testResolvesSymbolicLinks(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-' . uniqid();

        mkdir($directory . '/inside', 0o777, true);
        mkdir($directory . '/outside');
        symlink($directory . '/outside', $directory . '/inside/link');
        file_put_contents($directory . '/outside/file.txt', '');
        symlink($directory . '/outside/file.txt', $directory . '/inside/file.txt');

        try {
            $resolvedDirectory = realpath($directory);

            $this->assertNotFalse($resolvedDirectory);

            $this->assertSame(
                $resolvedDirectory . '/outside/does-not-exist.txt',
                Filesystem::resolvePath($directory . '/inside/link/does-not-exist.txt'),
            );

            $this->assertSame(
                $resolvedDirectory . '/does-not-exist.txt',
                Filesystem::resolvePath($directory . '/inside/link/../does-not-exist.txt'),
            );

            $this->assertSame(
                $resolvedDirectory . '/outside/file.txt',
                Filesystem::resolvePath($directory . '/inside/file.txt'),
            );
        } finally {
            unlink($directory . '/inside/file.txt');
            unlink($directory . '/inside/link');
            unlink($directory . '/outside/file.txt');
            rmdir($directory . '/inside');
            rmdir($directory . '/outside');
            rmdir($directory);
        }
    }

    public function testCanTellWhetherPathIsInsideDirectory(): void
    {
        $directory = DIRECTORY_SEPARATOR . 'a';

        $this->assertTrue(Filesystem::isInside($directory, $directory));
        $this->assertTrue(Filesystem::isInside($directory . DIRECTORY_SEPARATOR . 'b', $directory));
        $this->assertTrue(Filesystem::isInside($directory . DIRECTORY_SEPARATOR . 'b', $directory . DIRECTORY_SEPARATOR));
        $this->assertFalse(Filesystem::isInside($directory . 'b', $directory));
        $this->assertFalse(Filesystem::isInside(DIRECTORY_SEPARATOR, $directory));
        $this->assertFalse(Filesystem::isInside($directory, $directory . DIRECTORY_SEPARATOR . 'b'));
    }

    /**
     * @return non-empty-string
     */
    private function workingDirectory(): string
    {
        $cwd = getcwd();

        $this->assertNotFalse($cwd);

        $cwd = realpath($cwd);

        $this->assertNotFalse($cwd);

        return $cwd;
    }
}
