<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\UserDeprecation;

final class ThirdPartyClassA
{
    public function callB(): void
    {
        (new ThirdPartyClassB)->trigger();
    }
}
