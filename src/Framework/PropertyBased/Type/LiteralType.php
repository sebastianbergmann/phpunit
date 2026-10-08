<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use function addcslashes;
use function assert;
use function is_float;
use function is_int;
use function var_export;

/**
 * An integer, floating-point, or string literal, for instance 42 or 'a'.
 * true, false, and null are keywords and parsed as NamedType.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class LiteralType extends Type
{
    private float|int|string $value;

    public function __construct(float|int|string $value)
    {
        $this->value = $value;
    }

    public function value(): float|int|string
    {
        return $this->value;
    }

    public function asString(): string
    {
        if (is_int($this->value)) {
            return (string) $this->value;
        }

        if (is_float($this->value)) {
            $value = var_export($this->value, true);

            assert($value !== '');

            return $value;
        }

        return "'" . addcslashes($this->value, "'\\") . "'";
    }
}
