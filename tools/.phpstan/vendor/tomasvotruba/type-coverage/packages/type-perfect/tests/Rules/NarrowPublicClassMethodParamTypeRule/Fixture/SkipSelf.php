<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture;

class SkipSelf {
    public function takesSelf(self $code): bool
    {
        return true;
    }

    static public function run(SkipSelf $self): void
    {
        $self->takesSelf(new SkipSelf());
    }
}
