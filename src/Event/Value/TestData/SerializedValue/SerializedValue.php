<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestData;

use function serialize;
use Exception;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class SerializedValue
{
    public static function from(mixed $value): self
    {
        try {
            return new SerializationSucceeded(serialize($value));
        } catch (Exception) {
            return new SerializationFailed;
        }
    }

    /**
     * @psalm-assert-if-true SerializationSucceeded $this
     */
    public function hasValue(): bool
    {
        return false;
    }
}
