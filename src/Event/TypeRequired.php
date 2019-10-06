<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use InvalidArgumentException;
use PHPUnit\Exception;

final class TypeRequired extends InvalidArgumentException implements Exception
{
    public static function create(): self
    {
        return new self('At least one type needs to be provided.');
    }
}
