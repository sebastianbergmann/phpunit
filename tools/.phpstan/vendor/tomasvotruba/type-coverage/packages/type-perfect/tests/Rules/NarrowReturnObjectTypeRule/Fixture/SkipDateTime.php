<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use DateTime;
use DateTimeInterface;

final class SkipDateTime
{
    public function create(DateTime $dateTime): DateTimeInterface
    {
        return $dateTime;
    }
}
