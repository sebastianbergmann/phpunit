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
use PHPUnit\Framework\TestCase;

#[CoversClass(HRTime::class)]
#[Small]
final class HRTimeTest extends TestCase
{
    /**
     * @return array<string, array{0: int, 1: int, 2: int, 3: int}>
     */
    public static function provideStartGreaterThanEnd(): array
    {
        return [
            'seconds-greater' => [
                11,
                1,
                10,
                1,
            ],
            'seconds-and-nanoseconds-greater' => [
                11,
                1,
                10,
                0,
            ],
            'nanoseconds-greater' => [
                10,
                1,
                10,
                0,
            ],
        ];
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: int, 3: int, 4: Duration}>
     */
    public static function provideStartEndAndDuration(): array
    {
        return [
            'start-equal-to-end' => [
                10,
                50,
                10,
                50,
                Duration::fromSecondsAndNanoseconds(0, 0),
            ],
            'start-smaller-than-end' => [
                10,
                50,
                12,
                70,
                Duration::fromSecondsAndNanoseconds(2, 20),
            ],
            'start-nanoseconds-greater-than-end-nanoseconds' => [
                10,
                50,
                12,
                30,
                Duration::fromSecondsAndNanoseconds(1, 999999980),
            ],
        ];
    }

    public function testFromSecondsAndNanosecondsRejectsNegativeSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for seconds must not be negative');

        HRTime::fromSecondsAndNanoseconds(
            -1,
            0,
        );
    }

    public function testFromSecondsAndNanosecondsRejectsNegativeNanoseconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be negative');

        HRTime::fromSecondsAndNanoseconds(
            0,
            -1,
        );
    }

    public function testFromSecondsAndNanosecondsRejectsNanosecondsGreaterThan999999999(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be greater than 999999999');

        HRTime::fromSecondsAndNanoseconds(
            0,
            1000000000,
        );
    }

    public function testFromSecondsAndNanosecondsReturnsHRTime(): void
    {
        $seconds     = 123;
        $nanoseconds = 456;

        $time = HRTime::fromSecondsAndNanoseconds(
            $seconds,
            $nanoseconds,
        );

        $this->assertSame($seconds, $time->seconds());
        $this->assertSame($nanoseconds, $time->nanoseconds());
    }

    #[DataProvider('provideStartGreaterThanEnd')]
    public function testDurationIgnoresStartGreaterThanEnd(int $startSeconds, int $startNanoseconds, int $endSeconds, int $endNanoseconds): void
    {
        $start = HRTime::fromSecondsAndNanoseconds(
            $startSeconds,
            $startNanoseconds,
        );

        $end = HRTime::fromSecondsAndNanoseconds(
            $endSeconds,
            $endNanoseconds,
        );

        $duration = $end->duration($start);

        $this->assertSame(0, $duration->seconds());
        $this->assertSame(0, $duration->nanoseconds());
    }

    #[DataProvider('provideStartEndAndDuration')]
    public function testDurationReturnsDifferenceBetweenEndAndStart(int $startSeconds, int $startNanoseconds, int $endSeconds, int $endNanoseconds, Duration $duration): void
    {
        $start = HRTime::fromSecondsAndNanoseconds(
            $startSeconds,
            $startNanoseconds,
        );

        $end = HRTime::fromSecondsAndNanoseconds(
            $endSeconds,
            $endNanoseconds,
        );

        $this->assertEquals($duration, $end->duration($start));
    }
}
