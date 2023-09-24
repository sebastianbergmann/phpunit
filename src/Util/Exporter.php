<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function is_array;
use function is_scalar;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Exporter
{
    public static function export(mixed $value, bool $exportObjects = false): string
    {
        if (self::isScalarOrArrayOfScalars($value) || $exportObjects) {
            return (new \SebastianBergmann\Exporter\Exporter)->export($value);
        }

        return '{enable export of objects to see this value}';
    }

    private static function isScalarOrArrayOfScalars(mixed $value): bool
    {
        if (is_scalar($value)) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $_value) {
            if (!self::isScalarOrArrayOfScalars($_value)) {
                return false;
            }
        }

        return true;
    }
}
