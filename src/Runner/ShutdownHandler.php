<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function register_shutdown_function;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ShutdownHandler
{
    private static bool $registered = false;
    private static string $message  = '';

    public static function setMessage(string $message): void
    {
        self::register();

        self::$message = $message;
    }

    public static function resetMessage(): void
    {
        self::$message = '';
    }

    private static function register(): void
    {
        if (self::$registered) {
            return;
        }

        register_shutdown_function(static function (): void
        {
            print self::$message;
        });
    }
}
