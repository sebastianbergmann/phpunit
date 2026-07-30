<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\Source\SomeClassWithArrayAccess;

final class ArrayAccessOnObject
{
    public function run()
    {
        $someClassWithArrayAcces = new SomeClassWithArrayAccess();
        return $someClassWithArrayAcces['key'];
    }
}
