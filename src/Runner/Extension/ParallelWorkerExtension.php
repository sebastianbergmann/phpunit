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
 * do when the test runner starts, it does in bootstrapWorker().
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface ParallelWorkerExtension extends Extension
{
    public function bootstrapWorker(Configuration $configuration, WorkerFacade $facade, ParameterCollection $parameters): void;
}
