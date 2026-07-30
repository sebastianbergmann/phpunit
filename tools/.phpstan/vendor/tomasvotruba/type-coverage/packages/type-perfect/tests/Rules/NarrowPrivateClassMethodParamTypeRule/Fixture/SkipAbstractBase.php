<?php

namespace SkipAbstractBase;

use Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Source\ConceptBase;
use Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Source\ConceptImpl1;

class MyService {
    public function run(ConceptImpl1 $arg)
    {
        $this->isCheck($arg);
    }
    private function isCheck(ConceptBase $arg)
    {
    }
}
