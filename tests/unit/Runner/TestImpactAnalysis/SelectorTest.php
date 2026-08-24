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
use const PHP_EOL;
use function array_merge;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sort;
use function str_starts_with;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use PHPUnit\TestFixture\TestImpactAnalysis\SelectionTest;
use PHPUnit\TestFixture\TestImpactAnalysis\UnrelatedSelectionTest;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;
use ReflectionClass;

#[CoversClass(Selector::class)]
#[UsesClass(Assumptions::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[UsesClass(PathHasher::class)]
#[UsesClass(Recording::class)]
#[UsesClass(Selection::class)]
#[UsesClass(TestImpactDataFile::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class SelectorTest extends TestCase
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

    public function testRunsEveryTestWhenNothingWasRecorded(): void
    {
        $selection = $this->selectorFor($this->temporaryDirectory(), [])->select($this->tests(), []);

        $this->assertTrue($selection->isEverything());
        $this->assertSame('no test impact data has been recorded', $selection->reason());
    }

    public function testRunsNoTestWhenNothingChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $source    = $this->writeSourceFile($directory, 'Money', 'first');

        $selection = $this->selectorFor($directory, $this->everyTestDependsOn($source), [$source])->select($this->tests(), [$source]);

        $this->assertFalse($selection->isEverything());
        $this->assertSame([$this->phpt()], $selection->tests());
        $this->assertSame(3, $selection->numberOfTestsThatAreNotRun());
    }

    public function testRunsATestThatDependsOnAFileThatChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $formatter = $this->writeSourceFile($directory, 'Formatter', 'first');

        $selector = $this->selectorFor(
            $directory,
            [
                SelectionTest::class . '::testProducesMoney'    => [$money],
                SelectionTest::class . '::testConsumesMoney'    => [$money],
                UnrelatedSelectionTest::class . '::testFormats' => [$formatter],
            ],
            [$money, $formatter],
        );

        $this->writeSourceFile($directory, 'Money', 'second');

        $selection = $selector->select($this->tests(), [$money, $formatter]);

        $this->assertSame(
            $this->sorted([
                SelectionTest::class . '::testConsumesMoney',
                SelectionTest::class . '::testProducesMoney',
                $this->phpt(),
            ]),
            $this->sorted($selection->tests()),
        );
    }

    public function testRunsATestThatIsDependedUponByATestThatIsRun(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $formatter = $this->writeSourceFile($directory, 'Formatter', 'first');

        /*
         * Only the test that consumes what another test provides depends on
         * the file that changes; the test it depends on has to be run anyway.
         */
        $selector = $this->selectorFor(
            $directory,
            [
                SelectionTest::class . '::testProducesMoney'    => [$formatter],
                SelectionTest::class . '::testConsumesMoney'    => [$money],
                UnrelatedSelectionTest::class . '::testFormats' => [$formatter],
            ],
            [$money, $formatter],
        );

        $this->writeSourceFile($directory, 'Money', 'second');

        $selection = $selector->select($this->tests(), [$money, $formatter]);

        $this->assertSame(
            $this->sorted([
                SelectionTest::class . '::testConsumesMoney',
                SelectionTest::class . '::testProducesMoney',
                $this->phpt(),
            ]),
            $this->sorted($selection->tests()),
        );
    }

    public function testRunsATestThatWasNeverRecorded(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');

        $selection = $this->selectorFor(
            $directory,
            [UnrelatedSelectionTest::class . '::testFormats' => [$money]],
            [$money],
        )->select($this->tests(), [$money]);

        $this->assertSame(
            $this->sorted([
                SelectionTest::class . '::testConsumesMoney',
                SelectionTest::class . '::testProducesMoney',
                $this->phpt(),
            ]),
            $this->sorted($selection->tests()),
        );
    }

    public function testRunsATestThatDidNotPassWhenItWasLastRun(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');

        $testRunHistory = new DefaultTestRunHistory($directory . DIRECTORY_SEPARATOR . 'history');

        $testRunHistory->setStatus(
            TestRunHistoryId::fromTestClassAndMethodName(UnrelatedSelectionTest::class, 'testFormats'),
            TestStatus::failure('for the sake of this test'),
        );

        $selection = $this->selectorFor($directory, $this->everyTestDependsOn($money), [$money], $testRunHistory)->select($this->tests(), [$money]);

        $this->assertSame(
            $this->sorted([UnrelatedSelectionTest::class . '::testFormats', $this->phpt()]),
            $this->sorted($selection->tests()),
        );
    }

    public function testRunsTheTestsThatDependOnAPathThatIsNamed(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $formatter = $this->writeSourceFile($directory, 'Formatter', 'first');

        $selector = $this->selectorFor(
            $directory,
            [
                SelectionTest::class . '::testProducesMoney'    => [$money],
                SelectionTest::class . '::testConsumesMoney'    => [$money],
                UnrelatedSelectionTest::class . '::testFormats' => [$formatter],
            ],
            [$money, $formatter],
        );

        $selection = $selector->select($this->tests(), [$money, $formatter], [$formatter]);

        $this->assertSame(
            $this->sorted([UnrelatedSelectionTest::class . '::testFormats', $this->phpt()]),
            $this->sorted($selection->tests()),
        );
    }

    public function testRunsTheTestsThatDependOnAnythingBeneathADirectoryThatIsNamed(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $formatter = $this->writeSourceFile($directory, 'Formatter', 'first');

        $selector = $this->selectorFor(
            $directory,
            [
                SelectionTest::class . '::testProducesMoney'    => [$money],
                SelectionTest::class . '::testConsumesMoney'    => [$money],
                UnrelatedSelectionTest::class . '::testFormats' => [$formatter],
            ],
            [$money, $formatter],
        );

        $selection = $selector->select($this->tests(), [$money, $formatter], [$directory]);

        $this->assertCount(4, $selection->tests());
    }

    public function testDoesNotWorkOutWhatChangedWhenItIsNamed(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $formatter = $this->writeSourceFile($directory, 'Formatter', 'first');

        $selector = $this->selectorFor(
            $directory,
            [
                SelectionTest::class . '::testProducesMoney'    => [$money],
                SelectionTest::class . '::testConsumesMoney'    => [$money],
                UnrelatedSelectionTest::class . '::testFormats' => [$formatter],
            ],
            [$money, $formatter],
        );

        /*
         * The file that changed is not the file that is named: what is named
         * is what the selection is made from.
         */
        $this->writeSourceFile($directory, 'Money', 'second');

        $selection = $selector->select($this->tests(), [$money, $formatter], [$formatter]);

        $this->assertSame(
            $this->sorted([UnrelatedSelectionTest::class . '::testFormats', $this->phpt()]),
            $this->sorted($selection->tests()),
        );
    }

    public function testRunsEveryTestWhenAPathThatIsNamedWasNotRecorded(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');

        $selection = $this->selectorFor($directory, $this->everyTestDependsOn($money), [$money])->select(
            $this->tests(),
            [$money],
            [$directory . DIRECTORY_SEPARATOR . 'NotRecorded.php'],
        );

        $this->assertTrue($selection->isEverything());
        $this->assertStringContainsString('NotRecorded.php is not among the files that were recorded', $selection->reason());
    }

    public function testRunsEveryTestWhenASourceFileThatWasNotRecordedIsThere(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $added     = $this->writeSourceFile($directory, 'Added', 'first');

        $selection = $this->selectorFor($directory, $this->everyTestDependsOn($money), [$money])->select($this->tests(), [$money, $added]);

        $this->assertTrue($selection->isEverything());
        $this->assertStringContainsString('Added.php was not there', $selection->reason());
    }

    public function testRunsEveryTestWhenAFileNoTestDependsOnChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $money     = $this->writeSourceFile($directory, 'Money', 'first');
        $untested  = $this->writeSourceFile($directory, 'Untested', 'first');

        $selector = $this->selectorFor($directory, $this->everyTestDependsOn($money), [$money, $untested]);

        $this->writeSourceFile($directory, 'Untested', 'second');

        $selection = $selector->select($this->tests(), [$money, $untested]);

        $this->assertTrue($selection->isEverything());
        $this->assertStringContainsString('Untested.php changed and no test is recorded as depending on it', $selection->reason());
    }

    /**
     * @param non-empty-string $file
     *
     * @return array<non-empty-string, list<non-empty-string>>
     */
    private function everyTestDependsOn(string $file): array
    {
        $dependencies = [];

        foreach ($this->tests() as $test) {
            $dependencies[$test->valueObjectForEvents()->id()] = [$file];
        }

        return $dependencies;
    }

    /**
     * Every test is recorded as depending on the file its own class is
     * declared in as well: that is what makes a change to a test select it.
     *
     * @param array<non-empty-string, list<non-empty-string>> $dependencies
     * @param list<non-empty-string>                          $sourceFiles
     */
    private function selectorFor(string $directory, array $dependencies, array $sourceFiles = [], ?TestRunHistory $testRunHistory = null): Selector
    {
        $file = new TestImpactDataFile($directory, $this->assumptions());

        if ($dependencies !== []) {
            $data = new DefaultTestImpactData;

            foreach ($dependencies as $test => $filesOfTest) {
                $data->record($test, array_merge($filesOfTest, $this->fileOfTestClassOf($test)));
            }

            $file->persist($data, Provenance::ObservedExecution, $sourceFiles);
        }

        if ($testRunHistory === null) {
            $testRunHistory = new DefaultTestRunHistory($directory . DIRECTORY_SEPARATOR . 'history');
        }

        return new Selector($file, $testRunHistory);
    }

    /**
     * @return list<non-empty-string>
     */
    private function fileOfTestClassOf(string $test): array
    {
        $className = SelectionTest::class;

        if (str_starts_with($test, UnrelatedSelectionTest::class)) {
            $className = UnrelatedSelectionTest::class;
        }

        $file = new ReflectionClass($className)->getFileName();

        $this->assertIsString($file);
        $this->assertNotSame('', $file);

        return [$file];
    }

    /**
     * @return non-empty-string
     */
    private function phpt(): string
    {
        $phpt = realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/test-that-declares-nothing.phpt');

        $this->assertIsString($phpt);
        $this->assertNotSame('', $phpt);

        return $phpt;
    }

    /**
     * A test that is not a test method is part of the tests that are
     * considered: nothing can be recorded for it, and it therefore has to be
     * run whatever changed.
     *
     * @return list<PhptTestCase|TestCase>
     */
    private function tests(): array
    {
        $tests = [];

        foreach ([SelectionTest::class, UnrelatedSelectionTest::class] as $className) {
            foreach (TestSuite::fromClassReflector(new ReflectionClass($className))->collect() as $test) {
                $tests[] = $test;
            }
        }

        $tests[] = new PhptTestCase($this->phpt());

        return $tests;
    }

    /**
     * @param list<non-empty-string> $tests
     *
     * @return list<non-empty-string>
     */
    private function sorted(array $tests): array
    {
        sort($tests);

        return $tests;
    }

    private function assumptions(): Assumptions
    {
        return Assumptions::from(
            null,
            new Source(
                null,
                false,
                FilterDirectoryCollection::fromArray([]),
                FilterFileCollection::fromArray([]),
                FilterDirectoryCollection::fromArray([]),
                FilterFileCollection::fromArray([]),
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                [
                    'functions' => [],
                    'methods'   => [],
                ],
                false,
                false,
                false,
                true,
            ),
        );
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-selector-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @return non-empty-string
     */
    private function writeSourceFile(string $directory, string $name, string $contents): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . '.php';

        file_put_contents($file, '<?php declare(strict_types=1); // ' . $contents . PHP_EOL);

        return $file;
    }
}
