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

use PHPUnit\Framework\Exception;

/**
 * Utility class that can print to STDOUT or write to a file.
 */
class Printer
{
    /**
     * If true, flush output after every write.
     *
     * @var bool
     */
    protected $autoFlush = false;

    /**
     * @var resource
     */
    protected $out;

    /**
     * @var string
     */
    protected $outTarget;

    /**
     * Constructor.
     *
     * @param null|mixed $out
     *
     * @throws Exception
     */
    public function __construct($out = null)
    {
        if ($out !== null) {
            if (\is_string($out)) {
                if (\strpos($out, 'socket://') === 0) {
                    $out = \explode(':', \str_replace('socket://', '', $out));

                    if (\count($out) !== 2) {
                        throw new Exception;
                    }

                    $this->out = \fsockopen($out[0], $out[1]);
                } else {
                    if (\strpos($out, 'php://') === false && !$this->createDirectory(\dirname($out))) {
                        throw new Exception(\sprintf('Directory "%s" was not created', \dirname($out)));
                    }

                    $this->out = \fopen($out, 'wt');
                }

                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        }
    }

    /**
     * Flush buffer and close output if it's not to a PHP stream
     */
    public function flush(): void
    {
        if ($this->out && \strncmp($this->outTarget, 'php://', 6) !== 0) {
            \fclose($this->out);
        }
    }

    /**
     * Performs a safe, incremental flush.
     *
     * Do not confuse this function with the flush() function of this class,
     * since the flush() function may close the file being written to, rendering
     * the current object no longer usable.
     */
    public function incrementalFlush(): void
    {
        if ($this->out) {
            \fflush($this->out);
        } else {
            \flush();
        }
    }

    public function write(string $buffer): void
    {
        if ($this->out) {
            \fwrite($this->out, $buffer);

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (\PHP_SAPI !== 'cli' && \PHP_SAPI !== 'phpdbg') {
                $buffer = \htmlspecialchars($buffer, \ENT_SUBSTITUTE);
            }

            print $buffer;

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }

    /**
     * Check auto-flush mode.
     */
    public function getAutoFlush(): bool
    {
        return $this->autoFlush;
    }

    /**
     * Set auto-flushing mode.
     *
     * If set, *incremental* flushes will be done after each write. This should
     * not be confused with the different effects of this class' flush() method.
     */
    public function setAutoFlush(bool $autoFlush): void
    {
        $this->autoFlush = $autoFlush;
    }

    private function createDirectory(string $directory): bool
    {
        return !(!\is_dir($directory) && !@\mkdir($directory, 0777, true) && !\is_dir($directory));
    }
}
