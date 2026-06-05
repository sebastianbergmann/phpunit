<?php declare(strict_types=1);
/*
 * Simulates an error handler that is registered before PHPUnit (for example in a
 * bootstrap file) and that suppresses some deprecations before PHPUnit can see them,
 * while delegating all other issues to PHPUnit by returning false.
 */
set_error_handler(static function (int $errorNumber, string $errorString): bool {
    if ($errorNumber === E_USER_DEPRECATED && str_contains($errorString, 'please ignore this deprecation')) {
        return true;
    }

    return false;
});
