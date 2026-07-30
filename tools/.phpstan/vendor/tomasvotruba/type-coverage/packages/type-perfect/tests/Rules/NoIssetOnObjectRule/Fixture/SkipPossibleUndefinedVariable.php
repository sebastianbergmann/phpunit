<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\Fixture;

final class SkipIssetOnArrayNestedOnObject
{
    public function run(bool $condition)
    {
        if ($condition) {
            $object = new \stdClass();
        }

        if (isset($object)) {
            $object->foo = 'bar';
        }
    }
}
