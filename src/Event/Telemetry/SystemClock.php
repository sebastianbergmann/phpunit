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

use DateTimeImmutable;
use DateTimeZone;

final class SystemClock implements Clock
{
    private DateTimeZone $dateTimeZone;

    public function __construct(DateTimeZone $dateTimeZone)
    {
        $this->dateTimeZone = $dateTimeZone;
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            'now',
            $this->dateTimeZone
        );
    }
}
