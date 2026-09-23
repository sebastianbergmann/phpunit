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
 * An extension that is bootstrapped in the child process of a test that runs
 * in a separate process as well as in the main process.
 *
 * A test that runs in a separate process (#[RunInSeparateProcess],
 * #[RunTestsInSeparateProcesses], --process-isolation, or
 * processIsolation="true") executes in a child process, and the main process
 * only replays the events it produces once the child process has finished.
 * An extension that has to act inside the process that runs the test — one
 * that wraps every test in a database transaction, for instance — therefore
 * implements this interface: bootstrapChildProcess() is called in the child
 * process before the test runs, and the subscribers it registers receive the
 * events of the test live, inside the child process.
 *
 * The main process bootstraps the extension through bootstrap() as it does
 * for any other extension, and the subscribers registered there receive every
 * event of the test run, including the replayed events of the tests that ran
 * in a separate process.
 *
 * No test runner lifecycle event is emitted inside a child process: what an
 * extension would do when the test runner starts, it does in
 * bootstrapChildProcess(), and what it would do when the test runner
 * finishes, it does in shutdownChildProcess().
 *
 * shutdownChildProcess() is called on the same instance, after the test has
 * run and before the child process reports its result to the main process,
 * when bootstrapChildProcess() succeeded. It is not called when the child
 * process dies before that, for instance because the test calls exit() or
 * triggers a fatal error. What it prints is not shown.
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
