<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use Exception;
use MultiDependencyTest;
use NotReorderableTest;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\TestableTestDoxPrinter;

/**
 * @group testdox
 * @small
 * @covers \PHPUnit\Util\TestDox\TestDoxPrinter
 */
final class TestDoxPrinterTest extends TestCase
{
    /**
     * @var TestableTestDoxPrinter
     */
    private $printer;

    /**
     * @var TestSuite
     */
    private $suite;

    /**
     * @var array|string[]
     */
    private $originalOrder;

    protected function setUp(): void
    {
        $this->printer = new TestableTestDoxPrinter(null, false, TestableTestDoxPrinter::COLOR_NEVER, false);

        // Use self-test suite with existing [testOne..testFive]
        $this->suite         = new TestSuite(MultiDependencyTest::class);
        $this->originalOrder = array_map(static function (TestCase $test) {
            return $test->sortId();
        }, $this->suite->tests());
        $this->printer->setOriginalExecutionOrder($this->originalOrder);
    }

    protected function tearDown(): void
    {
        $this->printer       = null;
        $this->suite         = null;
        $this->originalOrder = null;
    }

    /**
     * @testdox Nameless internal wrapper TestSuite
     */
    public function testNamelessRootTestSuiteDoesNotGenerateWarnings(): void
    {
        // Simulate PHPUnit's internal nameless wrapper TestSuite
        $namelessSuite = new TestSuite;

        // Start the wrapper TestSuite
        $this->printer->startTestSuite($namelessSuite);
        $this->printer->writeProgress();
        $this->assertEquals([
            'PHPUNIT_UNNAMED_SUITE::class',
        ], $this->printer->getTestSuiteStack());
        $this->assertEquals([], $this->printer->getBuffer());

        // Start an actual TestSuite with tests inside
        $this->printer->startTestSuite($this->suite);
        $this->printer->writeProgress();

        $this->assertEquals([
            'PHPUNIT_UNNAMED_SUITE::class',
            'MultiDependencyTest::class',
        ], $this->printer->getTestSuiteStack());
        $this->assertEquals([], $this->printer->getBuffer());

        // Simulate a test
        $this->runTestAndFlush($this->suite->tests()[0]);
        $this->assertEquals([
            "MultiDependencyTest::testOne\n",
        ], $this->printer->getBuffer());

        // End TestSuite with tests
        $this->printer->endTestSuite($this->suite);
        $this->printer->writeProgress();

        $this->assertEquals([
            'PHPUNIT_UNNAMED_SUITE::class',
        ], $this->printer->getTestSuiteStack());
        $this->assertEquals([
            "MultiDependencyTest::testOne\n",
        ], $this->printer->getBuffer());

        // End internal wrapper TestSuite
        $this->printer->endTestSuite($namelessSuite);
        $this->printer->writeProgress();

        $this->assertEquals([], $this->printer->getTestSuiteStack());
        $this->assertEquals([
            "MultiDependencyTest::testOne\n",
        ], $this->printer->getBuffer());
    }

    public function testEnabledOutputBufferDoesResequenceTestResults(): void
    {
        // Simulate running a non-default order with buffer on
        $this->printer->setEnableOutputBuffer(true);
        $lines = $this->getOutputLinesForTestOrder();

        // 1. testTwo, no output
        $this->runTestAndFlush($this->suite->tests()[1]);
        $this->assertEquals([], $this->printer->getBuffer());

        // 2. testOne, output [One, Two]
        $this->runTestAndFlush($this->suite->tests()[0]);
        $this->assertEquals(
            array_slice($lines, 0, 2),
            $this->printer->getBuffer()
        );

        // 3. testFive, no new output
        $this->runTestAndFlush($this->suite->tests()[4]);
        $this->assertEquals(
            array_slice($lines, 0, 2),
            $this->printer->getBuffer()
        );

        // 4. testFour, no new output
        $this->runTestAndFlush($this->suite->tests()[3]);
        $this->assertEquals(
            array_slice($lines, 0, 2),
            $this->printer->getBuffer()
        );

        // 5. testThree, output is complete now
        // The five test results are reported in the ORIGINAL execution order
        $this->runTestAndFlush($this->suite->tests()[2]);
        $this->assertEquals($lines, $this->printer->getBuffer());

        // Force flushing adds no further output
        $this->printer->flush();
        $this->assertEquals($lines, $this->printer->getBuffer());
    }

    public function testDisabledOutputBufferPrintsTestResultsOutOfOrder(): void
    {
        // Simulate running a non-default order with buffer on
        $this->printer->setEnableOutputBuffer(false);
        $lines = $this->getOutputLinesForTestOrder();

        // 1. testTwo
        $this->runTestAndFlush($this->suite->tests()[1]);
        $this->assertEquals([
            $lines[1],
        ], $this->printer->getBuffer());

        // 2. testOne
        $this->runTestAndFlush($this->suite->tests()[0]);
        $this->assertEquals([
            $lines[1],
            $lines[0],
        ], $this->printer->getBuffer());

        // 3. testFive
        $this->runTestAndFlush($this->suite->tests()[4]);
        $this->assertEquals([
            $lines[1],
            $lines[0],
            $lines[4],
        ], $this->printer->getBuffer());

        // 4. testFour
        $this->runTestAndFlush($this->suite->tests()[3]);
        $this->assertEquals([
            $lines[1],
            $lines[0],
            $lines[4],
            $lines[3],
        ], $this->printer->getBuffer());

        // 5. testThree
        $this->runTestAndFlush($this->suite->tests()[2]);
        $this->assertEquals([
            $lines[1],
            $lines[0],
            $lines[4],
            $lines[3],
            $lines[2],
        ], $this->printer->getBuffer());

        // Force flushing adds no further output
        $this->printer->flush();
        $this->assertEquals([
            $lines[1],
            $lines[0],
            $lines[4],
            $lines[3],
            $lines[2],
        ], $this->printer->getBuffer());
    }

    public function testFormatTestWithException(): void
    {
        $testOne = $this->suite->tests()[0];
        $testTwo = $this->suite->tests()[1];

        $this->printer->startTest($testOne);
        $this->printer->addError($testOne, new Exception('elephant in the room'), 0.1);
        $this->printer->endTest($testOne, 0.1);

        $this->printer->startTest($testTwo);
        $this->printer->addError($testTwo, new Exception(), 0.1);
        $this->printer->endTest($testTwo, 0.1);

        $this->printer->flush();

        $this->assertMatchesRegularExpression(
            "/{$testOne->sortId()}\s*^\s+│ Exception: elephant in the room\s+^/m",
            $this->printer->getBuffer()[0]
        );

        $this->assertMatchesRegularExpression(
            "/{$testTwo->sortId()}\s*^\s+│ Exception:\s+^/m",
            $this->printer->getBuffer()[1]
        );
    }

    /**
     * @testdox printResult() has no default output
     */
    public function testPrintResultHasNoDefaultOutput(): void
    {
        $result = new TestResult;
        $this->suite->run($result);
        $this->printer->printResult($result);

        $this->assertEquals([], $this->printer->getBuffer());
    }

    /**
     * @testdox Printer ignores non-TestCase
     */
    public function testHandlesNonTestcaseTests(): void
    {
        $test = new NotReorderableTest;

        $this->printer->startTest($test);
        $this->printer->endTest($test, 0.1);

        $this->printer->startTest($this->suite->tests()[0]);
        $this->printer->endTest($this->suite->tests()[0], 0.1);

        $this->printer->flush();

        $this->assertEquals([
            $this->suite->tests()[0]->sortId() . "\n",
        ], $this->printer->getBuffer());
    }

    private function runTestAndFlush(Test $test): void
    {
        $this->printer->startTest($test);
        $this->printer->endTest($test, 0.1);
        $this->printer->writeProgress();
    }

    /**
     * @param null|array<string> $testNames
     *
     * @return array<string>
     */
    private function getOutputLinesForTestOrder(?array $testNames = null): array
    {
        if ($testNames === null) {
            $testNames = $this->originalOrder;
        }

        return array_map(static function (string $t): string {
            return "{$t}\n";
        }, $testNames);
    }
}
