<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

final class ThirdPartyClassA
{
    public function callAnotherThirdParty(): void
    {
        (new ThirdPartyClassB)->triggerDeprecation();
    }
}
