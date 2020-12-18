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
    public function testConstructorRejectsNegativeSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for second must not be negative');

        new Duration(
            -1,
            0
        );
    }

    public function testConstructorRejectsNegativeNanoseconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanosecond must not be negative');

        new Duration(
            0,
            -1
        );
    }

    public function testConstructorSetsValues(): void
    {
        $seconds     = 123;
        $nanoseconds = 456;

        $duration = new Duration(
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

        $duration = new Duration(
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
        $duration = new Duration(
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
}
