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
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sort;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestImpactAnalysis\DriverThatReportsWhatItIsTold;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\StaticAnalysis\Registry;

#[CoversClass(ExecutionOutsideOfTests::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[UsesClass(ExecutedFiles::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class ExecutionOutsideOfTestsTest extends TestCase
{
    /**
     * The line of a source file written by writeSourceFile() that is
     * executable.
     */
    private const int EXECUTABLE_LINE = 4;
    private ?string $directory        = null;

    protected function tearDown(): void
    {
        if ($this->directory === null) {
            return;
        }

        $entries = scandir($this->directory);

        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                unlink($this->directory . DIRECTORY_SEPARATOR . $entry);
            }
        }

        rmdir($this->directory);

        $this->directory = null;
    }

    public function testRecordsWhatIsExecutedOutsideOfTestsForEveryTest(): void
    {
        $bootstrapped = $this->writeSourceFile('Bootstrapped');
        $driver       = new DriverThatReportsWhatItIsTold;
        $execution    = $this->executionOutsideOfTests($driver, [$bootstrapped]);

        $execution->start();

        $driver->execute($bootstrapped, self::EXECUTABLE_LINE);

        $execution->stop();

        $this->assertSame([$bootstrapped], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    public function testLeavesTheCodeCoverageDriverToATestWhileTheTestRuns(): void
    {
        $executedByTest = $this->writeSourceFile('ExecutedByTest');
        $driver         = new DriverThatReportsWhatItIsTold;
        $execution      = $this->executionOutsideOfTests($driver, [$executedByTest]);

        $execution->start();

        $this->assertTrue($driver->isRunning());

        $execution->pause();

        $this->assertFalse($driver->isRunning());

        $driver->execute($executedByTest, self::EXECUTABLE_LINE);

        $execution->resume();

        $this->assertTrue($driver->isRunning());

        $execution->stop();

        $this->assertFalse($driver->isRunning());
        $this->assertSame([], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    public function testRecordsWhatADataProviderExecutesForTheTestsThatAreMadeFromTheDataItProvides(): void
    {
        $provided  = $this->writeSourceFile('Provided');
        $executed  = $this->writeSourceFile('Executed');
        $driver    = new DriverThatReportsWhatItIsTold;
        $execution = $this->executionOutsideOfTests($driver, [$provided, $executed]);

        $execution->start();
        $execution->enterDataProvider('FooTest', 'testOne');

        $driver->execute($provided, self::EXECUTABLE_LINE);

        $execution->leaveDataProvider();

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne#0', [$executed]);
        $data->record('FooTest::testTwo', [$executed]);

        $execution->testWasRecorded('FooTest::testOne#0', 'FooTest', 'testOne');
        $execution->testWasRecorded('FooTest::testTwo', 'FooTest', 'testTwo');
        $execution->stop();

        $execution->addTo($data);

        $this->assertSame(
            [
                'FooTest::testOne#0' => [$executed, $provided],
                'FooTest::testTwo'   => [$executed],
            ],
            $data->recorded(),
        );

        $this->assertSame([], $data->executedOutsideOfTests());
    }

    public function testRecordsWhatIsExecutedWhileTheTestsOfATestClassAreRunForTheTestsOfThatTestClass(): void
    {
        $beforeFirstTest = $this->writeSourceFile('BeforeFirstTest');
        $afterLastTest   = $this->writeSourceFile('AfterLastTest');
        $executed        = $this->writeSourceFile('Executed');
        $driver          = new DriverThatReportsWhatItIsTold;
        $execution       = $this->executionOutsideOfTests($driver, [$beforeFirstTest, $afterLastTest, $executed]);

        $execution->start();
        $execution->enterTestClass('FooTest');

        $driver->execute($beforeFirstTest, self::EXECUTABLE_LINE);

        $execution->pause();
        $execution->resume();

        $driver->execute($afterLastTest, self::EXECUTABLE_LINE);

        $execution->leaveTestClass();

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$executed]);
        $data->record('BarTest::testOne', [$executed]);

        $execution->testWasRecorded('FooTest::testOne', 'FooTest', 'testOne');
        $execution->testWasRecorded('BarTest::testOne', 'BarTest', 'testOne');
        $execution->stop();

        $execution->addTo($data);

        $filesOfFooTest = $data->recorded()['FooTest::testOne'];

        sort($filesOfFooTest);

        $expected = [$afterLastTest, $beforeFirstTest, $executed];

        sort($expected);

        $this->assertSame($expected, $filesOfFooTest);
        $this->assertSame([$executed], $data->recorded()['BarTest::testOne']);
    }

    public function testRecordsWhatIsExecutedWhileTheTestsOfANestedTestClassAreRunForTheTestsOfThatTestClass(): void
    {
        $outer     = $this->writeSourceFile('Outer');
        $inner     = $this->writeSourceFile('Inner');
        $driver    = new DriverThatReportsWhatItIsTold;
        $execution = $this->executionOutsideOfTests($driver, [$outer, $inner]);

        $execution->start();
        $execution->enterTestClass('OuterTest');
        $execution->enterTestClass('InnerTest');

        $driver->execute($inner, self::EXECUTABLE_LINE);

        $execution->leaveTestClass();

        $driver->execute($outer, self::EXECUTABLE_LINE);

        $execution->leaveTestClass();

        $data = new DefaultTestImpactData;
        $data->record('OuterTest::testOne', []);
        $data->record('InnerTest::testOne', []);

        $execution->testWasRecorded('OuterTest::testOne', 'OuterTest', 'testOne');
        $execution->testWasRecorded('InnerTest::testOne', 'InnerTest', 'testOne');
        $execution->stop();

        $execution->addTo($data);

        $this->assertSame(
            [
                'OuterTest::testOne' => [$outer],
                'InnerTest::testOne' => [$inner],
            ],
            $data->recorded(),
        );
    }

    /**
     * A test that builds and runs tests of its own, as the tests of PHPUnit
     * do, executes what those tests execute, and what their data providers
     * and test classes execute.
     */
    public function testLeavesWhatIsExecutedWhileATestRunsToTheTestEvenWhenItIsADataProviderOrATestClass(): void
    {
        $executedAfterTest = $this->writeSourceFile('ExecutedAfterTest');
        $driver            = new DriverThatReportsWhatItIsTold;
        $execution         = $this->executionOutsideOfTests($driver, [$executedAfterTest]);

        $execution->start();
        $execution->pause();
        $execution->enterDataProvider('InnerTest', 'testOne');

        $this->assertFalse($driver->isRunning());

        $execution->leaveDataProvider();
        $execution->enterTestClass('InnerTest');
        $execution->leaveTestClass();

        $this->assertFalse($driver->isRunning());

        $execution->resume();

        $driver->execute($executedAfterTest, self::EXECUTABLE_LINE);

        $execution->stop();

        $this->assertSame([$executedAfterTest], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    public function testDoesNotCollectBeforeItIsStartedOrAfterItWasStopped(): void
    {
        $driver    = new DriverThatReportsWhatItIsTold;
        $execution = $this->executionOutsideOfTests($driver, []);

        $execution->pause();
        $execution->resume();
        $execution->enterTestClass('FooTest');

        $this->assertFalse($driver->isRunning());

        $execution->start();
        $execution->stop();
        $execution->start();
        $execution->resume();

        $this->assertFalse($driver->isRunning());
    }

    public function testAddsNothingToATestItWasNotToldAbout(): void
    {
        $beforeFirstTest = $this->writeSourceFile('BeforeFirstTest');
        $driver          = new DriverThatReportsWhatItIsTold;
        $execution       = $this->executionOutsideOfTests($driver, [$beforeFirstTest]);

        $execution->start();
        $execution->enterTestClass('FooTest');

        $driver->execute($beforeFirstTest, self::EXECUTABLE_LINE);

        $execution->leaveTestClass();
        $execution->stop();

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', []);

        $execution->addTo($data);

        $this->assertSame(['FooTest::testOne' => []], $data->recorded());
    }

    public function testRecordsNothingForALineThatIsNotExecutable(): void
    {
        $loaded    = $this->writeSourceFile('Loaded');
        $driver    = new DriverThatReportsWhatItIsTold;
        $execution = $this->executionOutsideOfTests($driver, [$loaded]);

        $execution->start();

        $driver->execute($loaded, 1);

        $execution->stop();

        $this->assertSame([], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    public function testRecordsNothingForAFileThatIsNotFirstPartyCode(): void
    {
        $firstParty = $this->writeSourceFile('FirstParty');
        $thirdParty = $this->writeSourceFile('ThirdParty');
        $driver     = new DriverThatReportsWhatItIsTold;
        $execution  = $this->executionOutsideOfTests($driver, [$firstParty]);

        $execution->start();

        $driver->execute($thirdParty, self::EXECUTABLE_LINE);

        $execution->stop();

        $this->assertSame([], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    public function testRecordsNothingForALineThatIsIgnoredForCodeCoverage(): void
    {
        $ignored   = $this->writeSourceFile('Ignored', ' // @codeCoverageIgnore');
        $driver    = new DriverThatReportsWhatItIsTold;
        $execution = $this->executionOutsideOfTests($driver, [$ignored]);

        $execution->start();

        $driver->execute($ignored, self::EXECUTABLE_LINE);

        $execution->stop();

        $this->assertSame([], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    public function testRecordsALineThatIsIgnoredForCodeCoverageWhenTheAnnotationsForIgnoringCodeAreNotUsed(): void
    {
        $ignored   = $this->writeSourceFile('Ignored', ' // @codeCoverageIgnore');
        $driver    = new DriverThatReportsWhatItIsTold;
        $execution = $this->executionOutsideOfTests($driver, [$ignored], false);

        $execution->start();

        $driver->execute($ignored, self::EXECUTABLE_LINE);

        $execution->stop();

        $this->assertSame([$ignored], $this->addedTo(new DefaultTestImpactData, $execution)->executedOutsideOfTests());
    }

    /**
     * @param list<non-empty-string> $firstPartyCode
     */
    private function executionOutsideOfTests(DriverThatReportsWhatItIsTold $driver, array $firstPartyCode, bool $useAnnotationsForIgnoringCode = true): ExecutionOutsideOfTests
    {
        $filter = new Filter;
        $filter->includeFiles($firstPartyCode);

        return new ExecutionOutsideOfTests(
            $driver,
            $filter,
            Registry::analyser(null, $useAnnotationsForIgnoringCode, false),
            $useAnnotationsForIgnoringCode,
        );
    }

    private function addedTo(DefaultTestImpactData $data, ExecutionOutsideOfTests $execution): DefaultTestImpactData
    {
        $execution->addTo($data);

        return $data;
    }

    /**
     * Writes a source file whose only executable line is EXECUTABLE_LINE.
     *
     * @return non-empty-string
     */
    private function writeSourceFile(string $name, string $commentOnExecutableLine = ''): string
    {
        if ($this->directory === null) {
            $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-execution-outside-of-tests-' . uniqid();

            mkdir($directory);

            $resolved = realpath($directory);

            $this->assertIsString($resolved);

            $this->directory = $resolved;
        }

        $file = $this->directory . DIRECTORY_SEPARATOR . $name . '.php';

        file_put_contents(
            $file,
            '<?php declare(strict_types=1);' . "\n" .
            'function ' . $name . '_' . uniqid() . '(): void' . "\n" .
            '{' . "\n" .
            "    print '';" . $commentOnExecutableLine . "\n" .
            '}' . "\n",
        );

        return $file;
    }
}
