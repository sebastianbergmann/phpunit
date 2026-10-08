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
use function is_int;
use function preg_match;

/**
 * An item of an array shape: a key, which may be optional, and the type of
 * its value. The key of an item of a shape such as list{int, string} is null.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ArrayShapeItem
{
    private null|int|string $key;
    private bool $optional;
    private Type $type;

    public function __construct(null|int|string $key, bool $optional, Type $type)
    {
        $this->key      = $key;
        $this->optional = $optional;
        $this->type     = $type;
    }

    public function key(): null|int|string
    {
        return $this->key;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function type(): Type
    {
        return $this->type;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        if ($this->key === null) {
            return $this->type->asString();
        }

        $key = $this->key;

        if (!is_int($key) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) !== 1) {
            $key = "'" . addcslashes($key, "'\\") . "'";
        }

        if ($this->optional) {
            return $key . '?: ' . $this->type->asString();
        }

        return $key . ': ' . $this->type->asString();
    }
}
