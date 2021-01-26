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

use function sprintf;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Telemetry\Duration
 */
final class DurationTest extends TestCase
{
    public function testFromSecondsAndNanosecondsRejectsNegativeSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for seconds must not be negative');

        Duration::fromSecondsAndNanoseconds(
            -1,
            0
        );
    }

    public function testFromSecondsAndNanosecondsRejectsNegativeNanoseconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be negative');

        Duration::fromSecondsAndNanoseconds(
            0,
            -1
        );
    }

    public function testFromSecondsAndNanosecondsRejectsNanosecondsGreaterThan999999999(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be greater than 999999999');

        Duration::fromSecondsAndNanoseconds(
            0,
            1000000000
        );
    }

    public function testFromSecondsAndNanosecondsReturnsDuration(): void
    {
        $seconds     = 123;
        $nanoseconds = 999999999;

        $duration = Duration::fromSecondsAndNanoseconds(
            $seconds,
            $nanoseconds
        );

        $this->assertSame($seconds, $duration->seconds());
        $this->assertSame($nanoseconds, $duration->nanoseconds());
    }

    public function testAsStringFormatsDurationWithDurationFormatterWhenSpecified(): void
    {
        $formatter = new class implements DurationFormatter {
            public function format(Duration $duration): string
            {
                return sprintf(
                    '%d#%d',
                    $duration->seconds(),
                    $duration->nanoseconds()
                );
            }
        };

        $duration = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $formatted = $duration->asString($formatter);

        $this->assertSame($formatter->format($duration), $formatted);
    }

    /**
     * @dataProvider provideDurationAndStringRepresentation
     */
    public function testAsStringFormatsDurationWhenDurationFormatterIsNotSpecified(int $seconds, int $nanoseconds, string $formatted): void
    {
        $duration = Duration::fromSecondsAndNanoseconds(
            $seconds,
            $nanoseconds
        );

        $this->assertSame($formatted, $duration->asString());
    }

    /**
     * @return array<string, array<{0: int, 1: int, 2: string>>
     */
    public function provideDurationAndStringRepresentation(): array
    {
        return [
            'less-than-a-minute' => [
                59,
                123,
                '59.000000123',
            ],
            'less-than-an-hour' => [
                3559,
                123,
                '59:19.000000123',
            ],
            'more-than-an-hour' => [
                3601,
                123,
                '01:01.000000123',
            ],
        ];
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            456,
            123
        );

        $this->assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesAreSame(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $this->assertTrue($one->equals($two));
    }

    public function testIsLessThanReturnsFalseWhenSecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            122,
            456
        );

        $this->assertFalse($one->isLessThan($two));
    }

    public function testIsLessThanReturnsFalseWhenSecondsAreEqualAndNanosecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            455
        );

        $this->assertFalse($one->isLessThan($two));
    }

    public function testIsLessThanReturnsFalseWhenValuesAreSame(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $this->assertFalse($one->isLessThan($two));
    }

    public function testIsLessThanReturnsTrueWhenSecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            124,
            456
        );

        $this->assertTrue($one->isLessThan($two));
    }

    public function testIsLessThanReturnsTrueWhenSecondsAreEqualAndNanosecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            457
        );

        $this->assertTrue($one->isLessThan($two));
    }

    public function testIsGreaterThanReturnsFalseWhenSecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            124,
            456
        );

        $this->assertFalse($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsFalseWhenSecondsAreEqualAndNanosecondsAreLess(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            457
        );

        $this->assertFalse($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsFalseWhenValuesAreSame(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $this->assertFalse($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsTrueWhenSecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            122,
            456
        );

        $this->assertTrue($one->isGreaterThan($two));
    }

    public function testIsGreaterThanReturnsTrueWhenSecondsAreEqualAndNanosecondsAreGreater(): void
    {
        $one = Duration::fromSecondsAndNanoseconds(
            123,
            456
        );

        $two = Duration::fromSecondsAndNanoseconds(
            123,
            455
        );

        $this->assertTrue($one->isGreaterThan($two));
    }
}
