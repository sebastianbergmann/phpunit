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

use function date_default_timezone_get;
use function date_default_timezone_set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordingTime::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class RecordingTimeTest extends TestCase
{
    public function testCanBeCreatedFromAUnixTimestamp(): void
    {
        $this->assertSame(1700000000, RecordingTime::fromUnixTimestamp(1700000000)->asUnixTimestamp());
    }

    public function testIsSaidInTheTimeZonePhpIsConfiguredForTogetherWithThatTimeZone(): void
    {
        $timeZone = date_default_timezone_get();

        try {
            date_default_timezone_set('UTC');

            $this->assertSame('2023-11-14 22:13:20 UTC', RecordingTime::fromUnixTimestamp(1700000000)->asString());

            date_default_timezone_set('Europe/Berlin');

            $this->assertSame('2023-11-14 23:13:20 CET', RecordingTime::fromUnixTimestamp(1700000000)->asString());
        } finally {
            date_default_timezone_set($timeZone);
        }
    }
}
