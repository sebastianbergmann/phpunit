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
 * An extension that is bootstrapped in the worker processes of a parallel
 * test run as well as in the main process.
 *
 * In a parallel test run, the tests execute in worker processes, and the main
 * process only replays the events they produce. An extension that has to act
 * inside the process that runs the tests — one that wraps every test in a
 * database transaction, for instance — therefore implements this interface:
 * bootstrapWorker() is called once in every worker process, before the worker
 * runs its first test, and the subscribers it registers receive the events of
 * the tests the worker runs, live, inside the worker.
 *
 * The main process bootstraps the extension through bootstrap() as it does in
 * a sequential run, and the subscribers registered there receive every event
 * of the run — replayed, for the tests that ran in a worker. An extension
 * that acts in both places has to decide, in bootstrap(), what its main
 * process subscribers still do when the run is a parallel one; the
 * configuration tells it how many workers the run uses.
 *
 * The worker's identity is available to bootstrapWorker() through the
 * environment variables PHPUNIT_WORKER_ID and PHPUNIT_WORKER_TOKEN. No test
 * runner lifecycle event is emitted inside a worker: what an extension would
 * do when the test runner starts, it does in bootstrapWorker(), and what it
 * would do when the test runner finishes, it does in shutdownWorker().
 *
 * shutdownWorker() is called once in every worker process whose
 * bootstrapWorker() succeeded, on the same instance, after the worker has run
 * its last unit and before it exits: at the end of the test run, and when the
 * worker is replaced with a fresh process because it has run the configured
 * number of test classes. It is not called when the worker process dies or
 * is terminated: when it crashes, when a test runs into the timeout, or when
 * the test run is stopped early (--stop-on-*) while the worker is running a
 * unit. No subscriber receives an event while it runs, and what it prints is
 * not shown. A failure is reported with a test runner warning.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface ParallelWorkerExtension extends Extension
{
    public function bootstrapWorker(Configuration $configuration, WorkerFacade $facade, ParameterCollection $parameters): void;

    public function shutdownWorker(): void;
}
