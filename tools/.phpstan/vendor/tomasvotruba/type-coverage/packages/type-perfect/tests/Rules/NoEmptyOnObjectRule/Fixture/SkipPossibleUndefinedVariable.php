<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\Fixture;

final class SkipPossibleUndefinedVariable
{
    public function run(bool $condition)
    {
        if ($condition) {
            $object = new \stdClass();
        }

        if (!empty($object)) {
            $object->foo = 'bar';
        }
    }
}
