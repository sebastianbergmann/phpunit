<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\BaselineIgnoreDeprecation;

function trigger_deprecation(string $message): void
{
    @trigger_error($message, E_USER_DEPRECATED);
}
