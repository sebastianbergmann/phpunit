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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Duration::class)]
#[Small]
final class DurationTest extends TestCase
{
    /**
     * @return array<string, array{0: int, 1: int, 2: string}>
     */
    public static function provideDurationAndStringRepresentation(): array
    {
        return [
            'less than a minute' => [
                '00:00:59.000000123',
                59,
                123,
            ],

            'less than an hour' => [
                '00:59:19.000000123',
                3559,
                123,
            ],

            'more than an hour' => [
                '01:00:01.000000123',
                3601,
                123,
            ],
        ];
    }

    public function testCanBeCreatedFromSecondsAndNanoseconds(): void
    {
        $seconds     = 123;
        $nanoseconds = 999999999;

        $duration = Duration::fromSecondsAndNanoseconds($seconds, $nanoseconds);

        $this->assertSame($seconds, $duration->seconds());
        $this->assertSame($nanoseconds, $duration->nanoseconds());
    }

    public function testSecondsMustNotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for seconds must not be negative');

        Duration::fromSecondsAndNanoseconds(-1, 0);
    }

    public function testNanosecondsMustNotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be negative');

        Duration::fromSecondsAndNanoseconds(0, -1);
    }

    public function testNanosecondsMustNotBeGreaterThan999999999(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be greater than 999999999');

        Duration::fromSecondsAndNanoseconds(0, 1000000000);
    }

    #[DataProvider('provideDurationAndStringRepresentation')]
    #[TestDox('$seconds seconds and $nanoseconds nanoseconds is represented as "$expected"')]
    public function testCanBeRepresentedAsString(string $expected, int $seconds, int $nanoseconds): void
    {
        $this->assertSame(
            $expected,
            (Duration::fromSecondsAndNanoseconds($seconds, $nanoseconds))->asString(),
        );
    }

    public function testCanBeRepresentedAsFloat(): void
    {
        $this->assertSame(0.0, Duration::fromSecondsAndNanoseconds(0, 0)->asFloat());
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456,
        );

        $two = Duration::fromSecondsAndNanoseconds(
            456,
            123,
        );

        $this->assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesAreSame(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 456);

        $this->assertTrue($one->equals($two));
    }

    public function testIsLessThanReturnsFalseWhenSecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(122, 456);

        $this->assertFalse($one->isLessThan($two));
    }

    public function testIsLessThanReturnsFalseWhenSecondsAreEqualAndNanosecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 455);

        $this->assertFalse($one->isLessThan($two));
    }

    public function testIsLessThanReturnsFalseWhenValuesAreSame(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 456);

        $this->assertFalse($one->isLessThan($two));
    }

    public function testIsLessThanReturnsTrueWhenSecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(124, 456);

        $this->assertTrue($one->isLessThan($two));
    }

    public function testIsLessThanReturnsTrueWhenSecondsAreEqualAndNanosecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 457);

        $this->assertTrue($one->isLessThan($two));
    }

    public function testIsGreaterThanReturnsFalseWhenSecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(124, 456);

        $this->assertFalse($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsFalseWhenSecondsAreEqualAndNanosecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 457);

        $this->assertFalse($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsFalseWhenValuesAreSame(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 456);

        $this->assertFalse($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsTrueWhenSecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(122, 456);

        $this->assertTrue($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsTrueWhenSecondsAreEqualAndNanosecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(123, 456);
        $two = Duration::fromSecondsAndNanoseconds(123, 455);

        $this->assertTrue($one->isGreaterThan($two));
    }
}
