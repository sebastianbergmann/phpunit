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

        $handler->testSuiteStarted();
        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('assertion failed')),
                null,
            ),
        );
        $handler->testSuiteFinished();

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

        $handler->testSuiteStarted();
        $handler->testFailed(
            new Failed(
                $this->telemetryInfo(),
                $test,
                ThrowableBuilder::from(new Exception('assertion failed')),
                null,
            ),
        );
        $handler->testRunnerExecutionAborted();
        $handler->testSuiteFinished();

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
