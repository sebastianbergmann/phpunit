<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;

final class SkipNoArgs
{
    public function run()
    {
        $this->execute();
    }
}
