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

use function assert;
use function fclose;
use function ini_get;
use function ini_set;
use function is_writable;
use function preg_replace;
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
    private bool $expectErrorLog = false;

    /**
     * @var false|resource
     */
    private mixed $captureHandle            = false;
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

        $captureHandle = tmpfile();

        // @codeCoverageIgnoreStart
        if ($captureHandle === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $meta = stream_get_meta_data($captureHandle);

        // @codeCoverageIgnoreStart
        if (!isset($meta['uri'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $capturePath = $meta['uri'];

        if (!@is_writable($capturePath)) {
            return;
        }

        $this->captureHandle     = $captureHandle;
        $this->previousLogTarget = ini_set('error_log', $capturePath);
    }

    /**
     * @throws ErrorLogNotWritableException
     */
    public function verify(): void
    {
        // @codeCoverageIgnoreStart
        if ($this->captureHandle === false) {
            if ($this->expectErrorLog) {
                throw new ErrorLogNotWritableException;
            }

            return;
        }
        // @codeCoverageIgnoreEnd

        $errorLogOutput = stream_get_contents($this->captureHandle);

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
        if ($this->captureHandle === false) {
            return;
        }

        if ($this->expectErrorLog) {
            return;
        }

        $errorLogOutput = stream_get_contents($this->captureHandle);

        if ($errorLogOutput !== false) {
            print self::stripDateFromErrorLog($errorLogOutput);
        }
    }

    public function stop(): void
    {
        if ($this->captureHandle === false) {
            return;
        }

        ShutdownHandler::resetMessage();

        fclose($this->captureHandle);

        $this->captureHandle = false;

        // @codeCoverageIgnoreStart
        if ($this->previousLogTarget === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        ini_set('error_log', $this->previousLogTarget);

        $this->previousLogTarget = false;
    }

    private static function stripDateFromErrorLog(string $log): string
    {
        // https://github.com/php/php-src/blob/c696087e323263e941774ebbf902ac249774ec9f/main/main.c#L905
        $result = preg_replace('/\[\d+-\w+-\d+ \d+:\d+:\d+ [^\r\n[\]]+?\] /', '', $log);

        assert($result !== null);

        return $result;
    }
}
