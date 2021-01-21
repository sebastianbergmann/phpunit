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
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Telemetry\HRTime
 */
final class HRTimeTest extends TestCase
{
    public function testFromSecondsAndNanosecondsRejectsNegativeSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for seconds must not be negative');

        HRTime::fromSecondsAndNanoseconds(
            -1,
            0
        );
    }

    public function testFromSecondsAndNanosecondsRejectsNegativeNanoseconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be negative');

        HRTime::fromSecondsAndNanoseconds(
            0,
            -1
        );
    }

    public function testFromSecondsAndNanosecondsRejectsNanosecondsGreaterThan999999999(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanoseconds must not be greater than 999999999');

        HRTime::fromSecondsAndNanoseconds(
            0,
            1000000000
        );
    }

    public function testFromSecondsAndNanosecondsReturnsHRTime(): void
    {
        $seconds     = 123;
        $nanoseconds = 456;

        $time = HRTime::fromSecondsAndNanoseconds(
            $seconds,
            $nanoseconds
        );

        $this->assertSame($seconds, $time->seconds());
        $this->assertSame($nanoseconds, $time->nanoseconds());
    }

    /**
     * @dataProvider provideStartGreaterThanCurrent
     */
    public function testDurationRejectsStartGreaterThanCurrent(
        int $startSeconds,
        int $startNanoseconds,
        int $currentSeconds,
        int $currentNanoseconds
    ): void {
        $start = HRTime::fromSecondsAndNanoseconds(
            $startSeconds,
            $startNanoseconds
        );

        $current = HRTime::fromSecondsAndNanoseconds(
            $currentSeconds,
            $currentNanoseconds
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Other needs to be greater.');

        $current->duration($start);
    }

    /**
     * @return array<string, array<{0: int, 1: int, 2: int, 3: int>
     */
    public function provideStartGreaterThanCurrent(): array
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
     * @dataProvider provideStartCurrentAndDuration
     */
    public function testDurationReturnsDifferenceBetweenTimeAndOtherTime(
        int $startSeconds,
        int $startNanoseconds,
        int $currentSeconds,
        int $currentNanoseconds,
        Duration $duration
    ): void {
        $start = HRTime::fromSecondsAndNanoseconds(
            $startSeconds,
            $startNanoseconds
        );

        $current = HRTime::fromSecondsAndNanoseconds(
            $currentSeconds,
            $currentNanoseconds
        );

        $this->assertEquals($duration, $current->duration($start));
    }

    /**
     * @return array<string, array<{0: int, 1: int, 2: int, 3: int, 4: Duration>
     */
    public function provideStartCurrentAndDuration(): array
    {
        return [
            'start-equal-to-current' => [
                10,
                50,
                10,
                50,
                Duration::fromSecondsAndNanoseconds(0, 0),
            ],
            'start-smaller-than-current' => [
                10,
                50,
                12,
                70,
                Duration::fromSecondsAndNanoseconds(2, 20),
            ],
            'start-nanoseconds-greater-than-current-nanoseconds' => [
                10,
                50,
                12,
                30,
                Duration::fromSecondsAndNanoseconds(1, 999999980),
            ],
        ];
    }
}
