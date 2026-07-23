<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode;

final class ThirdPartyClass
{
    public function method(FirstPartyClass $firstPartyClass): void
    {
        $firstPartyClass->method();
    }
}
