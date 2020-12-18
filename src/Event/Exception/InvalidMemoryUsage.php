<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Exception;

use function sprintf;
use InvalidArgumentException;
use PHPUnit\Event\Exception;

final class InvalidMemoryUsage extends InvalidArgumentException implements Exception
{
    public static function bytes(int $bytes): self
    {
        return new self(sprintf(
            '%d is not a valid memory usage in bytes.',
            $bytes
        ));
    }
}
