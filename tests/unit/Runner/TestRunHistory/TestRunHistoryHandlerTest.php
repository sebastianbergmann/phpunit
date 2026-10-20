<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use const DIRECTORY_SEPARATOR;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PropertyFalsified;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestStatus\TestStatus;

#[CoversClass(TestRunHistoryHandler::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-run-history')]
final class TestRunHistoryHandlerTest extends AbstractEventTestCase
{
    public function testMarkedIncompleteRecordsIncompleteStatus(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test  = $this->testValueObject();
        $event = new MarkedIncomplete(
            $this->telemetryInfo(),
            $test,
            ThrowableBuilder::from(new Exception('not yet implemented')),
        );

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $handler->testMarkedIncomplete($event);

        $id = TestRunHistoryId::fromTest($test);

        $this->assertTrue($cache->status($id)->isIncomplete());
    }

    public function testConsideredRiskyRecordsRiskyStatus(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test  = $this->testValueObject();
        $event = new ConsideredRisky(
            $this->telemetryInfo(),
            $test,
            'This test did not perform any assertions',
        );

        $handler->testConsideredRisky($event);

        $id = TestRunHistoryId::fromTest($test);

        $this->assertTrue($cache->status($id)->isRisky());
    }

    public function testSkippedRecordsSkippedStatusAndTime(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));

        $event = new Skipped(
            $this->telemetryInfo(),
            $test,
            'skipped for now',
        );

        $handler->testSkipped($event);

        $id = TestRunHistoryId::fromTest($test);

        $this->assertTrue($cache->status($id)->isSkipped());
    }

    public function testPassedRemovesStatusFromPreviousRun(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $cache->setStatus($id, TestStatus::failure('failed in previous run'));

        $handler->testPassed(new Passed($this->telemetryInfo(), $test));

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testPropertyFalsifiedRemembersTheChoiceSequenceOfTheMinimalCounterexample(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();

        $handler->testPropertyFalsified(
            new PropertyFalsified($this->telemetryInfo(), $test, 1234, 2, 3, 1, false, false, [0, 11], ['a' => '0', 'b' => '11'], null, [], [], 0, 0),
        );

        $this->assertSame([[0, 11]], $cache->counterexamples(TestRunHistoryId::fromTest($test)));
    }

    public function testPropertyFalsifiedRemembersTheChoiceSequencesOfTheCounterexamplesOfTheOtherWaysInWhichThePropertyWasFalsifiedAfterTheOneOfTheMinimalCounterexample(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();

        $handler->testPropertyFalsified(
            new PropertyFalsified(
                $this->telemetryInfo(),
                $test,
                1234,
                2,
                3,
                1,
                false,
                false,
                [0, 11],
                ['a' => '0', 'b' => '11'],
                null,
                [],
                [
                    ['description' => 'RuntimeException at FooTest.php:12', 'choiceSequence' => [3, 4], 'counterexample' => ['a' => '3', 'b' => '4'], 'drawnValues' => []],
                    ['description' => 'LogicException at FooTest.php:15', 'choiceSequence' => [5], 'counterexample' => ['a' => '5', 'b' => '0'], 'drawnValues' => []],
                ],
                0,
                0,
            ),
        );

        $this->assertSame([[0, 11], [3, 4], [5]], $cache->counterexamples(TestRunHistoryId::fromTest($test)));
    }

    public function testPropertyFalsifiedByAnExplicitExampleKeepsTheCounterexampleThatWasRememberedForTheProperty(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $cache->setCounterexamples($id, [[0, 11]]);

        $handler->testPropertyFalsified(
            new PropertyFalsified($this->telemetryInfo(), $test, 1234, 0, 0, 0, false, false, [], ['a' => '1', 'b' => '2'], 'one and two', [], [], 0, 0),
        );

        $this->assertSame([[0, 11]], $cache->counterexamples($id));
    }

    public function testPassedForgetsTheCounterexampleThatWasRememberedForAProperty(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $cache->setCounterexamples($id, [[0, 11]]);

        $handler->testPassed(new Passed($this->telemetryInfo(), $test));

        $this->assertSame([], $cache->counterexamples($id));
    }

    public function testPassedDoesNotForgetCounterexamplesForATestForWhichNoneWereRemembered(): void
    {
        $testRunHistory = $this->createMock(TestRunHistory::class);

        $testRunHistory->method('counterexamples')->willReturn([]);
        $testRunHistory->expects($this->never())->method('removeCounterexamples');

        $handler = new TestRunHistoryHandler($testRunHistory, new Facade, false);

        $handler->testPassed(new Passed($this->telemetryInfo(), $this->testValueObject()));
    }

    public function testPassedDoesNotRemoveStatusRecordedInCurrentRun(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();

        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('failed repetition')),
                null,
            ),
        );

        $handler->testPassed(new Passed($this->telemetryInfo(), $test));

        $this->assertTrue($cache->status(TestRunHistoryId::fromTest($test))->isFailure());
    }

    public function testStatusFromCurrentRunReplacesStatusFromPreviousRun(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $cache->setStatus($id, TestStatus::failure('failed in previous run'));

        $handler->testConsideredRisky(
            new ConsideredRisky(
                $this->telemetryInfo(),
                $test,
                'This test did not perform any assertions',
            ),
        );

        $this->assertTrue($cache->status($id)->isRisky());
    }

    public function testLessImportantStatusDoesNotReplaceStatusRecordedInCurrentRun(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();

        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('assertion failed')),
                null,
            ),
        );

        $handler->testConsideredRisky(
            new ConsideredRisky(
                $this->telemetryInfo(),
                $test,
                'This test printed unexpected output',
            ),
        );

        $this->assertTrue($cache->status(TestRunHistoryId::fromTest($test))->isFailure());
    }

    public function testMoreImportantStatusReplacesStatusRecordedInCurrentRun(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();

        $handler->testConsideredRisky(
            new ConsideredRisky(
                $this->telemetryInfo(),
                $test,
                'This test did not perform any assertions',
            ),
        );

        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('assertion failed')),
                null,
            ),
        );

        $this->assertTrue($cache->status(TestRunHistoryId::fromTest($test))->isFailure());
    }

    public function testPersistsWithPruningWhenPruningIsEnabled(): void
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-prune-' . uniqid() . '.cache';

        $staleId = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testGone');

        $seed = new DefaultTestRunHistory($file);
        $seed->setStatus($staleId, TestStatus::failure('failed before it was deleted'));
        $seed->persist();

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $handler = new TestRunHistoryHandler($cache, new Facade, true);

        $test = $this->testValueObject();

        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('assertion failed')),
                null,
            ),
        );
        $handler->testRunnerExecutionFinished();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($staleId)->isUnknown());
        $this->assertTrue($loaded->status(TestRunHistoryId::fromTest($test))->isFailure());

        @unlink($file);
    }

    public function testDoesNotPruneWhenExecutionWasAborted(): void
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-prune-' . uniqid() . '.cache';

        $staleId = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testNotReached');

        $seed = new DefaultTestRunHistory($file);
        $seed->setStatus($staleId, TestStatus::failure('failed in previous run'));
        $seed->persist();

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $handler = new TestRunHistoryHandler($cache, new Facade, true);

        $test = $this->testValueObject();

        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('assertion failed')),
                null,
            ),
        );
        $handler->testRunnerExecutionAborted();
        $handler->testRunnerExecutionFinished();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($staleId)->isFailure());
        $this->assertTrue($loaded->status(TestRunHistoryId::fromTest($test))->isFailure());

        @unlink($file);
    }

    public function testSkippedWithoutPreparedDoesNotRecordDuration(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test  = $this->testValueObject();
        $event = new Skipped(
            $this->telemetryInfo(),
            $test,
            'skipped without prepare',
        );

        $handler->testSkipped($event);

        $id = TestRunHistoryId::fromTest($test);

        $this->assertTrue($cache->status($id)->isSkipped());
        $this->assertSame(0.0, $cache->time($id));
    }

    public function testSkippedDoesNotOverwriteTimeFromPreviousRun(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $cache->setTime($id, 5.0);

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $handler->testSkipped(new Skipped($this->telemetryInfo(), $test, 'not applicable'));
        $handler->testFinished(new Finished($this->telemetryInfo(), $test, 0));

        $this->assertSame(5.0, $cache->time($id));
    }

    public function testFinishedRecordsTimeWhenTestWasNotSkipped(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $cache->setTime($id, 5.0);

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $handler->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $this->assertLessThan(5.0, $cache->time($id));
    }

    public function testFinishedRecordsTimeForTestRunAfterSkippedTest(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $handler->testSkipped(new Skipped($this->telemetryInfo(), $test, 'not applicable'));
        $handler->testFinished(new Finished($this->telemetryInfo(), $test, 0));

        $cache->setTime($id, 5.0);

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $handler->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $this->assertLessThan(5.0, $cache->time($id));
    }

    public function testFinishedWithoutPreparedRecordsZeroDuration(): void
    {
        $cache   = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new TestRunHistoryHandler($cache, new Facade, false);

        $test = $this->testValueObject();
        $id   = TestRunHistoryId::fromTest($test);

        $handler->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $this->assertSame(0.0, $cache->time($id));
    }
}
