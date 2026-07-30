<?php

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\Generics;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\SkipSameGeneric\MyGroup;
use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\SkipSameGeneric\MyIterator;

final class SkipSameGeneric
{
    /**
     * @param MyIterator<MyGroup> $_werbemasse
     */
    public function setWerbemasse($_werbemasse): void
    {
        $this->_werbemasse = $_werbemasse;
    }
}


