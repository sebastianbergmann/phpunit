<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function gettype;
use function is_array;
use function is_bool;
use function is_callable;
use function is_float;
use function is_int;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_scalar;
use function is_string;
use function sprintf;
use PHPUnit\Framework\UnknownTypeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsType extends Constraint
{
    public const string TYPE_ARRAY           = 'array';
    public const string TYPE_BOOL            = 'bool';
    public const string TYPE_FLOAT           = 'float';
    public const string TYPE_INT             = 'int';
    public const string TYPE_NULL            = 'null';
    public const string TYPE_NUMERIC         = 'numeric';
    public const string TYPE_OBJECT          = 'object';
    public const string TYPE_RESOURCE        = 'resource';
    public const string TYPE_CLOSED_RESOURCE = 'resource (closed)';
    public const string TYPE_STRING          = 'string';
    public const string TYPE_SCALAR          = 'scalar';
    public const string TYPE_CALLABLE        = 'callable';
    public const string TYPE_ITERABLE        = 'iterable';

    /**
     * @var non-empty-array<non-empty-string, bool>
     */
    private const array KNOWN_TYPES = [
        'array'             => true,
        'boolean'           => true,
        'bool'              => true,
        'double'            => true,
        'float'             => true,
        'integer'           => true,
        'int'               => true,
        'null'              => true,
        'numeric'           => true,
        'object'            => true,
        'real'              => true,
        'resource'          => true,
        'resource (closed)' => true,
        'string'            => true,
        'scalar'            => true,
        'callable'          => true,
        'iterable'          => true,
    ];

    /**
     * @var 'array'|'bool'|'boolean'|'callable'|'double'|'float'|'int'|'integer'|'iterable'|'null'|'numeric'|'object'|'real'|'resource (closed)'|'resource'|'scalar'|'string'
     */
    private readonly string $type;

    /**
     * @param 'array'|'bool'|'boolean'|'callable'|'double'|'float'|'int'|'integer'|'iterable'|'null'|'numeric'|'object'|'real'|'resource (closed)'|'resource'|'scalar'|'string' $type
     *
     * @throws UnknownTypeException
     */
    public function __construct(string $type)
    {
        /** @phpstan-ignore isset.offset */
        if (!isset(self::KNOWN_TYPES[$type])) {
            throw new UnknownTypeException($type);
        }

        $this->type = $type;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return sprintf(
            'is of type %s',
            $this->type,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        switch ($this->type) {
            case 'numeric':
                return is_numeric($other);

            case 'integer':
            case 'int':
                return is_int($other);

            case 'double':
            case 'float':
            case 'real':
                return is_float($other);

            case 'string':
                return is_string($other);

            case 'boolean':
            case 'bool':
                return is_bool($other);

            case 'null':
                return null === $other;

            case 'array':
                return is_array($other);

            case 'object':
                return is_object($other);

            case 'resource':
                $type = gettype($other);

                return $type === 'resource' || $type === 'resource (closed)';

            case 'resource (closed)':
                return gettype($other) === 'resource (closed)';

            case 'scalar':
                return is_scalar($other);

            case 'callable':
                return is_callable($other);

            case 'iterable':
                return is_iterable($other);

            default:
                return false;
        }
    }
}
