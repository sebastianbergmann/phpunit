<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Source;

use SplFixedArray;

final class SomeClassWithSplFixedArray extends SplFixedArray
{
}
