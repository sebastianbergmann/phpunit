<?php declare(strict_types=1);
/*
 * Simulates an error handler that is registered before PHPUnit (for example in a
 * bootstrap file, like Drupal's BootstrapErrorHandler) and that suppresses some
 * deprecations before PHPUnit can see them, while delegating all other issues back
 * into PHPUnit's own error handler.
 *
 * The delegation back into PHPUnit's handler exercises the re-entrancy guard in
 * PHPUnit\Runner\ErrorHandler: without it, PHPUnit would call this handler, this
 * handler would call PHPUnit, and PHPUnit would call this handler again, ad infinitum.
 */
set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    if ($errorNumber === E_USER_DEPRECATED && str_contains($errorString, 'please ignore this deprecation')) {
        return true;
    }

    return (bool) PHPUnit\Runner\ErrorHandler::instance()($errorNumber, $errorString, $errorFile, $errorLine);
});
