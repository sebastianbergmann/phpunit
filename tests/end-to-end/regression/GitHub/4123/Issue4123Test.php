<?php declare(strict_types=1);

use Prophecy\Exception\Doubler\ClassMirrorException;

class Issue4123Test extends PHPUnit\Framework\TestCase
{
    public function testExpectClassMirrorExceptionWhenFinalClassHasBeenProphetized(): void
    {
        static::expectException(ClassMirrorException::class);

        static::prophesize(FinalSimpleClass::class);
    }
}

final class FinalSimpleClass
{
}
