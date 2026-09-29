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

use const PHP_OS_FAMILY;
use function getcwd;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaseDirectory::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class BaseDirectoryTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideRelativePaths(): array
    {
        return [
            'file in the directory'                     => ['/project/src/Foo.php', 'src/Foo.php'],
            'the directory itself'                      => ['/project', '.'],
            'file next to the directory'                => ['/shared/Foo.php', '../shared/Foo.php'],
            'file in a directory next to the directory' => ['/shared/src/Foo.php', '../shared/src/Foo.php'],
            'file with a . in its path'                 => ['/project/./src/Foo.php', 'src/Foo.php'],
            'file with a .. in its path'                => ['/project/tests/../src/Foo.php', 'src/Foo.php'],
            'file with a .. that leaves the directory'  => ['/project/../shared/Foo.php', '../shared/Foo.php'],
            'file with a doubled separator'             => ['/project//src/Foo.php', 'src/Foo.php'],
        ];
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function provideAbsolutePaths(): array
    {
        return [
            'file in the directory'                   => ['src/Foo.php', '/project/src/Foo.php'],
            'the directory itself'                    => ['.', '/project'],
            'file next to the directory'              => ['../shared/Foo.php', '/shared/Foo.php'],
            'file with a .. in its path'              => ['tests/../src/Foo.php', '/project/src/Foo.php'],
            'file that climbs past the root'          => ['../../../Foo.php', '/Foo.php'],
            'file that is named by its absolute path' => ['/elsewhere/Foo.php', '/elsewhere/Foo.php'],
        ];
    }

    protected function setUp(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('The paths in this test are paths on a POSIX file system');
        }
    }

    public function testCanBeCreatedFromAPath(): void
    {
        $this->assertSame('/project', BaseDirectory::from('/project')->path());
    }

    public function testCanBeCreatedFromTheWorkingDirectory(): void
    {
        $this->assertSame(getcwd(), BaseDirectory::fromWorkingDirectory()->path());
    }

    /**
     * @param non-empty-string $path
     * @param non-empty-string $relativePath
     */
    #[DataProvider('provideRelativePaths')]
    public function testNamesAPathRelativeToIt(string $path, string $relativePath): void
    {
        $this->assertSame($relativePath, BaseDirectory::from('/project')->relativePathOf($path));
    }

    /**
     * @param non-empty-string $relativePath
     * @param non-empty-string $path
     */
    #[DataProvider('provideAbsolutePaths')]
    public function testNamesAPathThatIsRelativeToItByItsAbsolutePath(string $relativePath, string $path): void
    {
        $this->assertSame($path, BaseDirectory::from('/project')->absolutePathOf($relativePath));
    }

    public function testNamesAPathRelativeToTheRootOfTheFileSystem(): void
    {
        $this->assertSame('src/Foo.php', BaseDirectory::from('/')->relativePathOf('/src/Foo.php'));
        $this->assertSame('/src/Foo.php', BaseDirectory::from('/')->absolutePathOf('src/Foo.php'));
    }

    public function testNamesThePathOfAFileInAnotherCheckoutTheSame(): void
    {
        $relativePath = BaseDirectory::from('/home/runner-a/project')->relativePathOf('/home/runner-a/project/src/Foo.php');

        $this->assertSame(
            '/home/runner-b/project/src/Foo.php',
            BaseDirectory::from('/home/runner-b/project')->absolutePathOf($relativePath),
        );
    }

    public function testLeavesAPathThatIsRelativeAlreadyAsItIs(): void
    {
        $this->assertSame('src/Foo.php', BaseDirectory::from('/project')->relativePathOf('src/Foo.php'));
    }

    public function testLeavesAPathThatIsNotAPathOnTheFileSystemAsItIs(): void
    {
        $this->assertSame('vfs://root/src/Foo.php', BaseDirectory::from('/project')->relativePathOf('vfs://root/src/Foo.php'));
        $this->assertSame('vfs://root/src/Foo.php', BaseDirectory::from('/project')->absolutePathOf('vfs://root/src/Foo.php'));
    }
}
