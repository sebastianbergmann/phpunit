<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

final class ThirdPartyClass
{
    public function method(): void
    {
        @trigger_error('deprecation in third-party code', E_USER_DEPRECATED);
    }

    public function anotherMethod(): true
    {
        return (new FirstPartyClass)->method();
    }
}
