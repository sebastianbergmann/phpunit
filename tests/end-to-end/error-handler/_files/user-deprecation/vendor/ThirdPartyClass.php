<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\UserDeprecation;

use const E_USER_DEPRECATED;
use function trigger_error;

final class ThirdPartyClass
{
    public function trigger(): void
    {
        @trigger_error('deprecation in third-party code', E_USER_DEPRECATED);
    }

    public function callFirstParty(): void
    {
        (new FirstPartyClass)->triggerSelf();
    }
}
