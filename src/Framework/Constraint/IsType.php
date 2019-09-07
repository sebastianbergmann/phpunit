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

/**
 * Constraint that asserts that the value it is evaluated for is of a
 * specified type.
 *
 * The expected value is passed in the constructor.
 */
final class IsType extends Constraint
{
    /**
     * @var string
     */
    public const TYPE_ARRAY = 'array';

    /**
     * @var string
     */
    public const TYPE_BOOL = 'bool';

    /**
     * @var string
     */
    public const TYPE_FLOAT = 'float';

    /**
     * @var string
     */
    public const TYPE_INT = 'int';

    /**
     * @var string
     */
    public const TYPE_NULL = 'null';

    /**
     * @var string
     */
    public const TYPE_NUMERIC = 'numeric';

    /**
     * @var string
     */
    public const TYPE_OBJECT = 'object';

    /**
     * @var string
     */
    public const TYPE_RESOURCE = 'resource';

    /**
     * @var string
     */
    public const TYPE_STRING = 'string';

    /**
     * @var string
     */
    public const TYPE_SCALAR = 'scalar';

    /**
     * @var string
     */
    public const TYPE_CALLABLE = 'callable';

    /**
     * @var string
     */
    public const TYPE_ITERABLE = 'iterable';

    /**
     * @var array<string,bool>
     */
    private const KNOWN_TYPES = [
        'array'    => true,
        'boolean'  => true,
        'bool'     => true,
        'double'   => true,
        'float'    => true,
        'integer'  => true,
        'int'      => true,
        'null'     => true,
        'numeric'  => true,
        'object'   => true,
        'real'     => true,
        'resource' => true,
        'string'   => true,
        'scalar'   => true,
        'callable' => true,
        'iterable' => true,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(string $type)
    {
        if (!isset(self::KNOWN_TYPES[$type])) {
            throw new \PHPUnit\Framework\Exception(
                \sprintf(
                    'Type specified for PHPUnit\Framework\Constraint\IsType <%s> ' .
                    'is not a valid type.',
                    $type
                )
            );
        }

        $this->type = $type;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return \sprintf(
            'is of type "%s"',
            $this->type
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        switch ($this->type) {
            case 'numeric':
                return \is_numeric($other);

            case 'integer':
            case 'int':
                return \is_int($other);

            case 'double':
            case 'float':
            case 'real':
                return \is_float($other);

            case 'string':
                return \is_string($other);

            case 'boolean':
            case 'bool':
                return \is_bool($other);

            case 'null':
                return null === $other;

            case 'array':
                return \is_array($other);

            case 'object':
                return \is_object($other);

            case 'resource':
                if (\is_resource($other)) {
                    return true;
                }

                try {
                    $resource = @\get_resource_type($other);

                    if (\is_string($resource)) {
                        return true;
                    }
                } catch (\TypeError $e) {
                }

                return false;

            case 'scalar':
                return \is_scalar($other);

            case 'callable':
                return \is_callable($other);

            case 'iterable':
                return \is_iterable($other);

            default:
                return false;
        }
    }
}
