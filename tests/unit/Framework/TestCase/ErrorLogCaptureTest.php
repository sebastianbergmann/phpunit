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
use function ini_set;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ShutdownHandler;
use ReflectionProperty;

#[CoversClass(ErrorLogCapture::class)]
#[Small]
final class ErrorLogCaptureTest extends TestCase
{
    public function testVerifyDoesNothingWhenErrorLogOutputWasNotExpected(): void
    {
        $capture = new ErrorLogCapture;

        $capture->verify();

        $this->expectNotToPerformAssertions();
    }

    public function testStopIsNoOpWhenCaptureWasNeverStarted(): void
    {
        $capture = new ErrorLogCapture;

        $capture->stop();

        $this->expectNotToPerformAssertions();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testVerifyPassesWhenErrorLogWasCalledAndExpected(): void
    {
        $capture = new ErrorLogCapture;

        $capture->expect();

        error_log('something went wrong');

        $capture->verify();
        $capture->stop();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testExpectingErrorLogOutputMoreThanOnceHasNoEffect(): void
    {
        $capture = new ErrorLogCapture;

        $capture->expect();
        $capture->expect();

        error_log('something went wrong');

        $capture->verify();
        $capture->stop();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testConfiguresShutdownMessageWhenDisplayErrorsIsDisabled(): void
    {
        ini_set('display_errors', '0');

        $capture = new ErrorLogCapture;

        $capture->expect();

        try {
            $this->assertStringContainsString(
                'Premature end of PHPUnit\'s PHP process',
                new ReflectionProperty(ShutdownHandler::class, 'message')->getValue(),
            );
        } finally {
            $capture->stop();
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testVerifyFailsWhenErrorLogWasExpectedButNotCalled(): void
    {
        $capture = new ErrorLogCapture;

        $capture->expect();

        try {
            $capture->verify();

            $this->fail('ExpectationFailedException was not raised');
        } catch (ExpectationFailedException) {
        } finally {
            $capture->stop();
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testErrorLogOutputWrittenBeforeExpectationIsNotConsidered(): void
    {
        $logTarget = tempnam(sys_get_temp_dir(), 'phpunit_');

        $this->assertNotFalse($logTarget);

        ini_set('error_log', $logTarget);

        error_log('logged before the expectation');

        $capture = new ErrorLogCapture;

        $capture->expect();

        try {
            $capture->verify();

            $this->fail('ExpectationFailedException was not raised');
        } catch (ExpectationFailedException) {
        } finally {
            $capture->stop();

            unlink($logTarget);
        }
    }
}
