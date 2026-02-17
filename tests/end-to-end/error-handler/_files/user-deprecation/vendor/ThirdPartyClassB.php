<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\UserDeprecation;

use const E_USER_DEPRECATED;
use function trigger_error;

final class ThirdPartyClassB
{
    public function trigger(): void
    {
        @trigger_error('deprecation in third-party code B', E_USER_DEPRECATED);
    }
}
