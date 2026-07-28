<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use const DIRECTORY_SEPARATOR;
use const PHP_VERSION;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite as TestSuiteObject;
use PHPUnit\Runner\TestIndex\TestFileSkipper;
use PHPUnit\TextUI\Configuration\File;
use PHPUnit\TextUI\Configuration\FileCollection;
use PHPUnit\TextUI\Configuration\TestDirectory;
use PHPUnit\TextUI\Configuration\TestDirectoryCollection;
use PHPUnit\TextUI\Configuration\TestFile;
use PHPUnit\TextUI\Configuration\TestFileCollection;
use PHPUnit\TextUI\Configuration\TestSuite;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\TestFileNotFoundException;
use PHPUnit\Util\VersionComparisonOperator;
use ReflectionProperty;
use RuntimeException;

#[CoversClass(TestSuiteMapper::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class TestSuiteMapperTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            $entries = scandir($directory);

            if ($entries !== false) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    unlink($directory . DIRECTORY_SEPARATOR . $entry);
                }
            }

            rmdir($directory);
        }

        $this->directories = [];
    }

    public function testMapsDirectoryToTestSuite(): void
    {
        $testSuite = (new TestSuiteMapper)->map(
            'phpunit.xml',
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    TestDirectoryCollection::fromArray([$this->testDirectory()]),
                ),
            ]),
            [],
            [],
        );

        $this->assertSame(2, $testSuite->count());
    }

    public function testDoesNotMapFilesThatAreExcluded(): void
    {
        $testSuite = (new TestSuiteMapper)->map(
            'phpunit.xml',
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    TestDirectoryCollection::fromArray([$this->testDirectory()]),
                    TestFileCollection::fromArray([]),
                    FileCollection::fromArray([
                        new File($this->fixturePath('ExcludedTest.php')),
                    ]),
                ),
            ]),
            [],
            [],
        );

        $this->assertSame(1, $testSuite->count());
    }

    public function testDoesNotMapDirectoryThatRequiresDifferentPhpVersion(): void
    {
        $testSuite = (new TestSuiteMapper)->map(
            'phpunit.xml',
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    TestDirectoryCollection::fromArray([$this->testDirectory('9999.0.0')]),
                ),
            ]),
            [],
            [],
        );

        $this->assertSame(0, $testSuite->count());
    }

    public function testDoesNotMapFileThatRequiresDifferentPhpVersion(): void
    {
        $testSuite = (new TestSuiteMapper)->map(
            'phpunit.xml',
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    TestDirectoryCollection::fromArray([]),
                    TestFileCollection::fromArray([$this->testFile('9999.0.0')]),
                ),
            ]),
            [],
            [],
        );

        $this->assertSame(0, $testSuite->count());
    }

    public function testRejectsFileThatDoesNotExist(): void
    {
        $this->expectException(TestFileNotFoundException::class);

        (new TestSuiteMapper)->map(
            'phpunit.xml',
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    TestDirectoryCollection::fromArray([]),
                    TestFileCollection::fromArray([
                        new TestFile(
                            $this->fixturePath('DoesNotExistTest.php'),
                            PHP_VERSION,
                            new VersionComparisonOperator('>='),
                            [],
                        ),
                    ]),
                ),
            ]),
            [],
            [],
        );
    }

    public function testDoesNotAddFileToMoreThanOneTestSuite(): void
    {
        $testSuite = $this->mapWithThrowAwayEventFacade(
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'first',
                    TestDirectoryCollection::fromArray([]),
                    TestFileCollection::fromArray([$this->testFile()]),
                ),
                $this->testSuite(
                    'second',
                    TestDirectoryCollection::fromArray([]),
                    TestFileCollection::fromArray([$this->testFile()]),
                ),
            ]),
        );

        $this->assertSame(1, $testSuite->count());
    }

    #[TestDox('Does not load a test file in a directory that cannot contribute a test to the run')]
    public function testDoesNotLoadTestFileInDirectoryThatCanBeSkipped(): void
    {
        $directory = $this->directory();

        $this->writeTestClass($directory, 'SkippedInDirectory');

        $skipper = $this->createMock(TestFileSkipper::class);

        $skipper
            ->expects($this->once())
            ->method('canSkipLoading')
            ->willReturn(true);

        $skipper
            ->expects($this->never())
            ->method('startRecording')
            ->seal();

        $testSuite = new TestSuiteMapper($skipper)->map(
            __FILE__,
            $this->testSuiteForDirectory($directory),
            [],
            [],
        );

        /*
         * A file that is not loaded is bookkept as if it were, so the test
         * suite it belongs to is not empty, it only has no tests.
         */
        $this->assertCount(1, $testSuite->tests());
        $this->assertSame(0, $testSuite->count());
    }

    #[TestDox('Stops recording when a test file in a directory cannot be loaded')]
    public function testStopsRecordingWhenTestFileInDirectoryCannotBeLoaded(): void
    {
        $directory = $this->directory();

        $this->writeTestFileThatCannotBeLoaded($directory, 'BrokenInDirectory');

        $skipper = $this->createMock(TestFileSkipper::class);

        $skipper
            ->expects($this->once())
            ->method('canSkipLoading')
            ->willReturn(false);

        $skipper
            ->expects($this->once())
            ->method('startRecording');

        $skipper
            ->expects($this->once())
            ->method('abortRecording');

        $skipper
            ->expects($this->never())
            ->method('stopRecording')
            ->seal();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BrokenInDirectoryTest.php cannot be loaded');

        new TestSuiteMapper($skipper)->map(
            __FILE__,
            $this->testSuiteForDirectory($directory),
            [],
            [],
        );
    }

    #[TestDox('Does not load a test file that cannot contribute a test to the run')]
    public function testDoesNotLoadTestFileThatCanBeSkipped(): void
    {
        $file = $this->writeTestClass($this->directory(), 'SkippedFile');

        $skipper = $this->createMock(TestFileSkipper::class);

        $skipper
            ->expects($this->once())
            ->method('canSkipLoading')
            ->willReturn(true);

        $skipper
            ->expects($this->never())
            ->method('startRecording')
            ->seal();

        $testSuite = new TestSuiteMapper($skipper)->map(
            __FILE__,
            $this->testSuiteForFile($file),
            [],
            [],
        );

        $this->assertCount(1, $testSuite->tests());
        $this->assertSame(0, $testSuite->count());
    }

    #[TestDox('Stops recording when a test file cannot be loaded')]
    public function testStopsRecordingWhenTestFileCannotBeLoaded(): void
    {
        $file = $this->writeTestFileThatCannotBeLoaded($this->directory(), 'BrokenFile');

        $skipper = $this->createMock(TestFileSkipper::class);

        $skipper
            ->expects($this->once())
            ->method('canSkipLoading')
            ->willReturn(false);

        $skipper
            ->expects($this->once())
            ->method('startRecording');

        $skipper
            ->expects($this->once())
            ->method('abortRecording');

        $skipper
            ->expects($this->never())
            ->method('stopRecording')
            ->seal();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BrokenFileTest.php cannot be loaded');

        new TestSuiteMapper($skipper)->map(
            __FILE__,
            $this->testSuiteForFile($file),
            [],
            [],
        );
    }

    private function mapWithThrowAwayEventFacade(TestSuiteCollection $configuredTestSuites): TestSuiteObject
    {
        /*
         * TestSuiteMapper emits a test runner warning when a test file is
         * configured for more than one test suite. This must not end up in the
         * result of the test run that exercises TestSuiteMapper, so it is
         * emitted into a throw-away event facade that is never forwarded.
         */
        $property = new ReflectionProperty(EventFacade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new EventFacade);

        try {
            return (new TestSuiteMapper)->map('phpunit.xml', $configuredTestSuites, [], []);
        } finally {
            $property->setValue(null, $facade);
        }
    }

    private function testSuite(string $name, TestDirectoryCollection $directories, ?TestFileCollection $files = null, ?FileCollection $exclude = null): TestSuite
    {
        if ($files === null) {
            $files = TestFileCollection::fromArray([]);
        }

        if ($exclude === null) {
            $exclude = FileCollection::fromArray([]);
        }

        return new TestSuite($name, $directories, $files, $exclude);
    }

    private function testDirectory(string $phpVersion = PHP_VERSION): TestDirectory
    {
        return new TestDirectory(
            $this->fixturePath(),
            '',
            'Test.php',
            $phpVersion,
            new VersionComparisonOperator('>='),
            [],
        );
    }

    private function testFile(string $phpVersion = PHP_VERSION): TestFile
    {
        return new TestFile(
            $this->fixturePath('SuccessTest.php'),
            $phpVersion,
            new VersionComparisonOperator('>='),
            [],
        );
    }

    private function fixturePath(string $file = ''): string
    {
        $path = TEST_FILES_PATH . 'testsuite-mapper';

        if ($file !== '') {
            $path .= DIRECTORY_SEPARATOR . $file;
        }

        return $path;
    }

    /**
     * @param non-empty-string $directory
     */
    private function testSuiteForDirectory(string $directory): TestSuiteCollection
    {
        return TestSuiteCollection::fromArray(
            [
                new TestSuite(
                    'default',
                    TestDirectoryCollection::fromArray(
                        [
                            new TestDirectory(
                                $directory,
                                '',
                                'Test.php',
                                PHP_VERSION,
                                new VersionComparisonOperator('>='),
                                [],
                            ),
                        ],
                    ),
                    TestFileCollection::fromArray([]),
                    FileCollection::fromArray([]),
                ),
            ],
        );
    }

    /**
     * @param non-empty-string $file
     */
    private function testSuiteForFile(string $file): TestSuiteCollection
    {
        return TestSuiteCollection::fromArray(
            [
                new TestSuite(
                    'default',
                    TestDirectoryCollection::fromArray([]),
                    TestFileCollection::fromArray(
                        [
                            new TestFile(
                                $file,
                                PHP_VERSION,
                                new VersionComparisonOperator('>='),
                                [],
                            ),
                        ],
                    ),
                    FileCollection::fromArray([]),
                ),
            ],
        );
    }

    /**
     * @return non-empty-string
     */
    private function directory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-suite-mapper-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestClass(string $directory, string $name): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestSuiteMapper;

                use PHPUnit\Framework\Attributes\Small;
                use PHPUnit\Framework\TestCase;

                #[Small]
                final class {$name}Test extends TestCase
                {
                    public function testOne(): void
                    {
                    }
                }
                PHP,
        );

        return $file;
    }

    /**
     * A test file that throws while it is being loaded: loading a test file can
     * fail in a way that is reported instead of ending the run.
     *
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestFileThatCannotBeLoaded(string $directory, string $name): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                throw new \RuntimeException('{$name}Test.php cannot be loaded');
                PHP,
        );

        return $file;
    }
}
