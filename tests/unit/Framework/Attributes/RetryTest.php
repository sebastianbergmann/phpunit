<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use DateTime;
use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;

final class RetryTest extends TestCase
{
    private static int $retryNumber = 0;
    private static ?DateTime $start = null;

    protected function setUp(): void
    {
        self::$retryNumber = 0;
        self::$start       = new DateTime;
    }

    #[Retry(3)]
    public function testRetriesUntilMaxAttemptsThenSucceeds(): void
    {
        if (self::$retryNumber < 3) {
            self::$retryNumber++;

            throw new Exception;
        }

        $this->assertSame(3, self::$retryNumber);
    }

    #[Retry(1)]
    public function testSingleRetryThenThrowsExpectedException(): void
    {
        if (self::$retryNumber < 1) {
            self::$retryNumber++;

            throw new Exception;
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('test exception two');
        $this->assertSame(1, self::$retryNumber);

        throw new Exception('test exception two');
    }

    #[Retry(2, 0, LogicException::class)]
    public function testRetryWithUnmatchedExceptionTypeFailsImmediately(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('test exception');
        $this->assertSame(0, self::$retryNumber);
        self::$retryNumber++;

        throw new Exception('test exception');
    }

    #[Retry(2, 0, LogicException::class)]
    #[Retry(2)]
    public function testMultipleRetryAttributesFallBackToDefaultRetry(): void
    {
        if (self::$retryNumber < 2) {
            self::$retryNumber++;

            throw new Exception;
        }

        $this->assertSame(2, self::$retryNumber);
    }

    #[Retry(5, 0, LogicException::class)]
    public function testRetriesUntilLogicExceptionStopsThrowing(): void
    {
        if (self::$retryNumber < 5) {
            self::$retryNumber++;

            throw new LogicException;
        }

        $this->assertSame(5, self::$retryNumber);
    }

    #[Retry(1, 2)]
    public function testRetryDelaysExecutionBySpecifiedSeconds(): void
    {
        $end = new DateTime;

        if (self::$retryNumber < 1) {
            self::$retryNumber++;

            throw new Exception;
        }

        $diffInSeconds = $end->getTimestamp() - self::$start->getTimestamp();

        $this->assertSame(1, self::$retryNumber);
        $this->assertSame(2, $diffInSeconds);
    }
}
