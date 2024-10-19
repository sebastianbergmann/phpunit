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

use function array_merge;
use function fclose;
use function file_put_contents;
use function fread;
use function fwrite;
use function is_array;
use function is_resource;
use function proc_close;
use function proc_open;
use function proc_terminate;
use function sprintf;
use function stream_select;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Framework\Exception;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class DefaultPhpProcess extends AbstractPhpProcess
{
    private ?string $tempFile = null;

    /**
     * Runs a single job (PHP code) using a separate PHP process.
     *
     * @psalm-return array{stdout: string, stderr: string}
     *
     * @throws Exception
     * @throws PhpProcessException
     */
    public function runJob(string $job, array $settings = []): array
    {
        if ($this->stdin) {
            if (!($this->tempFile = tempnam(sys_get_temp_dir(), 'phpunit_')) ||
                file_put_contents($this->tempFile, $job) === false) {
                throw new PhpProcessException(
                    'Unable to write temporary file',
                );
            }

            $job = $this->stdin;
        }

        return $this->runProcess($job, $settings);
    }

    /**
     * Handles creating the child process and returning the STDOUT and STDERR.
     *
     * @psalm-return array{stdout: string, stderr: string}
     *
     * @throws Exception
     * @throws PhpProcessException
     */
    protected function runProcess(string $job, array $settings): array
    {
        $env = null;

        if ($this->env) {
            $env = $_SERVER ?? [];
            unset($env['argv'], $env['argc']);
            $env = array_merge($env, $this->env);

            foreach ($env as $envKey => $envVar) {
                if (is_array($envVar)) {
                    unset($env[$envKey]);
                }
            }
        }

        $pipeSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        if ($this->stderrRedirection) {
            $pipeSpec[2] = ['redirect', 1];
        }

        $process = proc_open(
            $this->getCommand($settings, $this->tempFile),
            $pipeSpec,
            $pipes,
            null,
            $env,
        );

        if (!is_resource($process)) {
            throw new PhpProcessException(
                'Unable to spawn worker process',
            );
        }

        if ($job) {
            $this->process($pipes[0], $job);
        }

        fclose($pipes[0]);

        $stderr = $stdout = '';

        unset($pipes[0]);
        $timeout = 5;

        while (true) {
            $r = $pipes;
            $w = null;
            $e = null;

            $n = @stream_select($r, $w, $e, $timeout);

            if ($n === false) {
                break;
            }

            if ($n === 0) {
                proc_terminate($process, 9);

                throw new PhpProcessException(
                    sprintf(
                        'Job execution aborted after %d seconds',
                        $timeout,
                    ),
                );
            }

            if ($n > 0) {
                foreach ($r as $pipe) {
                    $pipeOffset = 0;

                    foreach ($pipes as $i => $origPipe) {
                        if ($pipe === $origPipe) {
                            $pipeOffset = $i;

                            break;
                        }
                    }

                    if (!$pipeOffset) {
                        break;
                    }

                    $line = fread($pipe, 8192);

                    if ($line === '' || $line === false) {
                        fclose($pipes[$pipeOffset]);

                        unset($pipes[$pipeOffset]);
                    } elseif ($pipeOffset === 1) {
                        $stdout .= $line;
                    } else {
                        $stderr .= $line;
                    }
                }

                if (empty($pipes)) {
                    break;
                }
            }
        }

        proc_close($process);

        $this->cleanup();

        return ['stdout' => $stdout, 'stderr' => $stderr];
    }

    /**
     * @param resource $pipe
     */
    protected function process($pipe, string $job): void
    {
        fwrite($pipe, $job);
    }

    protected function cleanup(): void
    {
        if ($this->tempFile) {
            unlink($this->tempFile);
        }
    }
}
