<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\BaselineIgnoreDeprecation;

trigger_deprecation('deprecation in third-party code');

final class ThirdPartyClass
{
    public function anotherMethod(): true
    {
        return true;
    }
}
