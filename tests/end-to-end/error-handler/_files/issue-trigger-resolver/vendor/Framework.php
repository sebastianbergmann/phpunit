<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolver;

use const E_USER_DEPRECATED;
use function trigger_error;

final class Framework
{
    public function trigger(): void
    {
        @trigger_error('framework deprecation', E_USER_DEPRECATED);
    }
}
