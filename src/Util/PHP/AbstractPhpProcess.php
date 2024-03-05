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
use function file_exists;
use function file_get_contents;
use function ini_get_all;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function str_replace;
use function strpos;
use function strrpos;
use function substr;
use function trim;
use function unlink;
use function unserialize;
use __PHP_Incomplete_Class;
use ErrorException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\SyntheticError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class AbstractPhpProcess
{
    /**
     * @var Runtime
     */
    protected $runtime;

    /**
     * @var bool
     */
    protected $stderrRedirection = false;

    /**
     * @var string
     */
    protected $stdin = '';

    /**
     * @var string
     */
    protected $args = '';

    /**
     * @var array<string, string>
     */
    protected $env = [];

    /**
     * @var int
     */
    protected $timeout = 0;

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
    public function setArgs(string $args): void
    {
        $this->args = $args;
    }

    /**
     * Returns the string of arguments to pass to the php job.
     */
    public function getArgs(): string
    {
        return $this->args;
    }

    /**
     * Sets the array of environment variables to start the child process with.
     *
     * @param array<string, string> $env
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
     * @throws InvalidArgumentException
     */
    public function runTestJob(string $job, Test $test, TestResult $result, string $processResultFile): void
    {
        $result->startTest($test);

        $processResult = '';
        $_result       = $this->runJob($job);

        if (file_exists($processResultFile)) {
            $processResult = file_get_contents($processResultFile);

            @unlink($processResultFile);
        }

        $this->processChildResult(
            $test,
            $result,
            $processResult,
            $_result['stderr'],
        );
    }

    /**
     * Returns the command based into the configurations.
     */
    public function getCommand(array $settings, ?string $file = null): string
    {
        $command = $this->runtime->getBinary();

        if ($this->runtime->hasPCOV()) {
            $settings = array_merge(
                $settings,
                $this->runtime->getCurrentSettings(
                    array_keys(ini_get_all('pcov')),
                ),
            );
        } elseif ($this->runtime->hasXdebug()) {
            $settings = array_merge(
                $settings,
                $this->runtime->getCurrentSettings(
                    array_keys(ini_get_all('xdebug')),
                ),
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

        if ($this->args) {
            if (!$file) {
                $command .= ' --';
            }
            $command .= ' ' . $this->args;
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
     * Processes the TestResult object from an isolated process.
     *
     * @throws InvalidArgumentException
     */
    private function processChildResult(Test $test, TestResult $result, string $stdout, string $stderr): void
    {
        $time = 0;

        if (!empty($stderr)) {
            $result->addError(
                $test,
                new Exception(trim($stderr)),
                $time,
            );
        } else {
            set_error_handler(
                /**
                 * @throws ErrorException
                 */
                static function ($errno, $errstr, $errfile, $errline): void
                {
                    throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
                },
            );

            try {
                if (strpos($stdout, "#!/usr/bin/env php\n") === 0) {
                    $stdout = substr($stdout, 19);
                }

                $childResult = unserialize(str_replace("#!/usr/bin/env php\n", '', $stdout));
                restore_error_handler();

                if ($childResult === false) {
                    $result->addFailure(
                        $test,
                        new AssertionFailedError('Test was run in child process and ended unexpectedly'),
                        $time,
                    );
                }
            } catch (ErrorException $e) {
                restore_error_handler();
                $childResult = false;

                $result->addError(
                    $test,
                    new Exception(trim($stdout), 0, $e),
                    $time,
                );
            }

            if ($childResult !== false) {
                if (!empty($childResult['output'])) {
                    $output = $childResult['output'];
                }

                /* @var TestCase $test */

                $test->setResult($childResult['testResult']);
                $test->addToAssertionCount($childResult['numAssertions']);

                $childResult = $childResult['result'];
                assert($childResult instanceof TestResult);

                if ($result->getCollectCodeCoverageInformation()) {
                    $result->getCodeCoverage()->merge(
                        $childResult->getCodeCoverage(),
                    );
                }

                $time           = $childResult->time();
                $notImplemented = $childResult->notImplemented();
                $risky          = $childResult->risky();
                $skipped        = $childResult->skipped();
                $errors         = $childResult->errors();
                $warnings       = $childResult->warnings();
                $failures       = $childResult->failures();

                if (!empty($notImplemented)) {
                    $result->addError(
                        $test,
                        $this->getException($notImplemented[0]),
                        $time,
                    );
                } elseif (!empty($risky)) {
                    $result->addError(
                        $test,
                        $this->getException($risky[0]),
                        $time,
                    );
                } elseif (!empty($skipped)) {
                    $result->addError(
                        $test,
                        $this->getException($skipped[0]),
                        $time,
                    );
                } elseif (!empty($errors)) {
                    $result->addError(
                        $test,
                        $this->getException($errors[0]),
                        $time,
                    );
                } elseif (!empty($warnings)) {
                    $result->addWarning(
                        $test,
                        $this->getException($warnings[0]),
                        $time,
                    );
                } elseif (!empty($failures)) {
                    $result->addFailure(
                        $test,
                        $this->getException($failures[0]),
                        $time,
                    );
                }
            }
        }

        $result->endTest($test, $time);

        if (!empty($output)) {
            print $output;
        }
    }

    /**
     * Gets the thrown exception from a PHPUnit\Framework\TestFailure.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/74
     */
    private function getException(TestFailure $error): Exception
    {
        $exception = $error->thrownException();

        if ($exception instanceof __PHP_Incomplete_Class) {
            $exceptionArray = [];

            foreach ((array) $exception as $key => $value) {
                $key                  = substr($key, strrpos($key, "\0") + 1);
                $exceptionArray[$key] = $value;
            }

            $exception = new SyntheticError(
                sprintf(
                    '%s: %s',
                    $exceptionArray['_PHP_Incomplete_Class_Name'],
                    $exceptionArray['message'],
                ),
                $exceptionArray['code'],
                $exceptionArray['file'],
                $exceptionArray['line'],
                $exceptionArray['trace'],
            );
        }

        return $exception;
    }
}
