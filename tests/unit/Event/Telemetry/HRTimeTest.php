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
    public function testConstructorRejectsNegativeSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for second must not be negative');

        new HRTime(
            -1,
            0
        );
    }

    public function testConstructorRejectsNegativeNanoseconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for nanosecond must not be negative');

        new HRTime(
            0,
            -1
        );
    }

    public function testConstructorSetsValues(): void
    {
        $seconds     = 123;
        $nanoseconds = 456;

        $time = new HRTime(
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
        $start = new HRTime(
            $startSeconds,
            $startNanoseconds
        );

        $current = new HRTime(
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
        $start = new HRTime(
            $startSeconds,
            $startNanoseconds
        );

        $current = new HRTime(
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
