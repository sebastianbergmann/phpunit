<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Default utility for PHP sub-processes.
 *
 * @since Class available since Release 3.5.12
 */
class PHPUnit_Util_PHP_Default extends PHPUnit_Util_PHP
{
    /**
     * Runs a single job (PHP code) using a separate PHP process.
     *
     * @param string $job
     * @param array  $settings
     *
     * @return array
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function runJob($job, array $settings = [])
    {
        $process = proc_open(
            $this->getCommand($settings),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new PHPUnit_Framework_Exception(
                'Unable to spawn worker process'
            );
        }

        $this->process($pipes[0], $job);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);
        $this->cleanup();

        return ['stdout' => $stdout, 'stderr' => $stderr];
    }

    /**
     * @param resource $pipe
     * @param string   $job
     *
     * @throws PHPUnit_Framework_Exception
     *
     * @since Method available since Release 3.5.12
     */
    protected function process($pipe, $job)
    {
        fwrite($pipe, $job);
    }

    /**
     * @since Method available since Release 3.5.12
     */
    protected function cleanup()
    {
    }
}
