<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

function trigger_deprecation(string $message): void
{
    @trigger_error($message, E_USER_DEPRECATED);
}
