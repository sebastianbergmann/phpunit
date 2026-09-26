<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\TextUI\Configuration\Configuration;

/**
 * An extension that is bootstrapped in the child processes that run tests as
 * well as in the main process.
 *
 * Tests run in a child process when they run in a separate process
 * (#[RunInSeparateProcess], #[RunTestsInSeparateProcesses],
 * --process-isolation, or processIsolation="true") and when they run in the
 * worker processes of a parallel test run (--parallel). The main process only
 * replays the events that a child process produces. An extension that has to
 * act inside the process that runs a test — one that wraps every test in a
 * database transaction, for instance — therefore implements this interface:
 * bootstrapChildProcess() is called in the child process before it runs its
 * first test, and the subscribers it registers receive the events of the
 * tests that the child process runs, live, inside the child process.
 *
 * A separate process runs a single test, so bootstrapChildProcess() is called
 * once for every test that runs in a separate process. A worker process runs
 * many tests, so bootstrapChildProcess() is called once for every worker
 * process, and the extension's state carries over from one test to the next.
 * The identity of a worker process is available through the environment
 * variables PHPUNIT_WORKER_ID and PHPUNIT_WORKER_TOKEN, which are not set in
 * a separate process.
 *
 * The main process bootstraps the extension through bootstrap() as it does
 * for any other extension, and the subscribers registered there receive every
 * event of the test run, including the replayed events of the tests that ran
 * in a child process. A subscriber that has to act once per test, in the
 * process that runs it, is registered in both places: in the main process
 * with Facade::registerSubscriberForEventsOfThisProcess(), so that it is not
 * notified of the replayed events of the tests that ran in a child process,
 * and in the child processes with ChildProcessFacade::registerSubscriber(),
 * because a child process does not replay the events of another process.
 *
 * No test runner lifecycle event is emitted inside a child process: what an
 * extension would do when the test runner starts, it does in
 * bootstrapChildProcess(), and what it would do when the test runner
 * finishes, it does in shutdownChildProcess().
 *
 * shutdownChildProcess() is called on the same instance in every child
 * process whose bootstrapChildProcess() succeeded, after the child process
 * has run its last test and before it reports to the main process: in a
 * separate process, after its test has run; in a worker process, at the end
 * of the test run and when the worker is replaced with a fresh process
 * because it has run the configured number of test classes. It is not called
 * when the child process dies or is terminated before that: when a test calls
 * exit() or triggers a fatal error, when a test runs into the timeout, or
 * when the test run is stopped early (--stop-on-*) while a worker process is
 * running tests. No subscriber receives an event of a worker process while
 * it runs, and what it prints is not shown.
 *
 * A failure of bootstrapChildProcess() or shutdownChildProcess() is reported
 * with a test runner warning.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface ChildProcessExtension extends Extension
{
    public function bootstrapChildProcess(Configuration $configuration, ChildProcessFacade $facade, ParameterCollection $parameters): void;

    public function shutdownChildProcess(): void;
}
