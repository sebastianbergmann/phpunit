<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoEmptyOnObjectRule\Fixture;

use stdClass;

final class EmptyOnObject
{
    public function run()
    {
        $object = null;

        if (mt_rand(0, 100)) {
            $object = new stdClass();
        }

        if (!empty($object)) {
            return $object;
        }
    }
}
