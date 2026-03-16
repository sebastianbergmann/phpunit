<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped;

use const E_USER_DEPRECATED;
use function trigger_error;

final class ThirdPartyClass
{
    public function deprecatedMethod(): void
    {
        @trigger_error('third-party deprecation', E_USER_DEPRECATED);
    }
}
