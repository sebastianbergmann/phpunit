<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class ObjectType extends Type
{
    /**
     * @var TypeName
     */
    private $className;

    /**
     * @var bool
     */
    private $allowsNull;

    public function __construct(TypeName $className, bool $allowsNull)
    {
        $this->className  = $className;
        $this->allowsNull = $allowsNull;
    }

    public function isAssignable(Type $other): bool
    {
        if ($this->allowsNull && $other instanceof NullType) {
            return true;
        }

        if ($other instanceof self) {
            if ($this->className->getQualifiedName() === $other->className->getQualifiedName()) {
                return true;
            }

            if (\is_subclass_of($other->className->getQualifiedName(), $this->className->getQualifiedName(), true)) {
                return true;
            }
        }

        return false;
    }

    public function getReturnTypeDeclaration(): string
    {
        return ': ' . ($this->allowsNull ? '?' : '') . $this->className->getQualifiedName();
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }
}
