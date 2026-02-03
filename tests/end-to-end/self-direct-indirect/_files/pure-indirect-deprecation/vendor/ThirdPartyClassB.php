<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

use const E_USER_DEPRECATED;
use function trigger_error;

final class ThirdPartyClassB
{
    public function triggerDeprecation(): void
    {
        @trigger_error('deprecation in third-party code triggered by third-party code', E_USER_DEPRECATED);
    }
}
