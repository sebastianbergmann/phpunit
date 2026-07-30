<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;

class SkipMixed
{
    public function run($arg)
    {
        $this->isCheck($arg);
    }
    private function isCheck(\PhpParser\Node $arg)
    {
    }
}
