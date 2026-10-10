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

use function ob_end_clean;
use function ob_flush;
use function ob_get_level;
use function ob_start;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutputBuffer::class)]
#[Small]
final class OutputBufferTest extends TestCase
{
    public function testFreshBufferHasNoExpectation(): void
    {
        $buffer = new OutputBuffer;

        $this->assertFalse($buffer->hasExpectation());
        $this->assertFalse($buffer->expectsOutput());
    }

    public function testExpectingARegularExpressionFlagsAnExpectation(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectRegularExpression('/.+/');

        $this->assertTrue($buffer->hasExpectation());
        $this->assertTrue($buffer->expectsOutput());
    }

    public function testExpectingAStringFlagsAnExpectation(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectString('hello');

        $this->assertTrue($buffer->hasExpectation());
        $this->assertTrue($buffer->expectsOutput());
    }

    public function testGetActualOutputForAssertionMakesTheBufferExpectOutput(): void
    {
        $buffer = new OutputBuffer;

        $this->assertFalse($buffer->expectsOutput());

        $buffer->getActualOutputForAssertion();

        $this->assertTrue($buffer->expectsOutput());
        $this->assertFalse($buffer->hasExpectation());
    }

    public function testHasUnexpectedOutputIsFalseWhenNoOutputWasCaptured(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        $result = $buffer->stop();

        $this->assertTrue($result->closedCleanly);
        $this->assertNull($result->riskyMessage);
        $this->assertFalse($buffer->hasUnexpectedOutput());
        $this->assertSame('', $buffer->output());
    }

    public function testCapturesOutputProducedDuringBuffering(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        print 'captured';

        $result = $buffer->stop();

        $this->assertTrue($result->closedCleanly);
        $this->assertSame('captured', $buffer->output());
        $this->assertTrue($buffer->hasUnexpectedOutput());
    }

    public function testRetainsOutputCapturedBeforeBufferingWasSuspended(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        print 'before';

        $buffer->suspend();

        $this->assertSame('before', $buffer->output());

        $buffer->resume();

        print ' after';

        $this->assertSame('before after', $buffer->output());

        $result = $buffer->stop();

        $this->assertTrue($result->closedCleanly);
        $this->assertSame('before after', $buffer->output());
    }

    public function testCapturesOutputWhenBufferingIsResumedAtAnotherOutputBufferingLevel(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        print 'before';

        $buffer->suspend();

        ob_start();

        $buffer->resume();

        print ' flushed';

        ob_flush();

        print ' inside';

        $this->assertSame('before flushed inside', $buffer->output());

        $buffer->suspend();

        ob_end_clean();

        $buffer->resume();

        print ' after';

        $result = $buffer->stop();

        $this->assertTrue($result->closedCleanly);
        $this->assertSame('before flushed inside after', $buffer->output());
    }

    public function testReportsOutputBufferLeftOpenAfterBufferingWasResumedAtAnotherOutputBufferingLevel(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();
        $buffer->suspend();

        ob_start();

        $buffer->resume();

        ob_start();

        $buffer->suspend();

        ob_end_clean();

        $buffer->resume();

        $result = $buffer->stop();

        $this->assertFalse($result->closedCleanly);
        $this->assertSame('Test code or tested code did not close its own output buffers', $result->riskyMessage);
    }

    public function testReportsOutputBufferLeftOpenBeforeBufferingWasSuspended(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        ob_start();

        $buffer->suspend();
        $buffer->resume();

        $result = $buffer->stop();

        $this->assertFalse($result->closedCleanly);
        $this->assertSame('Test code or tested code did not close its own output buffers', $result->riskyMessage);
    }

    public function testReportsFirstProblemFoundWhenBufferingWasSuspended(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        ob_start();

        $buffer->suspend();
        $buffer->resume();

        ob_end_clean();

        $buffer->suspend();
        $buffer->resume();

        $result = $buffer->stop();

        $this->assertFalse($result->closedCleanly);
        $this->assertSame('Test code or tested code did not close its own output buffers', $result->riskyMessage);
    }

    public function testStartingBufferingDiscardsProblemFoundWhenBufferingWasSuspendedPreviously(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();

        ob_start();

        $buffer->suspend();
        $buffer->resume();
        $buffer->stop();

        $buffer->start();

        $result = $buffer->stop();

        $this->assertTrue($result->closedCleanly);
        $this->assertNull($result->riskyMessage);
    }

    public function testStoppingWhileSuspendedEndsBuffering(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();
        $buffer->suspend();

        $level = ob_get_level();

        $buffer->stop();
        $buffer->resume();

        $this->assertSame($level, ob_get_level());
    }

    public function testReportsBufferingThatWasSuspendedAndNotResumed(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();
        $buffer->suspend();

        $result = $buffer->stop();

        $this->assertFalse($result->closedCleanly);
        $this->assertSame('Output buffering was suspended and not resumed', $result->riskyMessage);
    }

    public function testSuspendingAndResumingAfterBufferingWasStoppedDoesNotRestartBuffering(): void
    {
        $buffer = new OutputBuffer;

        $buffer->start();
        $buffer->stop();

        $level = ob_get_level();

        $buffer->suspend();
        $buffer->resume();

        $this->assertSame($level, ob_get_level());
    }

    public function testHasUnexpectedOutputIsFalseWhenOutputWasExpected(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectString('captured');
        $buffer->start();

        print 'captured';

        $buffer->stop();

        $this->assertFalse($buffer->hasUnexpectedOutput());
    }

    public function testPerformAssertionsAcceptsMatchingExpectedString(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectString('captured');
        $buffer->start();

        print 'captured';

        $buffer->stop();
        $buffer->performAssertions();

        $this->assertSame('captured', $buffer->output());
    }

    public function testPerformAssertionsRejectsMismatchedExpectedString(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectString('expected');
        $buffer->start();

        print 'actual';

        $buffer->stop();

        $this->expectException(ExpectationFailedException::class);

        $buffer->performAssertions();
    }

    public function testPerformAssertionsAcceptsMatchingExpectedRegex(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectRegularExpression('/^cap/');
        $buffer->start();

        print 'captured';

        $buffer->stop();
        $buffer->performAssertions();

        $this->assertSame('captured', $buffer->output());
    }

    public function testPerformAssertionsRejectsMismatchedExpectedRegex(): void
    {
        $buffer = new OutputBuffer;

        $buffer->expectRegularExpression('/^cap/');
        $buffer->start();

        print 'actual';

        $buffer->stop();

        $this->expectException(ExpectationFailedException::class);

        $buffer->performAssertions();
    }

    public function testPerformAssertionsDoesNothingWithoutExpectation(): void
    {
        $buffer = new OutputBuffer;

        $buffer->performAssertions();

        $this->expectNotToPerformAssertions();
    }
}
