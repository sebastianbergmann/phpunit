<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

final class SkipDateTimeMix
{
    public function markDate(
        \DateTimeInterface $date
    ): bool
    {
        return true;
    }

    static public function run(): void
    {
        $skipDateTimeMix = new SkipDateTimeMix();
        $skipDateTimeMix->markDate(
            new \DateTimeImmutable('15:30')
        );
    }
}
