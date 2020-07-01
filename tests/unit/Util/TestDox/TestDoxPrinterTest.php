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

use MultiDependencyTest;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
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
        $this->printer = null;
    }

    public function testEnabledOutputBufferDoesResequenceTestResults(): void
    {
        // Simulate running a non-default order with buffer on
        $this->printer->setEnableOutputBuffer(true);
        $lines = array_map(static function (string $t) {
            return "${t}\n";
        }, $this->originalOrder);

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

        // 4. testFive, no new output
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

    private function runTestAndFlush(Test $test): void
    {
        $this->printer->startTest($test);
        $this->printer->endTest($test, 0.1);
        $this->printer->writeProgress();
    }
}
