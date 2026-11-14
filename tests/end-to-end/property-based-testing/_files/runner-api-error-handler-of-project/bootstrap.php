<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use const E_USER_WARNING;
use function set_error_handler;
use ErrorException;

final class ErrorHandlerThatCanOnlyBeCalledInItsScope
{
    public function register(): void
    {
        set_error_handler([$this, 'handle']);
    }

    private function handle(): bool
    {
        return false;
    }
}

new ErrorHandlerThatCanOnlyBeCalledInItsScope()->register();

set_error_handler(
    static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        if ($errorNumber !== E_USER_WARNING) {
            return false;
        }

        throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
    },
);
