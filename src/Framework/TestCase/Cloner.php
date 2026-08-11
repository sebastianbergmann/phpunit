<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use const E_DEPRECATED;
use function assert;
use function dirname;
use function restore_error_handler;
use function set_error_handler;
use function str_starts_with;
use DeepCopy\DeepCopy;
use ReflectionClass;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Cloner
{
    private static ?string $deepCopyDirectory = null;

    /**
     * Creates a deep clone of the value returned by a depended-upon test.
     *
     * Deprecations that myclabs/deep-copy triggers for its own code are an
     * implementation detail of PHPUnit that the user cannot act on and are
     * therefore not reported. Issues triggered by the code under test while
     * its object graph is copied, in a __clone() method for instance, are
     * still reported.
     */
    public static function deepClone(mixed $value): mixed
    {
        $deepCopy = new DeepCopy;
        $deepCopy->skipUncloneable(false);

        $previousErrorHandler = set_error_handler(
            static function (int $number, string $string, string $file, int $line) use (&$previousErrorHandler): bool
            {
                if ($number === E_DEPRECATED && str_starts_with($file, self::deepCopyDirectory())) {
                    return true;
                }

                if ($previousErrorHandler === null) {
                    return false;
                }

                if ($previousErrorHandler($number, $string, $file, $line) === false) {
                    return false;
                }

                return true;
            },
        );

        try {
            return $deepCopy->copy($value);
        } finally {
            restore_error_handler();
        }
    }

    private static function deepCopyDirectory(): string
    {
        if (self::$deepCopyDirectory === null) {
            $file = new ReflectionClass(DeepCopy::class)->getFileName();

            assert($file !== false);

            self::$deepCopyDirectory = dirname($file);
        }

        return self::$deepCopyDirectory;
    }
}
