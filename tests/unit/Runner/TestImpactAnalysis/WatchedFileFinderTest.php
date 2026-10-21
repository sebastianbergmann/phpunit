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
use PHPUnit\TextUI\Configuration\File;
use PHPUnit\TextUI\Configuration\FileCollection;
use PHPUnit\TextUI\Configuration\FilterDirectory;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFile;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\TestDirectory;
use PHPUnit\TextUI\Configuration\TestDirectoryCollection;
use PHPUnit\TextUI\Configuration\TestFile;
use PHPUnit\TextUI\Configuration\TestFileCollection;
use PHPUnit\TextUI\Configuration\TestImpactAnalysis;
use PHPUnit\TextUI\Configuration\TestSuite;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(WatchedFileFinder::class)]
#[UsesClass(TestImpactAnalysis::class)]
#[UsesClass(AddedFilesAreReachedThroughChanges::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class WatchedFileFinderTest extends TestCase
{
    public function testWatchesThePhpFilesInTheDirectoriesOfTheTestSuitesThatAreNotTestFiles(): void
    {
        $this->assertSame(
            [
                self::path('tests/Helper.php'),
                self::path('tests/ListedTestFile.php'),
                self::path('tests/fixtures/fixture.php'),
            ],
            (new WatchedFileFinder)->find(
                self::testSuites([self::path('tests')], [], []),
                self::nothingIsWatched(),
            ),
        );
    }

    public function testDoesNotWatchATestFileThatATestSuiteNames(): void
    {
        $this->assertSame(
            [
                self::path('tests/Helper.php'),
                self::path('tests/fixtures/fixture.php'),
            ],
            (new WatchedFileFinder)->find(
                self::testSuites([self::path('tests')], [self::path('tests/ListedTestFile.php')], []),
                self::nothingIsWatched(),
            ),
        );
    }

    public function testDoesNotWatchWhatATestSuiteExcludes(): void
    {
        $this->assertSame(
            [
                self::path('tests/Helper.php'),
                self::path('tests/ListedTestFile.php'),
            ],
            (new WatchedFileFinder)->find(
                self::testSuites([self::path('tests')], [], [self::path('tests/fixtures')]),
                self::nothingIsWatched(),
            ),
        );
    }

    public function testWatchesTheDirectoriesAndFilesTheConfigurationSaysAreWatched(): void
    {
        $this->assertSame(
            [
                self::path('config/app.php'),
                self::path('config/services.yaml'),
                self::path('settings.ini'),
            ],
            (new WatchedFileFinder)->find(
                TestSuiteCollection::fromArray([]),
                new TestImpactAnalysis(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::path('config'), '', '.php'),
                            new FilterDirectory(self::path('config'), '', '.yaml'),
                        ],
                    ),
                    FilterFileCollection::fromArray(
                        [
                            new FilterFile(self::path('settings.ini')),
                            new FilterFile(self::path('does-not-exist.ini')),
                        ],
                    ),
                    self::noAddedFilesAreReachedThroughChanges(),
                ),
            ),
        );
    }

    public function testDoesNotWatchATestFileInADirectoryTheConfigurationSaysIsWatched(): void
    {
        $this->assertSame(
            [
                self::path('tests/Helper.php'),
                self::path('tests/ListedTestFile.php'),
                self::path('tests/fixtures/fixture.php'),
            ],
            (new WatchedFileFinder)->find(
                self::testSuites([self::path('tests')], [], []),
                new TestImpactAnalysis(
                    FilterDirectoryCollection::fromArray(
                        [
                            new FilterDirectory(self::path('tests'), '', '.php'),
                        ],
                    ),
                    FilterFileCollection::fromArray([]),
                    self::noAddedFilesAreReachedThroughChanges(),
                ),
            ),
        );
    }

    /**
     * @param list<non-empty-string> $directories
     * @param list<non-empty-string> $files
     * @param list<non-empty-string> $exclude
     */
    private static function testSuites(array $directories, array $files, array $exclude): TestSuiteCollection
    {
        $testDirectories = [];

        foreach ($directories as $directory) {
            $testDirectories[] = new TestDirectory($directory, '', 'Test.php', '', new VersionComparisonOperator('>='), []);
        }

        $testFiles = [];

        foreach ($files as $file) {
            $testFiles[] = new TestFile($file, '', new VersionComparisonOperator('>='), []);
        }

        $excludedFiles = [];

        foreach ($exclude as $file) {
            $excludedFiles[] = new File($file);
        }

        return TestSuiteCollection::fromArray(
            [
                new TestSuite(
                    'default',
                    TestDirectoryCollection::fromArray($testDirectories),
                    TestFileCollection::fromArray($testFiles),
                    FileCollection::fromArray($excludedFiles),
                ),
            ],
        );
    }

    private static function noAddedFilesAreReachedThroughChanges(): AddedFilesAreReachedThroughChanges
    {
        return new AddedFilesAreReachedThroughChanges(
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
        );
    }

    private static function nothingIsWatched(): TestImpactAnalysis
    {
        return new TestImpactAnalysis(
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            self::noAddedFilesAreReachedThroughChanges(),
        );
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private static function path(string $path): string
    {
        $directory = realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/watched');

        self::assertIsString($directory);

        return $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
