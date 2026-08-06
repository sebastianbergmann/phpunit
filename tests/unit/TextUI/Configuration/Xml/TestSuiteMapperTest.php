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
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite as TestSuiteObject;
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

#[CoversClass(TestSuiteMapper::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class TestSuiteMapperTest extends TestCase
{
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
}
