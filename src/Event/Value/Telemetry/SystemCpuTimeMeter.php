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

use function getrusage;
use function is_int;
use function sprintf;
use PHPUnit\Event\RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class SystemCpuTimeMeter implements CpuTimeMeter
{
    /**
     * @throws RuntimeException
     */
    public function userCpuTime(): CpuTime
    {
        return $this->cpuTime('ru_utime.tv_sec', 'ru_utime.tv_usec');
    }

    /**
     * @throws RuntimeException
     */
    public function systemCpuTime(): CpuTime
    {
        return $this->cpuTime('ru_stime.tv_sec', 'ru_stime.tv_usec');
    }

    /**
     * @param 'ru_stime.tv_sec'|'ru_utime.tv_sec'   $secondsKey
     * @param 'ru_stime.tv_usec'|'ru_utime.tv_usec' $microsecondsKey
     *
     * @throws RuntimeException
     */
    private function cpuTime(string $secondsKey, string $microsecondsKey): CpuTime
    {
        $usage = getrusage();

        if ($usage === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('getrusage() failed.');
            // @codeCoverageIgnoreEnd
        }

        if (!isset($usage[$secondsKey]) || !isset($usage[$microsecondsKey])) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                sprintf(
                    'getrusage() did not return the expected keys "%s" and "%s".',
                    $secondsKey,
                    $microsecondsKey,
                ),
            );
            // @codeCoverageIgnoreEnd
        }

        $seconds      = $usage[$secondsKey];
        $microseconds = $usage[$microsecondsKey];

        if (!is_int($seconds) || !is_int($microseconds)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                sprintf(
                    'getrusage() returned non-integer values for "%s" and/or "%s".',
                    $secondsKey,
                    $microsecondsKey,
                ),
            );
            // @codeCoverageIgnoreEnd
        }

        return CpuTime::fromSecondsAndNanoseconds($seconds, $microseconds * 1000);
    }
}
