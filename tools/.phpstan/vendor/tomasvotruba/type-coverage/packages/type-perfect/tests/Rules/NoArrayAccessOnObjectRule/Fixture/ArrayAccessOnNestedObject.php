<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Source\ChildOfSomeClassWithArrayAccess;

final class ArrayAccessOnNestedObject
{
    public function run()
    {
        $someClassWithArrayAcces = new ChildOfSomeClassWithArrayAccess();
        return $someClassWithArrayAcces['key'];
    }
}
