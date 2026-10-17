<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function date;

/**
 * When what is known about the tests was recorded.
 *
 * What changed is what is not what it was when the recording was made. A
 * recording that is older than a developer assumes makes more tests look
 * affected than they expect, and a recording that is newer makes fewer look
 * affected. When it was made is therefore said wherever it is used.
 *
 * The time is said in the time zone PHP is configured for, and the time zone is
 * said with it: the machine a developer works on and the container their tests
 * run in are often configured for different time zones.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RecordingTime
{
    private int $timestamp;

    public static function fromUnixTimestamp(int $timestamp): self
    {
        return new self($timestamp);
    }

    private function __construct(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function asUnixTimestamp(): int
    {
        return $this->timestamp;
    }

    public function asString(): string
    {
        return date('Y-m-d H:i:s T', $this->timestamp);
    }
}
