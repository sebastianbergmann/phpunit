<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

final class Type
{
    public static function isType(string $type): bool
    {
        switch ($type) {
            case 'numeric':
            case 'integer':
            case 'int':
            case 'iterable':
            case 'float':
            case 'string':
            case 'boolean':
            case 'bool':
            case 'null':
            case 'array':
            case 'object':
            case 'resource':
            case 'scalar':
                return true;

            default:
                return false;
        }
    }
}
