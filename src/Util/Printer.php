<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function assert;
use function count;
use function dirname;
use function explode;
use function fclose;
use function fopen;
use function fsockopen;
use function fwrite;
use function sprintf;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strncmp;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class Printer
{
    /**
     * @psalm-var closed-resource|resource
     */
    private $stream;
    private bool $isPhpStream;
    private bool $isOpen;

    /**
     * @throws Exception
     */
    public function __construct(string $out)
    {
        if (str_starts_with($out, 'socket://')) {
            $tmp = explode(':', str_replace('socket://', '', $out));

            if (count($tmp) !== 2) {
                throw new Exception(
                    sprintf(
                        '"%s" does not match "socket://hostname:port" format',
                        $out
                    )
                );
            }

            $this->stream = fsockopen($tmp[0], (int) $tmp[1]);
            $this->isOpen = true;

            return;
        }

        if (!str_contains($out, 'php://') && !Filesystem::createDirectory(dirname($out))) {
            throw new Exception(
                sprintf(
                    'Directory "%s" was not created',
                    dirname($out)
                )
            );
        }

        $this->stream      = fopen($out, 'wb');
        $this->isPhpStream = strncmp($out, 'php://', 6) !== 0;
        $this->isOpen      = true;
    }

    public function flush(): void
    {
        if ($this->isOpen && $this->isPhpStream) {
            fclose($this->stream);

            $this->isOpen = false;
        }
    }

    public function write(string $buffer): void
    {
        assert($this->isOpen);

        fwrite($this->stream, $buffer);
    }
}
