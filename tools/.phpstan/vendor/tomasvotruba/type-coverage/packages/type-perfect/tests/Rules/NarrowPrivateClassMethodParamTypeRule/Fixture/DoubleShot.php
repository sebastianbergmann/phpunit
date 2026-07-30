<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;

use PhpParser\Node;

class DoubleShot
{
    public function run(Node\Arg $arg, Node\Param $param)
    {
        $this->isCheck($arg, $param);
    }
    private function isCheck(\PhpParser\Node $arg, \PhpParser\Node $param)
    {
    }
}
