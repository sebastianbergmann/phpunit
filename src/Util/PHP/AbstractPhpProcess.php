<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use const DIRECTORY_SEPARATOR;
use const PHP_SAPI;
use function array_keys;
use function array_merge;
use function assert;
use function escapeshellarg;
use function ini_get_all;
use function restore_error_handler;
use function set_error_handler;
use function str_replace;
use function str_starts_with;
use function substr;
use function trim;
use function unserialize;
use ErrorException;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Event\TestData\MoreThanOneDataSetFromDataProviderException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use SebastianBergmann\Environment\Runtime;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class AbstractPhpProcess
{
    protected Runtime $runtime;
    protected bool $stderrRedirection = false;
    protected string $stdin           = '';
    protected string $arguments       = '';

    /**
     * @psalm-var array<string, string>
     */
    protected array $env   = [];
    protected int $timeout = 0;

    public static function factory(): self
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return new WindowsPhpProcess;
        }

        return new DefaultPhpProcess;
    }

    public function __construct()
    {
        $this->runtime = new Runtime;
    }

    /**
     * Defines if should use STDERR redirection or not.
     *
     * Then $stderrRedirection is TRUE, STDERR is redirected to STDOUT.
     */
    public function setUseStderrRedirection(bool $stderrRedirection): void
    {
        $this->stderrRedirection = $stderrRedirection;
    }

    /**
     * Returns TRUE if uses STDERR redirection or FALSE if not.
     */
    public function useStderrRedirection(): bool
    {
        return $this->stderrRedirection;
    }

    /**
     * Sets the input string to be sent via STDIN.
     */
    public function setStdin(string $stdin): void
    {
        $this->stdin = $stdin;
    }

    /**
     * Returns the input string to be sent via STDIN.
     */
    public function getStdin(): string
    {
        return $this->stdin;
    }

    /**
     * Sets the string of arguments to pass to the php job.
     */
    public function setArgs(string $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Returns the string of arguments to pass to the php job.
     */
    public function getArgs(): string
    {
        return $this->arguments;
    }

    /**
     * Sets the array of environment variables to start the child process with.
     *
     * @psalm-param array<string, string> $env
     */
    public function setEnv(array $env): void
    {
        $this->env = $env;
    }

    /**
     * Returns the array of environment variables to start the child process with.
     */
    public function getEnv(): array
    {
        return $this->env;
    }

    /**
     * Sets the amount of seconds to wait before timing out.
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Returns the amount of seconds to wait before timing out.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Runs a single test in a separate PHP process.
     *
     * @throws \PHPUnit\Runner\Exception
     * @throws Exception
     * @throws MoreThanOneDataSetFromDataProviderException
     * @throws NoPreviousThrowableException
     */
    public function runTestJob(string $job, Test $test): void
    {
        $_result = $this->runJob($job);

        $this->processChildResult(
            $test,
            $_result['stdout'],
            $_result['stderr']
        );
    }

    /**
     * Returns the command based into the configurations.
     */
    public function getCommand(array $settings, string $file = null): string
    {
        $command = $this->runtime->getBinary();

        if ($this->runtime->hasPCOV()) {
            $settings = array_merge(
                $settings,
                $this->runtime->getCurrentSettings(
                    array_keys(ini_get_all('pcov'))
                )
            );
        } elseif ($this->runtime->hasXdebug()) {
            $settings = array_merge(
                $settings,
                $this->runtime->getCurrentSettings(
                    array_keys(ini_get_all('xdebug'))
                )
            );
        }

        $command .= $this->settingsToParameters($settings);

        if (PHP_SAPI === 'phpdbg') {
            $command .= ' -qrr';

            if (!$file) {
                $command .= 's=';
            }
        }

        if ($file) {
            $command .= ' ' . escapeshellarg($file);
        }

        if ($this->arguments) {
            if (!$file) {
                $command .= ' --';
            }
            $command .= ' ' . $this->arguments;
        }

        if ($this->stderrRedirection) {
            $command .= ' 2>&1';
        }

        return $command;
    }

    /**
     * Runs a single job (PHP code) using a separate PHP process.
     */
    abstract public function runJob(string $job, array $settings = []): array;

    protected function settingsToParameters(array $settings): string
    {
        $buffer = '';

        foreach ($settings as $setting) {
            $buffer .= ' -d ' . escapeshellarg($setting);
        }

        return $buffer;
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws Exception
     * @throws MoreThanOneDataSetFromDataProviderException
     * @throws NoPreviousThrowableException
     */
    private function processChildResult(Test $test, string $stdout, string $stderr): void
    {
        if (!empty($stderr)) {
            $exception = new Exception(trim($stderr));

            assert($test instanceof TestCase);

            Facade::emitter()->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception)
            );

            return;
        }

        set_error_handler(
            /**
             * @throws ErrorException
             */
            static function (int $errno, string $errstr, string $errfile, int $errline): never
            {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            }
        );

        try {
            if (str_starts_with($stdout, "#!/usr/bin/env php\n")) {
                $stdout = substr($stdout, 19);
            }

            $childResult = unserialize(str_replace("#!/usr/bin/env php\n", '', $stdout));
            restore_error_handler();

            if ($childResult === false) {
                $exception = new AssertionFailedError('Test was run in child process and ended unexpectedly');

                assert($test instanceof TestCase);

                Facade::emitter()->testErrored(
                    TestMethodBuilder::fromTestCase($test),
                    ThrowableBuilder::from($exception)
                );

                Facade::emitter()->testFinished(
                    TestMethodBuilder::fromTestCase($test),
                    0
                );
            }
        } catch (ErrorException $e) {
            restore_error_handler();
            $childResult = false;

            $exception = new Exception(trim($stdout), 0, $e);

            assert($test instanceof TestCase);

            Facade::emitter()->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception)
            );
        }

        if ($childResult !== false) {
            if (!empty($childResult['output'])) {
                $output = $childResult['output'];
            }

            Facade::instance()->forward($childResult['events']);
            PassedTests::instance()->import($childResult['passedTests']);

            assert($test instanceof TestCase);

            $test->setResult($childResult['testResult']);
            $test->addToAssertionCount($childResult['numAssertions']);

            if (CodeCoverage::instance()->isActive() && $childResult['codeCoverage'] instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
                CodeCoverage::instance()->codeCoverage()->merge(
                    $childResult['codeCoverage']
                );
            }
        }

        if (!empty($output)) {
            print $output;
        }
    }
}
