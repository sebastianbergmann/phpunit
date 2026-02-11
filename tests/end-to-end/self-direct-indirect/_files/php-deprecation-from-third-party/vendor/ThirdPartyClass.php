<?php
namespace PHPUnit\TestFixture\SelfDirectIndirect;

use function strlen;

final class ThirdPartyClass
{
    public function method(): void
    {
        @strlen(null);
    }
}
