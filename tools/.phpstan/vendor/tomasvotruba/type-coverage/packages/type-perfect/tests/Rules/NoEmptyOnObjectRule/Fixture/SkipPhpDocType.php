<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoEmptyOnObjectRule\Fixture;

/** @var SkipPhpDocType|null $x */
if (!empty($x)) {
    return $x;
}

class SkipPhpDocType {}
