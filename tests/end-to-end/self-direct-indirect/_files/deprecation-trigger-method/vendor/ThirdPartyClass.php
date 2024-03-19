<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

final class ThirdPartyClass
{
    public function method(): void
    {
        DeprecationTrigger::triggerDeprecation('deprecation in third-party code');
    }

    public function anotherMethod(): true
    {
        return (new FirstPartyClass)->method();
    }
}
