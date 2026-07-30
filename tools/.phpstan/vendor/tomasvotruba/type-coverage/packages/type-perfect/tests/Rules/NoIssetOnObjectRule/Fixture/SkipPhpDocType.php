<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\Fixture;

/** @var SkipPhpDocType|null $x */
if (isset($x)) {
    return $x;
}

class SkipPhpDocType {}
