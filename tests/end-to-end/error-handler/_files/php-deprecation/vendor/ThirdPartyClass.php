<?php
namespace PHPUnit\TestFixture\ErrorHandler\PhpDeprecation;

use function strlen;

final class ThirdPartyClass
{
    public function method(): true
    {
        @strlen(null);

        return true;
    }
}
