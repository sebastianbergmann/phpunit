<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod;

final class ThirdPartyClass
{
    public function method(): void
    {
        DeprecationTrigger::triggerDeprecation('deprecation via method trigger');
    }
}
