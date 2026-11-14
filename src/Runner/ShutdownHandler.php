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

use const PHP_EOL;
use function getmypid;
use function register_shutdown_function;
use function rtrim;
use Closure;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ShutdownHandler
{
    private static bool $registered = false;
    private static string $message  = '';

    /**
     * @var ?Closure(): iterable<string>
     */
    private static ?Closure $details = null;

    /**
     * @param ?Closure(): iterable<string> $details returns what is printed after the message, which is only worked out when the PHP process ends prematurely, in parts: each part is printed, after an empty line, before the next one is worked out, so that it is printed even when working out the next one ends the process
     */
    public static function setMessage(string $message, ?Closure $details = null): void
    {
        self::register();

        self::$message = $message;
        self::$details = $details;
    }

    public static function resetMessage(): void
    {
        self::$message = '';
        self::$details = null;
    }

    private static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;
        $pid              = getmypid();

        register_shutdown_function(
            static function () use ($pid): void
            {
                // This runs during PHP's shutdown sequence, after code coverage
                // data has been collected; it is covered by end-to-end tests
                // @codeCoverageIgnoreStart
                $message = rtrim(self::$message);

                if ($message === '' || $pid !== getmypid()) {
                    return;
                }

                print $message . PHP_EOL;

                if (self::$details !== null) {
                    try {
                        foreach ((self::$details)() as $details) {
                            $details = rtrim($details);

                            if ($details !== '') {
                                print "\n" . $details . "\n";
                            }
                        }
                    } catch (Throwable) {
                        // the details that were worked out before have been
                        // printed
                    }
                }

                exit(2);
                // @codeCoverageIgnoreEnd
            },
        );
    }
}
