<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Falsification;

use Countable;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use SebastianBergmann\Invoker\TimeoutException;

final class ExampleTest extends TestCase
{
    public function testPasses(int $number): void
    {
        $this->assertTrue(true);
    }

    public function testFailsWithAMessageThatDependsOnItsArgument(int $number): void
    {
        $this->fail('failure for ' . $number);
    }

    public function testErrorsAtALocationThatDependsOnItsArgument(int $number): void
    {
        if ($number === 0) {
            throw new RuntimeException('error');
        }

        throw new RuntimeException('error');
    }

    public function testErrors(int $number): void
    {
        throw new RuntimeException('error');
    }

    public function testDoesNotMeetTheExpectationForATestDouble(int $number): void
    {
        $this->createMock(Countable::class)->expects($this->once())->method('count');
    }

    public function testExceedsItsTimeLimit(int $number): void
    {
        throw new TimeoutException('Execution aborted after ' . $number . ' seconds');
    }

    public function testIsSkipped(int $number): void
    {
        $this->markTestSkipped('skipped');
    }

    public function testReachesTheCallStackLimit(int $line): void
    {
        throw $this->errorInARecursion('Maximum call stack size of 8339456 bytes (zend.max_allowed_stack_size - zend.reserved_stack_size) reached. Infinite recursion?', $line);
    }

    public function testErrorsInARecursion(int $line): void
    {
        throw $this->errorInARecursion('error', $line);
    }

    /**
     * Returns an error that is raised by the call in the given line of a
     * recursion in which the calls in lines 100 and 102 call each other ten
     * times. As in the stack trace of a real recursion, the first frame is the
     * call in the other line, which led to the given one.
     */
    private function errorInARecursion(string $message, int $line): Error
    {
        $other = $line === 100 ? 102 : 100;
        $trace = [];

        for ($i = 0; $i < 10; $i++) {
            $trace[] = ['file' => __FILE__, 'line' => $other, 'function' => 'call'];
            $trace[] = ['file' => __FILE__, 'line' => $line, 'function' => 'call'];
        }

        $error = new Error($message);

        new ReflectionProperty(Error::class, 'line')->setValue($error, $line);
        new ReflectionProperty(Error::class, 'trace')->setValue($error, $trace);

        return $error;
    }
}
