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

use function error_log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ErrorLogNotWritableException;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorLogCapture::class)]
#[Small]
final class ErrorLogCaptureTest extends TestCase
{
    public function testVerifyDoesNothingWhenCaptureNeverStartedAndNoExpectation(): void
    {
        $capture = new ErrorLogCapture;

        $capture->verify();

        $this->expectNotToPerformAssertions();
    }

    public function testVerifyThrowsWhenExpectedAndCaptureCouldNotBeStarted(): void
    {
        $capture = new ErrorLogCapture;

        $capture->expect();

        $this->expectException(ErrorLogNotWritableException::class);

        $capture->verify();
    }

    public function testHandleErrorIsNoOpWhenCaptureNeverStarted(): void
    {
        $capture = new ErrorLogCapture;

        $capture->handleError();

        $this->expectNotToPerformAssertions();
    }

    public function testStopIsNoOpWhenCaptureNeverStarted(): void
    {
        $capture = new ErrorLogCapture;

        $capture->stop();

        $this->expectNotToPerformAssertions();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testStartStopRoundtripIsCleanWhenNoErrorWasLogged(): void
    {
        $capture = new ErrorLogCapture;

        $capture->start();
        $capture->verify();
        $capture->stop();

        $this->expectNotToPerformAssertions();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testVerifyPassesWhenErrorLogWasCalledAndExpected(): void
    {
        $capture = new ErrorLogCapture;

        $capture->expect();
        $capture->start();

        error_log('something went wrong');

        $capture->verify();
        $capture->stop();
    }
}
