<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction;

final class ThirdPartyClass
{
    public function method(): void
    {
        trigger_deprecation('deprecation via function trigger');
    }
}
