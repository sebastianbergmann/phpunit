<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

final class DeprecationTrigger
{
    public static function triggerDeprecation(string $message): void
    {
        @trigger_error($message, E_USER_DEPRECATED);
    }
}
