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

use const PHP_OUTPUT_HANDLER_CLEAN;
use const PHP_OUTPUT_HANDLER_FINAL;
use function is_string;
use function ob_end_clean;
use function ob_get_contents;
use function ob_get_level;
use function ob_start;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class OutputBuffer
{
    private string $output                     = '';
    private ?string $expectedRegularExpression = null;
    private ?string $expectedString            = null;
    private bool $bufferingActive              = false;
    private int $bufferingLevel                = 0;
    private string $bufferingCaptured          = '';
    private bool $bufferingDestroyed           = false;
    private bool $retrievedForAssertion        = false;

    public function expectRegularExpression(string $expectedRegularExpression): void
    {
        $this->expectedRegularExpression = $expectedRegularExpression;
    }

    public function expectString(string $expectedString): void
    {
        $this->expectedString = $expectedString;
    }

    public function hasExpectation(): bool
    {
        return is_string($this->expectedString) || is_string($this->expectedRegularExpression);
    }

    public function expectsOutput(): bool
    {
        return $this->hasExpectation() || $this->retrievedForAssertion;
    }

    /**
     * @phpstan-assert-if-true non-empty-string $this->output()
     */
    public function hasUnexpectedOutput(): bool
    {
        if ($this->output === '') {
            return false;
        }

        if ($this->expectsOutput()) {
            return false;
        }

        return true;
    }

    public function output(): string
    {
        if (!$this->bufferingActive) {
            return $this->output;
        }

        return $this->bufferingCaptured . (string) ob_get_contents();
    }

    public function getActualOutputForAssertion(): string
    {
        $this->retrievedForAssertion = true;

        return $this->output();
    }

    public function start(): void
    {
        $this->bufferingCaptured  = '';
        $this->bufferingDestroyed = false;

        ob_start(function (string $buffer, int $phase): string
        {
            $isClean = ($phase & PHP_OUTPUT_HANDLER_CLEAN) !== 0;
            $isFinal = ($phase & PHP_OUTPUT_HANDLER_FINAL) !== 0;

            if ($isFinal) {
                $this->bufferingDestroyed = true;
            }

            if (!$isClean || $isFinal) {
                $this->bufferingCaptured .= $buffer;
            }

            // @codeCoverageIgnoreStart
            if ($isFinal && !$isClean) {
                return $buffer;
            }
            // @codeCoverageIgnoreEnd

            return '';
        });

        $this->bufferingActive = true;
        $this->bufferingLevel  = ob_get_level();
    }

    public function stop(): OutputBufferStopResult
    {
        $bufferingLevel = ob_get_level();

        if ($bufferingLevel !== $this->bufferingLevel) {
            if ($bufferingLevel > $this->bufferingLevel) {
                $message = 'Test code or tested code did not close its own output buffers';
            } else {
                $message = 'Test code or tested code closed output buffers other than its own';
            }

            while (ob_get_level() >= $this->bufferingLevel) {
                if (!ob_end_clean()) {
                    break;
                }
            }

            $this->output          = $this->bufferingCaptured;
            $this->bufferingActive = false;
            $this->bufferingLevel  = ob_get_level();

            return new OutputBufferStopResult(false, $message);
        }

        $bufferWasSubstituted = $this->bufferingDestroyed;

        ob_end_clean();

        $this->output          = $this->bufferingCaptured;
        $this->bufferingActive = false;
        $this->bufferingLevel  = ob_get_level();

        if ($bufferWasSubstituted) {
            return new OutputBufferStopResult(
                false,
                'Test code or tested code closed output buffers other than its own',
            );
        }

        return new OutputBufferStopResult(true, null);
    }

    /**
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function performAssertions(): void
    {
        if ($this->expectedRegularExpression !== null) {
            Assert::assertMatchesRegularExpression($this->expectedRegularExpression, $this->output);
        } elseif ($this->expectedString !== null) {
            Assert::assertSame($this->expectedString, $this->output);
        }
    }
}
