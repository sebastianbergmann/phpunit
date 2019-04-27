<?php


namespace PHPUnit\Framework\MockObject;


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

    public function __construct(TypeName $className, bool $nullable)
    {
        $this->className = $className;
        $this->allowsNull = $nullable;
    }

    public function isAssignable(Type $other): bool
    {
        if ($this->allowsNull && isNull($other)) {
            return true;
        }
        if ($other instanceof self) {
            if ($this->className->getQualifiedName() === $other->className->getQualifiedName()) {
                return true;
            }

            if (is_subclass_of($other->className->getQualifiedName(), $this->className->getQualifiedName(), true)) {
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
