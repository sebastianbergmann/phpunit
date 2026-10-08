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
use PHPUnit\Framework\TestCase;
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
}
