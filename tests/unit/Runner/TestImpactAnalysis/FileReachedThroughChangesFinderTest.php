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
use function realpath;
use function str_replace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\AddedFilesAreReachedThroughChanges;
use PHPUnit\TextUI\Configuration\FilterDirectory;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFile;
use PHPUnit\TextUI\Configuration\FilterFileCollection;

#[CoversClass(FileReachedThroughChangesFinder::class)]
#[UsesClass(AddedFilesAreReachedThroughChanges::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class FileReachedThroughChangesFinderTest extends TestCase
{
    public function testFindsNothingWhenNothingIsConfigured(): void
    {
        $this->assertSame([], (new FileReachedThroughChangesFinder)->find(self::configuration([], [], [], [])));
    }

    public function testFindsTheFilesInTheDirectoriesThatAreIncluded(): void
    {
        $this->assertSame(
            [
                self::path('app/Jobs/SendInvoice.php'),
                self::path('app/Kernel.php'),
                self::path('app/Listeners/Registry.php'),
                self::path('app/Listeners/SendWelcomeMailListener.php'),
            ],
            (new FileReachedThroughChangesFinder)->find(
                self::configuration([new FilterDirectory(self::path('app'), '', '.php')], [], [], []),
            ),
        );
    }

    public function testFindsTheFilesThatAreIncluded(): void
    {
        $this->assertSame(
            [
                self::path('bootstrap/helpers.php'),
            ],
            (new FileReachedThroughChangesFinder)->find(
                self::configuration([], [new FilterFile(self::path('bootstrap/helpers.php')), new FilterFile(self::path('bootstrap/does-not-exist.php'))], [], []),
            ),
        );
    }

    public function testDoesNotFindTheFilesThatAreExcluded(): void
    {
        $this->assertSame(
            [
                self::path('app/Jobs/SendInvoice.php'),
                self::path('app/Listeners/Registry.php'),
            ],
            (new FileReachedThroughChangesFinder)->find(
                self::configuration(
                    [new FilterDirectory(self::path('app'), '', '.php')],
                    [],
                    [new FilterDirectory(self::path('app/Listeners'), 'Send', 'Listener.php')],
                    [new FilterFile(self::path('app/Kernel.php')), new FilterFile(self::path('app/DoesNotExist.php'))],
                ),
            ),
        );
    }

    /**
     * @param list<FilterDirectory> $includeDirectories
     * @param list<FilterFile>      $includeFiles
     * @param list<FilterDirectory> $excludeDirectories
     * @param list<FilterFile>      $excludeFiles
     */
    private static function configuration(array $includeDirectories, array $includeFiles, array $excludeDirectories, array $excludeFiles): AddedFilesAreReachedThroughChanges
    {
        return new AddedFilesAreReachedThroughChanges(
            FilterDirectoryCollection::fromArray($includeDirectories),
            FilterFileCollection::fromArray($includeFiles),
            FilterDirectoryCollection::fromArray($excludeDirectories),
            FilterFileCollection::fromArray($excludeFiles),
        );
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private static function path(string $path): string
    {
        $directory = realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/reached-through-changes');

        self::assertIsString($directory);

        return $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
