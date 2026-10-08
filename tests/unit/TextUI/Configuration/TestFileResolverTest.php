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

use const DIRECTORY_SEPARATOR;
use const PHP_VERSION;
use function realpath;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder as CliConfigurationBuilder;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(TestFileResolver::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
final class TestFileResolverTest extends TestCase
{
    public function testResolvesTestFilesInDirectoryOfTestSuite(): void
    {
        $this->assertSame(
            [
                [
                    'name'  => 'default',
                    'files' => [
                        ['path' => $this->fixturePath('ExcludedTest.php'), 'groups' => ['group']],
                        ['path' => $this->fixturePath('SuccessTest.php'), 'groups' => ['group']],
                    ],
                ],
            ],
            new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
                TestSuiteCollection::fromArray([
                    $this->testSuite('default', [$this->testDirectory(groups: ['group'])]),
                ]),
                [],
                [],
            ),
        );
    }

    public function testResolvesTestFileOfTestSuite(): void
    {
        $this->assertSame(
            [
                [
                    'name'  => 'default',
                    'files' => [
                        ['path' => $this->fixturePath('SuccessTest.php'), 'groups' => ['group']],
                    ],
                ],
            ],
            new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
                TestSuiteCollection::fromArray([
                    $this->testSuite('default', [], [$this->testFile(groups: ['group'])]),
                ]),
                [],
                [],
            ),
        );
    }

    public function testDoesNotResolveTestFilesThatAreExcluded(): void
    {
        $this->assertSame(
            [
                [
                    'name'  => 'default',
                    'files' => [
                        ['path' => $this->fixturePath('SuccessTest.php'), 'groups' => []],
                    ],
                ],
            ],
            new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
                TestSuiteCollection::fromArray([
                    $this->testSuite('default', [$this->testDirectory()], [], [$this->fixturePath('ExcludedTest.php')]),
                ]),
                [],
                [],
            ),
        );
    }

    public function testResolvesTestSuiteThatHasNoTestFiles(): void
    {
        $this->assertSame(
            [
                ['name' => 'default', 'files' => []],
            ],
            new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
                TestSuiteCollection::fromArray([
                    $this->testSuite(
                        'default',
                        [$this->testDirectory('9999.0.0')],
                        [$this->testFile('9999.0.0')],
                    ),
                ]),
                [],
                [],
            ),
        );
    }

    public function testResolvesOnlyTestSuitesThatAreIncluded(): void
    {
        $resolved = new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
            $this->twoTestSuites(),
            ['second'],
            [],
        );

        $this->assertCount(1, $resolved);
        $this->assertSame('second', $resolved[0]['name']);
    }

    public function testDoesNotResolveTestSuitesThatAreExcluded(): void
    {
        $resolved = new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
            $this->twoTestSuites(),
            [],
            ['first'],
        );

        $this->assertCount(1, $resolved);
        $this->assertSame('second', $resolved[0]['name']);
    }

    public function testDoesNotResolveTestFileForMoreThanOneTestSuite(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->matchesRegularExpression('/^Cannot add file .+SuccessTest\.php to test suite "second" as it was already added to test suite "first"$/'))
            ->seal();

        $this->assertSame(
            [
                [
                    'name'  => 'first',
                    'files' => [
                        ['path' => $this->fixturePath('ExcludedTest.php'), 'groups' => []],
                        ['path' => $this->fixturePath('SuccessTest.php'), 'groups' => []],
                    ],
                ],
                ['name' => 'second', 'files' => []],
            ],
            new TestFileResolver($emitter)->filesInTestSuites(
                TestSuiteCollection::fromArray([
                    $this->testSuite('first', [$this->testDirectory()]),
                    $this->testSuite('second', [], [$this->testFile()]),
                ]),
                [],
                [],
            ),
        );
    }

    public function testDoesNotResolveTestFileInDirectoryForMoreThanOneTestSuite(): void
    {
        $this->assertSame(
            [
                [
                    'name'  => 'first',
                    'files' => [
                        ['path' => $this->fixturePath('ExcludedTest.php'), 'groups' => []],
                        ['path' => $this->fixturePath('SuccessTest.php'), 'groups' => []],
                    ],
                ],
                ['name' => 'second', 'files' => []],
            ],
            new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
                TestSuiteCollection::fromArray([
                    $this->testSuite('first', [$this->testDirectory()]),
                    $this->testSuite('second', [$this->testDirectory()]),
                ]),
                [],
                [],
            ),
        );
    }

    public function testWarnsAboutGroupNameThatCannotBeUsedToSelectTests(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->stringStartsWith('Group name "one+two" configured for '))
            ->seal();

        new TestFileResolver($emitter)->filesInTestSuites(
            TestSuiteCollection::fromArray([
                $this->testSuite('default', [], [$this->testFile(groups: ['one+two'])]),
            ]),
            [],
            [],
        );
    }

    public function testRejectsTestDirectoryThatDoesNotExist(): void
    {
        $this->expectException(TestDirectoryNotFoundException::class);

        new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    [
                        new TestDirectory(
                            $this->fixturePath('does-not-exist'),
                            '',
                            'Test.php',
                            PHP_VERSION,
                            new VersionComparisonOperator('>='),
                            [],
                        ),
                    ],
                ),
            ]),
            [],
            [],
        );
    }

    public function testRejectsTestFileThatDoesNotExist(): void
    {
        $this->expectException(TestFileNotFoundException::class);

        new TestFileResolver($this->createStub(Emitter::class))->filesInTestSuites(
            TestSuiteCollection::fromArray([
                $this->testSuite(
                    'default',
                    [],
                    [
                        new TestFile(
                            $this->fixturePath('DoesNotExistTest.php'),
                            PHP_VERSION,
                            new VersionComparisonOperator('>='),
                            [],
                        ),
                    ],
                ),
            ]),
            [],
            [],
        );
    }

    public function testSelectsTestFilesFromCommandLineWhenArgumentsAreGiven(): void
    {
        $this->assertTrue(
            new TestFileResolver($this->createStub(Emitter::class))->selectsTestFilesFromCommandLine(
                $this->configurationFromCommandLine($this->fixturePath('SuccessTest.php')),
            ),
        );
    }

    public function testSelectsTestFilesFromCommandLineWhenTestFilesFileIsGiven(): void
    {
        $this->assertTrue(
            new TestFileResolver($this->createStub(Emitter::class))->selectsTestFilesFromCommandLine(
                $this->configurationFromCommandLine('--test-files-file', $this->testFilesFile('test-files.txt')),
            ),
        );
    }

    public function testDoesNotSelectTestFilesFromCommandLineWhenNeitherArgumentsNorTestFilesFileAreGiven(): void
    {
        $this->assertFalse(
            new TestFileResolver($this->createStub(Emitter::class))->selectsTestFilesFromCommandLine(
                $this->configurationFromCommandLine(),
            ),
        );
    }

    public function testResolvesPathsThatAreGivenAsArguments(): void
    {
        $this->assertSame(
            [
                $this->fixturePath(),
                $this->fixturePath('SuccessTest.php'),
            ],
            new TestFileResolver($this->createStub(Emitter::class))->pathsFromCommandLine(
                $this->configurationFromCommandLine(
                    $this->fixturePath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testsuite-mapper',
                    $this->fixturePath('SuccessTest.php'),
                ),
            ),
        );
    }

    public function testResolvesPathsThatAreListedInTestFilesFileRelativeToIt(): void
    {
        $this->assertSame(
            [
                $this->fixturePath('SuccessTest.php'),
                $this->fixturePath('ExcludedTest.php'),
            ],
            new TestFileResolver($this->createStub(Emitter::class))->pathsFromCommandLine(
                $this->configurationFromCommandLine('--test-files-file', $this->testFilesFile('test-files.txt')),
            ),
        );
    }

    public function testRejectsPathGivenAsArgumentThatDoesNotExist(): void
    {
        $this->expectException(TestFileNotFoundException::class);
        $this->expectExceptionMessage('Test file "does-not-exist" not found');

        new TestFileResolver($this->createStub(Emitter::class))->pathsFromCommandLine(
            $this->configurationFromCommandLine('does-not-exist'),
        );
    }

    public function testRejectsPathListedInTestFilesFileThatDoesNotExist(): void
    {
        $this->expectException(TestFileNotFoundException::class);
        $this->expectExceptionMessage('Test file "DoesNotExistTest.php" not found');

        new TestFileResolver($this->createStub(Emitter::class))->pathsFromCommandLine(
            $this->configurationFromCommandLine('--test-files-file', $this->testFilesFile('test-files-with-file-that-does-not-exist.txt')),
        );
    }

    public function testRejectsTestFilesFileThatDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read from does-not-exist.txt');

        new TestFileResolver($this->createStub(Emitter::class))->pathsFromCommandLine(
            $this->configurationFromCommandLine('--test-files-file', 'does-not-exist.txt'),
        );
    }

    public function testResolvesTestFilesInDirectory(): void
    {
        $this->assertSame(
            [$this->fixturePath('SuccessTest.php')],
            new TestFileResolver($this->createStub(Emitter::class))->filesInDirectory(
                $this->fixturePath(),
                ['SuccessTest.php'],
            ),
        );
    }

    /**
     * @param non-empty-string ...$parameters
     */
    private function configurationFromCommandLine(string ...$parameters): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            // the first parameter is the name of the script that was invoked
            new CliConfigurationBuilder($this->createStub(Emitter::class))->fromParameters(['phpunit', ...$parameters]),
            DefaultConfiguration::create(),
        );
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function testFilesFile(string $name): string
    {
        return TEST_FILES_PATH . 'test-file-resolver' . DIRECTORY_SEPARATOR . $name;
    }

    private function twoTestSuites(): TestSuiteCollection
    {
        return TestSuiteCollection::fromArray([
            $this->testSuite('first', [], [$this->testFile()]),
            $this->testSuite('second', [$this->testDirectory()], [], [$this->fixturePath('SuccessTest.php')]),
        ]);
    }

    /**
     * @param non-empty-string       $name
     * @param list<TestDirectory>    $directories
     * @param list<TestFile>         $files
     * @param list<non-empty-string> $exclude
     */
    private function testSuite(string $name, array $directories, array $files = [], array $exclude = []): TestSuite
    {
        $excludedFiles = [];

        foreach ($exclude as $path) {
            $excludedFiles[] = new File($path);
        }

        return new TestSuite(
            $name,
            TestDirectoryCollection::fromArray($directories),
            TestFileCollection::fromArray($files),
            FileCollection::fromArray($excludedFiles),
        );
    }

    /**
     * @param list<non-empty-string> $groups
     */
    private function testDirectory(string $phpVersion = PHP_VERSION, array $groups = []): TestDirectory
    {
        return new TestDirectory(
            $this->fixturePath(),
            '',
            'Test.php',
            $phpVersion,
            new VersionComparisonOperator('>='),
            $groups,
        );
    }

    /**
     * @param list<non-empty-string> $groups
     */
    private function testFile(string $phpVersion = PHP_VERSION, array $groups = []): TestFile
    {
        return new TestFile(
            $this->fixturePath('SuccessTest.php'),
            $phpVersion,
            new VersionComparisonOperator('>='),
            $groups,
        );
    }

    /**
     * @return non-empty-string
     */
    private function fixturePath(string $file = ''): string
    {
        $path = realpath(TEST_FILES_PATH . 'testsuite-mapper');

        $this->assertIsString($path);

        if ($file !== '') {
            $path .= DIRECTORY_SEPARATOR . $file;
        }

        return $path;
    }
}
