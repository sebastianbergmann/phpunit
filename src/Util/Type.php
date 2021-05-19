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

use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Type
{
    public static function isType(string $type): bool
    {
        return match ($type) {
            'numeric', 'integer', 'int', 'iterable', 'float', 'string', 'boolean', 'bool', 'null', 'array', 'object', 'resource', 'scalar' => true,
            default => false,
        };
    }

    public static function isCloneable(object $object): bool
    {
        try {
            $clone = clone $object;
        } catch (Throwable $t) {
            return false;
        }

        return $clone instanceof $object;
    }
}
