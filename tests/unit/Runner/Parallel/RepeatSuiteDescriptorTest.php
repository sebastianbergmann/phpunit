<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(RepeatSuiteDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
#[Small]
final class RepeatSuiteDescriptorTest extends TestCase
{
    public function testRebuildsTheSuiteUnderTheNameItWasDescribedWith(): void
    {
        $suite = $this->rebuild($this->suite());

        $this->assertInstanceOf(RepeatTestSuite::class, $suite);
        $this->assertSame(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter', $suite->name());
    }

    public function testRebuildsTheNumberOfFailuresTheRepetitionsAreAllowed(): void
    {
        $this->assertSame(2, $this->rebuild($this->suite())->failureThreshold());
    }

    public function testRebuildsEveryRepetitionInTheOrderItWasDescribedIn(): void
    {
        // Unlike the attempts of a retried test method, every repetition is
        // described: the worker runs all of them, up to the point where the
        // failure threshold is exceeded.
        $tests = $this->rebuild($this->suite())->tests();

        $this->assertCount(3, $tests);

        foreach ($tests as $position => $test) {
            $this->assertInstanceOf(WorkerFirstTest::class, $test);
            $this->assertSame('testStartsTheProcessLocalCounter', $test->name());
            $this->assertSame($position + 1, $test->repetition());
            $this->assertSame(3, $test->totalRepetitions());
        }
    }

    public function testCannotDescribeASuiteWhoseRepetitionsCarryDataThatCannotBeSerialized(): void
    {
        // A closure cannot be serialized, so a suite whose repetitions carry
        // one cannot be described for transport to a worker.
        $closure = static function (): void
        {
        };

        $repetition = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $repetition->setData(0, [$closure]);

        $suite = RepeatTestSuite::fromTests(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            [$repetition],
            1,
        );

        $this->expectException(WorkerException::class);

        RepeatSuiteDescriptor::fromTestSuite($suite, WorkerFirstTest::class);
    }

    private function suite(): RepeatTestSuite
    {
        $repetitions = [];

        for ($repetition = 1; $repetition <= 3; $repetition++) {
            $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

            $test->setRepetition($repetition, 3);

            $repetitions[] = $test;
        }

        return RepeatTestSuite::fromTests(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $repetitions,
            2,
        );
    }

    /**
     * Describe the suite as the parent process does and rebuild it as the
     * worker process does.
     */
    private function rebuild(RepeatTestSuite $suite): RepeatTestSuite
    {
        return RepeatSuiteDescriptor::fromTestSuite($suite, WorkerFirstTest::class)->test(WorkerFirstTest::class);
    }
}
