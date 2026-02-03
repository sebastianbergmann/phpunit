<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

final class ThirdPartyClass
{
    public function callFirstParty(): true
    {
        return (new FirstPartyClass)->method();
    }
}
