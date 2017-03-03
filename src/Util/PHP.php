<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use __PHP_Incomplete_Class;
use ErrorException;
use SebastianBergmann\Environment\Runtime;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\SyntheticError;

/**
 * Default utility for PHP sub-processes.
 */
class PHP
{
    /**
     * @var string
     */
    protected $tempFile;

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
     * @var array
     */
    protected $env = [];

    /**
     * @var int
     */
    protected $timeout = 0;

    /**
     * Creates internal Runtime instance.
     */
    public function __construct()
    {
        $this->runtime = new Runtime();
    }

        /**
     * Defines if should use STDERR redirection or not.
     *
     * Then $stderrRedirection is TRUE, STDERR is redirected to STDOUT.
     *
     * @throws Exception
     *
     * @param bool $stderrRedirection
     */
    public function setUseStderrRedirection($stderrRedirection)
    {
        if (!is_bool($stderrRedirection)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stderrRedirection = $stderrRedirection;
    }

    /**
     * Returns TRUE if uses STDERR redirection or FALSE if not.
     *
     * @return bool
     */
    public function useStderrRedirection()
    {
        return $this->stderrRedirection;
    }

    /**
     * Sets the input string to be sent via STDIN
     *
     * @param string $stdin
     */
    public function setStdin($stdin)
    {
        $this->stdin = (string) $stdin;
    }

    /**
     * Returns the input string to be sent via STDIN
     *
     * @return string
     */
    public function getStdin()
    {
        return $this->stdin;
    }

    /**
     * Sets the string of arguments to pass to the php job
     *
     * @param string $args
     */
    public function setArgs($args)
    {
        $this->args = (string) $args;
    }

    /**
     * Returns the string of arguments to pass to the php job
     *
     * @retrun string
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Sets the array of environment variables to start the child process with
     *
     * @param array $env
     */
    public function setEnv(array $env)
    {
        $this->env = $env;
    }

    /**
     * Returns the array of environment variables to start the child process with
     *
     * @return array
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Sets the amount of seconds to wait before timing out
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
    }

    /**
     * Returns the amount of seconds to wait before timing out
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return \PHPUnit\Util\PHP
     */
    public static function factory()
    {
        return new static();
    }

    /**
     * Runs a single test in a separate PHP process.
     *
     * @param string     $job
     * @param Test       $test
     * @param TestResult $result
     *
     * @throws Exception
     */
    public function runTestJob($job, Test $test, TestResult $result)
    {
        $result->startTest($test);

        $_result = $this->runJob($job);

        $this->processChildResult(
            $test,
            $result,
            $_result['stdout'],
            $_result['stderr']
        );
    }

    /**
     * Returns the command based into the configurations.
     *
     * @param array  $settings
     * @param string $file
     *
     * @return string
     */
    public function getCommand(array $settings, $file)
    {
        $command = $this->runtime->getBinary();
        $command .= $this->settingsToParameters($settings);

        if ('phpdbg' === PHP_SAPI) {
            $command .= ' -qrr ';

            $command .= '-e ' . escapeshellarg($file);
        } else {
            $command .= ' -f ' . escapeshellarg($file);
        }

        if ($this->args) {
            $command .= ' -- ' . $this->args;
        }

        if (true === $this->stderrRedirection) {
            $command .= ' 2>&1';
        }

        // Special case windows.
        if (DIRECTORY_SEPARATOR == '\\') {
            $command = '"' . $command . '"';
        }
      
        return $command;
    }

    /**
     * @param array $settings
     *
     * @return string
     */
    protected function settingsToParameters(array $settings)
    {
        $buffer = '';

        foreach ($settings as $setting) {
            $buffer .= ' -d ' . $setting;
        }

        return $buffer;
    }

    /**
     * Processes the TestResult object from an isolated process.
     *
     * @param Test       $test
     * @param TestResult $result
     * @param string     $stdout
     * @param string     $stderr
     */
    private function processChildResult(Test $test, TestResult $result, $stdout, $stderr)
    {
        $time = 0;

        if (!empty($stderr)) {
            $result->addError(
                $test,
                new Exception(trim($stderr)),
                $time
            );
        } else {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            });
            try {
                if (strpos($stdout, "#!/usr/bin/env php\n") === 0) {
                    $stdout = substr($stdout, 19);
                }

                $childResult = unserialize(str_replace("#!/usr/bin/env php\n", '', $stdout));
                restore_error_handler();
            } catch (ErrorException $e) {
                restore_error_handler();
                $childResult = false;

                $result->addError(
                    $test,
                    new Exception(trim($stdout), 0, $e),
                    $time
                );
            }

            if ($childResult !== false) {
                if (!empty($childResult['output'])) {
                    $output = $childResult['output'];
                }

                $test->setResult($childResult['testResult']);
                $test->addToAssertionCount($childResult['numAssertions']);

                $childResult = $childResult['result'];
                /* @var $childResult TestResult */

                if ($result->getCollectCodeCoverageInformation()) {
                    $result->getCodeCoverage()->merge(
                        $childResult->getCodeCoverage()
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
                        $time
                    );
                } elseif (!empty($risky)) {
                    $result->addError(
                        $test,
                        $this->getException($risky[0]),
                        $time
                    );
                } elseif (!empty($skipped)) {
                    $result->addError(
                        $test,
                        $this->getException($skipped[0]),
                        $time
                    );
                } elseif (!empty($errors)) {
                    $result->addError(
                        $test,
                        $this->getException($errors[0]),
                        $time
                    );
                } elseif (!empty($warnings)) {
                    $result->addWarning(
                        $test,
                        $this->getException($warnings[0]),
                        $time
                    );
                } elseif (!empty($failures)) {
                    $result->addFailure(
                        $test,
                        $this->getException($failures[0]),
                        $time
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
     * Gets the thrown exception from a PHPUnit_Framework_TestFailure.
     *
     * @param TestFailure $error
     *
     * @return Exception
     *
     * @see    https://github.com/sebastianbergmann/phpunit/issues/74
     */
    private function getException(TestFailure $error)
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
                    $exceptionArray['message']
                ),
                $exceptionArray['code'],
                $exceptionArray['file'],
                $exceptionArray['line'],
                $exceptionArray['trace']
            );
        }

        return $exception;
    }

    /**
     * Runs a single job (PHP code) using a separate PHP process.
     *
     * @param string $job
     * @param array  $settings
     *
     * @return array
     *
     * @throws Exception
     */
    public function runJob($job, array $settings = [])
    {
        if ($this->stdin) {
            if (!($this->tempFile = tempnam(sys_get_temp_dir(), 'PHPUnit')) ||
                file_put_contents($this->tempFile, $job) === false
            ) {
                throw new Exception(
                    'Unable to write temporary file'
                );
            }

            $job = $this->stdin;
        }

        return $this->runProcess($job, $settings);
    }

    /**
     * Returns an array of file handles to be used in place of pipes
     *
     * @return array
     */
    protected function getHandles()
    {
        return [];
    }

    /**
     * Handles creating the child process and returning the STDOUT and STDERR
     *
     * @param string $job
     * @param array  $settings
     *
     * @return array
     *
     * @throws Exception
     */
    protected function runProcess($job, $settings)
    {
        $handles = $this->getHandles();

        $env = null;
        if ($this->env) {
            $env = isset($_SERVER) ? $_SERVER : [];
            unset($env['argv'], $env['argc']);
            $env = array_merge($env, $this->env);

            foreach ($env as $envKey => $envVar) {
                if (is_array($envVar)) {
                    unset($env[$envKey]);
                }
            }
        }

        $pipeSpec = [
            0 => isset($handles[0]) ? $handles[0] : ['pipe', 'r'],
            1 => isset($handles[1]) ? $handles[1] : ['pipe', 'w'],
            2 => isset($handles[2]) ? $handles[2] : ['pipe', 'w'],
        ];
        $process = proc_open(
            $this->getCommand($settings, $this->tempFile),
            $pipeSpec,
            $pipes,
            null,
            $env
        );

        if (!is_resource($process)) {
            throw new Exception(
                'Unable to spawn worker process'
            );
        }

        if ($job) {
            $this->process($pipes[0], $job);
        }
        fclose($pipes[0]);

        if ($this->timeout) {
            $stderr = $stdout = '';
            unset($pipes[0]);

            while (true) {
                $r = $pipes;
                $w = null;
                $e = null;

                $n = @stream_select($r, $w, $e, $this->timeout);

                if ($n === false) {
                    break;
                } elseif ($n === 0) {
                    proc_terminate($process, 9);
                    throw new Exception(sprintf('Job execution aborted after %d seconds', $this->timeout));
                } elseif ($n > 0) {
                    foreach ($r as $pipe) {
                        $pipeOffset = 0;
                        foreach ($pipes as $i => $origPipe) {
                            if ($pipe == $origPipe) {
                                $pipeOffset = $i;
                                break;
                            }
                        }

                        if (!$pipeOffset) {
                            break;
                        }

                        $line = fread($pipe, 8192);
                        if (strlen($line) == 0) {
                            fclose($pipes[$pipeOffset]);
                            unset($pipes[$pipeOffset]);
                        } else {
                            if ($pipeOffset == 1) {
                                $stdout .= $line;
                            } else {
                                $stderr .= $line;
                            }
                        }
                    }

                    if (empty($pipes)) {
                        break;
                    }
                }
            }
        } else {
            if (isset($pipes[1])) {
                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
            }

            if (isset($pipes[2])) {
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
            }
        }

        if (isset($handles[1])) {
            rewind($handles[1]);
            $stdout = stream_get_contents($handles[1]);
            fclose($handles[1]);
        }

        if (isset($handles[2])) {
            rewind($handles[2]);
            $stderr = stream_get_contents($handles[2]);
            fclose($handles[2]);
        }

        proc_close($process);
        $this->cleanup();

        return ['stdout' => $stdout, 'stderr' => $stderr];
    }

    /**
     * @param resource $pipe
     * @param string   $job
     *
     * @throws Exception
     */
    protected function process($pipe, $job)
    {
        fwrite($pipe, $job);
    }

    /**
     */
    protected function cleanup()
    {
        if ($this->tempFile) {
            unlink($this->tempFile);
        }
    }
}
