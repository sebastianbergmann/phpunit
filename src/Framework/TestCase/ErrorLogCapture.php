<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use function array_pop;
use function assert;
use function ftruncate;
use function ini_get;
use function ini_set;
use function is_writable;
use function preg_replace;
use function rewind;
use function stream_get_contents;
use function stream_get_meta_data;
use function tmpfile;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ErrorLogNotWritableException;
use PHPUnit\Runner\ShutdownHandler;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ErrorLogCapture
{
    /**
     * Creating and removing the temporary file that error_log() is redirected
     * to is expensive and would otherwise have to be done for every test. A
     * file is therefore reused once the capture it was created for has been
     * stopped. A capture that is started while another one is still active
     * gets a file of its own.
     *
     * @var list<array{handle: resource, path: non-empty-string}>
     */
    private static array $unusedCaptureFiles = [];
    private bool $expectErrorLog             = false;

    /**
     * @var ?array{handle: resource, path: non-empty-string}
     */
    private ?array $captureFile             = null;
    private false|string $previousLogTarget = false;

    public function expect(): void
    {
        $this->expectErrorLog = true;
    }

    public function start(): void
    {
        if (ini_get('display_errors') === '0') {
            ShutdownHandler::setMessage(
                'Fatal error: Premature end of PHPUnit\'s PHP process. Use display_errors=On to see the error message.',
            );
        }

        $captureFile = self::captureFile();

        if ($captureFile === null) {
            return;
        }

        $this->captureFile       = $captureFile;
        $this->previousLogTarget = ini_set('error_log', $captureFile['path']);
    }

    /**
     * @throws ErrorLogNotWritableException
     */
    public function verify(): void
    {
        // @codeCoverageIgnoreStart
        if ($this->captureFile === null) {
            if ($this->expectErrorLog) {
                throw new ErrorLogNotWritableException;
            }

            return;
        }
        // @codeCoverageIgnoreEnd

        $errorLogOutput = stream_get_contents($this->captureFile['handle']);

        if ($this->expectErrorLog) {
            Assert::assertNotEmpty($errorLogOutput, 'error_log() was not called');

            return;
        }

        // @codeCoverageIgnoreStart
        if ($errorLogOutput === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        print self::stripDateFromErrorLog($errorLogOutput);
    }

    public function handleError(): void
    {
        if ($this->captureFile === null) {
            return;
        }

        if ($this->expectErrorLog) {
            return;
        }

        $errorLogOutput = stream_get_contents($this->captureFile['handle']);

        if ($errorLogOutput !== false) {
            print self::stripDateFromErrorLog($errorLogOutput);
        }
    }

    public function stop(): void
    {
        if ($this->captureFile === null) {
            return;
        }

        ShutdownHandler::resetMessage();

        self::$unusedCaptureFiles[] = $this->captureFile;

        $this->captureFile = null;

        // @codeCoverageIgnoreStart
        if ($this->previousLogTarget === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        ini_set('error_log', $this->previousLogTarget);

        $this->previousLogTarget = false;
    }

    /**
     * @return ?array{handle: resource, path: non-empty-string}
     */
    private static function captureFile(): ?array
    {
        $captureFile = array_pop(self::$unusedCaptureFiles);

        if ($captureFile !== null) {
            ftruncate($captureFile['handle'], 0);
            rewind($captureFile['handle']);

            return $captureFile;
        }

        $handle = tmpfile();

        // @codeCoverageIgnoreStart
        if ($handle === false) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        $meta = stream_get_meta_data($handle);

        // @codeCoverageIgnoreStart
        if (!isset($meta['uri']) || $meta['uri'] === '') {
            return null;
        }
        // @codeCoverageIgnoreEnd

        $path = $meta['uri'];

        if (!@is_writable($path)) {
            return null;
        }

        return [
            'handle' => $handle,
            'path'   => $path,
        ];
    }

    private static function stripDateFromErrorLog(string $log): string
    {
        // https://github.com/php/php-src/blob/c696087e323263e941774ebbf902ac249774ec9f/main/main.c#L905
        $result = preg_replace('/\[\d+-\w+-\d+ \d+:\d+:\d+ [^\r\n[\]]+?\] /', '', $log);

        assert($result !== null);

        return $result;
    }
}
