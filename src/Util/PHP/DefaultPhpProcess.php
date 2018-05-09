<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use PHPUnit\Framework\Exception;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Default utility for PHP sub-processes.
 */
class DefaultPhpProcess extends AbstractPhpProcess
{
    /**
     * @var string
     */
    protected $tempFile;

    /**
     * Runs a single job (PHP code) using a separate PHP process.
     *
     * @throws Exception
     */
    public function runJob(string $job, array $settings = []): array
    {
        if ($this->useTemporaryFile() || $this->stdin) {
            if (!($this->tempFile = \tempnam(\sys_get_temp_dir(), 'PHPUnit')) ||
                \file_put_contents($this->tempFile, $job) === false) {
                throw new Exception(
                    'Unable to write temporary file'
                );
            }

            $job = $this->stdin;
        }

        return $this->runProcess($job, $settings);
    }

    /**
     * Handles creating the child process and returning the STDOUT and STDERR
     *
     * @throws Exception
     */
    protected function runProcess(string $job, array $settings): array
    {
        $env = null;

        if ($this->env) {
            $env = $_SERVER ?? [];
            unset($env['argv'], $env['argc']);
            $env = \array_merge($env, $this->env);

            foreach ($env as $envKey => $envVar) {
                if (\is_array($envVar)) {
                    unset($env[$envKey]);
                }
            }
        }

        ['command' => $command, 'parameters' => $parameters] = $this->getCommand($settings, $this->tempFile);

        $process = new Process(
            $command,
            \getcwd(),
            $env,
            $job,
            $this->timeout === 0 ? null : $this->timeout
        );

        try {
            $process->start(null, $parameters);
        } catch (RuntimeException $e) {
            throw new Exception(
                'Unable to spawn worker process'
            );
        }

        $process->wait();

        $this->cleanup();

        return ['stdout' => $process->getOutput(), 'stderr' => $process->getErrorOutput()];
    }

    protected function process($pipe, string $job): void
    {
        \fwrite($pipe, $job);
    }

    protected function cleanup(): void
    {
        if ($this->tempFile) {
            \unlink($this->tempFile);
        }
    }

    protected function useTemporaryFile(): bool
    {
        return false;
    }
}
