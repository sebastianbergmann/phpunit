<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Future
{
    private static bool $isFutureModeEnforced = false;

    /**
     * @var array<string, true>
     */
    private static array $deprecations = [];

    private function __construct()
    {
        // cannot create instance
    }

    /**
     * @return mixed
     */
    public static function runWithEnforcedFutureMode(callable $callback)
    {
        try {
            self::$isFutureModeEnforced = true;

            return $callback();
        } finally {
            self::$isFutureModeEnforced = false;
        }
    }

    public static function isFutureModeEnabled(): bool
    {
        return self::$isFutureModeEnforced || filter_var(
            getenv('PHP_CS_FIXER_FUTURE_MODE'),
            \FILTER_VALIDATE_BOOL,
        );
    }

    public static function triggerDeprecation(\Exception $futureException): void
    {
        if (self::isFutureModeEnabled()) {
            throw new \RuntimeException(
                'Your are using something deprecated, see previous exception. Aborting execution because `PHP_CS_FIXER_FUTURE_MODE` environment variable is set.',
                0,
                $futureException,
            );
        }

        $message = $futureException->getMessage();

        self::$deprecations[$message] = true;
        @trigger_error($message, \E_USER_DEPRECATED);
    }

    /**
     * @return list<string>
     */
    public static function getTriggeredDeprecations(): array
    {
        $triggeredDeprecations = array_keys(self::$deprecations);
        sort($triggeredDeprecations);

        return $triggeredDeprecations;
    }

    /**
     * @template T
     *
     * @param T $new
     * @param T $old
     *
     * @return T
     *
     * @TODO v4.0: remove this method, ensure code compiles, create getV5OrV4. While removing, ensure to document in `UPGRADE-vX.md` file.
     */
    public static function getV4OrV3($new, $old)
    {
        return self::getNewOrOld($new, $old);
    }

    /**
     * @template T
     *
     * @param T $new
     * @param T $old
     *
     * @return T
     */
    private static function getNewOrOld($new, $old)
    {
        return self::isFutureModeEnabled() ? $new : $old;
    }
}
