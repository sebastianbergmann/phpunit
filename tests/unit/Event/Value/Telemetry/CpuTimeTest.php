<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(CpuTime::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class CpuTimeTest extends TestCase
{
    public function testCanBeCreatedFromSecondsAndNanoseconds(): void
    {
        $seconds     = 123;
        $nanoseconds = 999999999;

        $cpuTime = CpuTime::fromSecondsAndNanoseconds($seconds, $nanoseconds);

        $this->assertSame($seconds, $cpuTime->seconds());
        $this->assertSame($nanoseconds, $cpuTime->nanoseconds());
    }

    public function testSecondsMustNotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Value for seconds must not be negative.');

        CpuTime::fromSecondsAndNanoseconds(-1, 0);
    }

    public function testNanosecondsMustNotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Value for nanoseconds must not be negative.');

        CpuTime::fromSecondsAndNanoseconds(0, -1);
    }

    public function testNanosecondsMustNotBeGreaterThan999999999(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Value for nanoseconds must not be greater than 999999999.');

        CpuTime::fromSecondsAndNanoseconds(0, 1000000000);
    }

    public function testCanBeRepresentedAsFloat(): void
    {
        $this->assertSame(0.0, CpuTime::fromSecondsAndNanoseconds(0, 0)->asFloat());
        $this->assertSame(1.5, CpuTime::fromSecondsAndNanoseconds(1, 500000000)->asFloat());
    }

    public function testCanBeAdded(): void
    {
        $one = CpuTime::fromSecondsAndNanoseconds(1, 200);
        $two = CpuTime::fromSecondsAndNanoseconds(2, 300);

        $sum = $one->add($two);

        $this->assertSame(3, $sum->seconds());
        $this->assertSame(500, $sum->nanoseconds());
    }

    public function testAdditionCarriesNanosecondsOverflow(): void
    {
        $one = CpuTime::fromSecondsAndNanoseconds(1, 800000000);
        $two = CpuTime::fromSecondsAndNanoseconds(2, 500000000);

        $sum = $one->add($two);

        $this->assertSame(4, $sum->seconds());
        $this->assertSame(300000000, $sum->nanoseconds());
    }

    public function testCanBeSubtracted(): void
    {
        $one = CpuTime::fromSecondsAndNanoseconds(5, 700);
        $two = CpuTime::fromSecondsAndNanoseconds(2, 300);

        $diff = $one->diff($two);

        $this->assertSame(3, $diff->seconds());
        $this->assertSame(400, $diff->nanoseconds());
    }

    public function testSubtractionBorrowsFromSecondsWhenNanosecondsUnderflow(): void
    {
        $one = CpuTime::fromSecondsAndNanoseconds(5, 100000000);
        $two = CpuTime::fromSecondsAndNanoseconds(2, 500000000);

        $diff = $one->diff($two);

        $this->assertSame(2, $diff->seconds());
        $this->assertSame(600000000, $diff->nanoseconds());
    }

    public function testSubtractionReturnsZeroWhenResultWouldBeNegative(): void
    {
        $one = CpuTime::fromSecondsAndNanoseconds(1, 0);
        $two = CpuTime::fromSecondsAndNanoseconds(5, 0);

        $diff = $one->diff($two);

        $this->assertSame(0, $diff->seconds());
        $this->assertSame(0, $diff->nanoseconds());
    }
}
