<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function file_get_contents;
use function file_put_contents;
use function function_exists;
use function get_included_files;
use function hrtime;
use function in_array;
use function ini_get;
use function pcntl_fork;
use function pcntl_waitpid;
use function serialize;
use function str_contains;
use function sys_get_temp_dir;
use function tempnam;
use Exception;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PcntlForkJobRunner implements IsolatedTestRunner
{
    public function __construct(private ChildProcessResultProcessor $processor)
    {
    }

    public function run(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): void
    {
        $processResultFile = tempnam(sys_get_temp_dir(), 'phpunit_');

        $pid = pcntl_fork();

        if ($pid === -1) {
            // @codeCoverageIgnoreStart
            throw new Exception('could not fork');
            // @codeCoverageIgnoreEnd
        }

        if ($pid !== 0) {
            // we are the parent
            Facade::emitter()->childProcessStarted();

            pcntl_waitpid($pid, $status);

            $this->processor->process($test, file_get_contents($processResultFile), '');

            EventFacade::emitter()->childProcessFinished('', '');

            return;
        }

        // we are the child, run the test

        $offset     = hrtime();
        $dispatcher = Facade::instance()->initForIsolation(
            HRTime::fromSecondsAndNanoseconds(
                $offset[0],
                $offset[1],
            ),
        );

        $test->setInIsolation(true);
        $test->run();

        file_put_contents(
            $processResultFile,
            serialize(
                (object) [
                    'testResult'    => $test->result(),
                    'codeCoverage'  => CodeCoverage::instance()->isActive() ? CodeCoverage::instance()->codeCoverage() : null,
                    'numAssertions' => $test->numberOfAssertionsPerformed(),
                    'output'        => !$test->expectsOutput() ? $test->output() : '',
                    'events'        => $dispatcher->flush(),
                    'passedTests'   => PassedTests::instance(),
                ],
            ),
        );

        exit();
    }

    public function canRun(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): bool
    {
        if (!$this->isPcntlForkAvailable()) {
            return false;
        }

        // we support bootstrap files only if they have been already included in the parent process
        // as we cannot require a file and load it into the global scope from within a forked process.
        if (ConfigurationRegistry::get()->hasBootstrap()) {
            if (!in_array(ConfigurationRegistry::get()->bootstrap(), get_included_files(), true)) {
                return false;
            }
        }

        return !$runEntireClass &&
            !$preserveGlobalState;
    }

    private function isPcntlForkAvailable(): bool
    {
        $disabledFunctions = ini_get('disable_functions');

        return
            function_exists('pcntl_fork') &&
            function_exists('pcntl_waitpid') &&
            !str_contains($disabledFunctions, 'pcntl');
    }
}
