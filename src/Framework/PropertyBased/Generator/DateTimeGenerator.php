<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function array_shift;
use function ceil;
use function floor;
use function implode;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

/**
 * Generates points in time from a minimum to a maximum, in one of the time
 * zones, as DateTimeImmutable objects.
 *
 * Half of the points in time are from the whole range, biased towards the
 * epoch, the simplest one, and the minimum and the maximum. The other half
 * are notable: points in time where 32-bit timestamps overflow, the turn of
 * the millennium, the first second of a month or a year and the second
 * before it, leap days, and the transitions between standard time and
 * daylight saving time of the time zone, with the second before them, in
 * which the time of day jumps.
 *
 * @template-extends AbstractGenerator<DateTimeImmutable>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DateTimeGenerator extends AbstractGenerator
{
    private const int EPOCH_YEAR              = 1970;
    private const int FIRST_LEAP_YEAR         = 1972;
    private const int SECONDS_PER_DAY         = 86400;
    private const int MICROSECONDS_PER_SECOND = 1000000;

    /**
     * Timestamps at which code that stores or computes points in time errs.
     *
     * @var list<int>
     */
    private const array NOTABLE_TIMESTAMPS = [
        // the epoch
        0,
        // the largest 32-bit signed timestamp and the one after it
        2147483647,
        2147483648,
        // the smallest 32-bit signed timestamp and the one before it
        -2147483648,
        -2147483649,
        // the turn of the millennium
        946684799,
        946684800,
        // the largest 32-bit unsigned timestamp and the one after it
        4294967295,
        4294967296,
    ];

    /**
     * The probability that a point in time is a notable one rather than one
     * from the whole range.
     */
    private const float PROBABILITY_OF_NOTABLE_POINT_IN_TIME = 0.5;

    // the kinds of notable points in time
    private const int NOTABLE_TIMESTAMP = 0;
    private const int START_OF_MONTH    = 1;
    private const int LEAP_DAY          = 2;
    private const int TRANSITION        = 3;

    /**
     * The minimum and the maximum, as their timestamp and their
     * microseconds.
     *
     * @var array{int, int<0, 999999>}
     */
    private array $minimum;

    /**
     * @var array{int, int<0, 999999>}
     */
    private array $maximum;

    /**
     * @var non-empty-list<DateTimeZone>
     */
    private array $timezones;
    private bool $microseconds;
    private IntegerGenerator $timestamps;
    private IntegerGenerator $microsecondsOfSecond;

    /**
     * The years in the range, as their distance from the year of the epoch.
     */
    private IntegerGenerator $years;

    /**
     * The leap years in the range, as the number of the leap years from
     * 1972, or null when there is no leap year in the range.
     */
    private ?IntegerGenerator $leapYears;
    private IntegerGenerator $secondsOfDay;

    /**
     * @var list<int>
     */
    private array $notableTimestamps;

    /**
     * @var non-empty-list<int>
     */
    private array $kinds;
    private BernoulliDistribution $notable;
    private UniformDistribution $uniform;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param array{int, int<0, 999999>}   $minimum      the timestamp and the microseconds of the minimum, which are 0 without $microseconds
     * @param array{int, int<0, 999999>}   $maximum      the timestamp and the microseconds of the maximum, which are 0 without $microseconds
     * @param non-empty-list<DateTimeZone> $timezones    the time zones to choose from, the first one simplest
     * @param bool                         $microseconds whether points in time with microseconds are generated
     */
    public function __construct(array $minimum, array $maximum, array $timezones, bool $microseconds)
    {
        $names = [];

        foreach ($timezones as $timezone) {
            $names[] = $timezone->getName();
        }

        $this->label = self::labelOf('dateTimes', implode('.', $minimum), implode('.', $maximum), implode(',', $names), (string) (int) $microseconds);

        $this->minimum              = $minimum;
        $this->maximum              = $maximum;
        $this->timezones            = $timezones;
        $this->microseconds         = $microseconds;
        $this->timestamps           = new IntegerGenerator($minimum[0], $maximum[0]);
        $this->microsecondsOfSecond = new IntegerGenerator(0, self::MICROSECONDS_PER_SECOND - 1);
        $this->secondsOfDay         = new IntegerGenerator(0, self::SECONDS_PER_DAY - 1);
        $this->notable              = new BernoulliDistribution(self::PROBABILITY_OF_NOTABLE_POINT_IN_TIME);
        $this->uniform              = new UniformDistribution;

        $minimumYear = self::year($minimum[0]);
        $maximumYear = self::year($maximum[0]);

        $this->years = new IntegerGenerator($minimumYear - self::EPOCH_YEAR, $maximumYear - self::EPOCH_YEAR);

        $kinds             = [];
        $notableTimestamps = [];

        foreach (self::NOTABLE_TIMESTAMPS as $timestamp) {
            if ($timestamp >= $minimum[0] && $timestamp <= $maximum[0]) {
                $notableTimestamps[] = $timestamp;
            }
        }

        if ($notableTimestamps !== []) {
            $kinds[] = self::NOTABLE_TIMESTAMP;
        }

        $kinds[] = self::START_OF_MONTH;

        // the leap years are counted in steps of four years from 1972
        $firstLeapYear = (int) ceil(($minimumYear - self::FIRST_LEAP_YEAR) / 4);
        $lastLeapYear  = (int) floor(($maximumYear - self::FIRST_LEAP_YEAR) / 4);

        $leapYears = null;

        if ($firstLeapYear <= $lastLeapYear) {
            $leapYears = new IntegerGenerator($firstLeapYear, $lastLeapYear);

            $kinds[] = self::LEAP_DAY;
        }

        $kinds[] = self::TRANSITION;

        $this->notableTimestamps = $notableTimestamps;
        $this->leapYears         = $leapYears;
        $this->kinds             = $kinds;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): DateTimeImmutable
    {
        $timezone = $source->choose($this->timezones, $this->uniform);

        if ($source->draw(1, $this->notable) === 0) {
            $timestamp = $this->timestamps->generate($source);
        } else {
            $timestamp = $this->notableTimestamp($source, $timezone);
        }

        $microseconds = 0;

        if ($this->microseconds) {
            $microseconds = $this->microsecondsOfSecond->generate($source);
        }

        // a notable point in time, or the microseconds of the minimum or the
        // maximum, can be outside of the range
        if ($timestamp < $this->minimum[0] || ($timestamp === $this->minimum[0] && $microseconds < $this->minimum[1])) {
            [$timestamp, $microseconds] = $this->minimum;
        }

        if ($timestamp > $this->maximum[0] || ($timestamp === $this->maximum[0] && $microseconds > $this->maximum[1])) {
            [$timestamp, $microseconds] = $this->maximum;
        }

        return DateTimeImmutable::createFromTimestamp($timestamp)->setMicrosecond($microseconds)->setTimezone($timezone);
    }

    private function notableTimestamp(ChoiceSource $source, DateTimeZone $timezone): int
    {
        $kind = $source->choose($this->kinds, $this->uniform);

        if ($kind === self::NOTABLE_TIMESTAMP && $this->notableTimestamps !== []) {
            return $source->choose($this->notableTimestamps, $this->uniform);
        }

        if ($kind === self::LEAP_DAY && $this->leapYears !== null) {
            $year = self::FIRST_LEAP_YEAR + 4 * $this->leapYears->generate($source);

            // a century that cannot be divided by 400 is not a leap year,
            // the next leap year is used instead
            if ($year % 100 === 0 && $year % 400 !== 0) {
                $year += 4;
            }

            return self::startOfDay($timezone, $year, 2, 29) + $this->secondsOfDay->generate($source);
        }

        $year = self::EPOCH_YEAR + $this->years->generate($source);

        if ($kind === self::START_OF_MONTH) {
            // the second before the first second of January is the last
            // second of the year before
            return self::startOfDay($timezone, $year, 1 + $source->draw(11, $this->uniform), 1) - $source->draw(1, $this->uniform);
        }

        $startOfYear = self::startOfDay($timezone, $year, 1, 1);
        $transitions = $timezone->getTransitions($startOfYear, self::startOfDay($timezone, $year + 1, 1, 1) - 1);

        if ($transitions === false) {
            return $startOfYear;
        }

        // the first transition is the start of the year itself
        array_shift($transitions);

        if ($transitions === []) {
            return $startOfYear;
        }

        return $source->choose($transitions, $this->uniform)['ts'] - $source->draw(1, $this->uniform);
    }

    private static function startOfDay(DateTimeZone $timezone, int $year, int $month, int $day): int
    {
        return new DateTimeImmutable('@0')->setTimezone($timezone)->setDate($year, $month, $day)->setTime(0, 0)->getTimestamp();
    }

    private static function year(int $timestamp): int
    {
        return (int) DateTimeImmutable::createFromTimestamp($timestamp)->format('Y');
    }
}
