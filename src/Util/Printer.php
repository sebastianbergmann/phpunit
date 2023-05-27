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

use const ENT_COMPAT;
use const ENT_SUBSTITUTE;
use const PHP_SAPI;
use function assert;
use function count;
use function dirname;
use function explode;
use function fclose;
use function fopen;
use function fsockopen;
use function fwrite;
use function htmlspecialchars;
use function is_resource;
use function is_string;
use function sprintf;
use function str_replace;
use function strncmp;
use function strpos;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class Printer
{
    /**
     * @psalm-var closed-resource|resource
     */
    private $stream;

    /**
     * @var bool
     */
    private $isPhpStream;

    /**
     * @param null|resource|string $out
     *
     * @throws Exception
     */
    public function __construct($out = null)
    {
        if (is_resource($out)) {
            $this->stream = $out;

            return;
        }

        if (!is_string($out)) {
            return;
        }

        if (strpos($out, 'socket://') === 0) {
            $tmp = explode(':', str_replace('socket://', '', $out));

            if (count($tmp) !== 2) {
                throw new Exception(
                    sprintf(
                        '"%s" does not match "socket://hostname:port" format',
                        $out,
                    ),
                );
            }

            $this->stream = fsockopen($tmp[0], (int) $tmp[1]);

            return;
        }

        if (strpos($out, 'php://') === false && !Filesystem::createDirectory(dirname($out))) {
            throw new Exception(
                sprintf(
                    'Directory "%s" was not created',
                    dirname($out),
                ),
            );
        }

        $this->stream      = fopen($out, 'wb');
        $this->isPhpStream = strncmp($out, 'php://', 6) !== 0;
    }

    public function write(string $buffer): void
    {
        if ($this->stream) {
            assert(is_resource($this->stream));

            fwrite($this->stream, $buffer);
        } else {
            if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
                $buffer = htmlspecialchars($buffer, ENT_COMPAT | ENT_SUBSTITUTE);
            }

            print $buffer;
        }
    }

    public function flush(): void
    {
        if ($this->stream && $this->isPhpStream) {
            assert(is_resource($this->stream));

            fclose($this->stream);
        }
    }
}
