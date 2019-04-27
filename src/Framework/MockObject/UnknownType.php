<?php


namespace PHPUnit\Framework\MockObject;


class UnknownType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return true;
    }

    public function getReturnTypeDeclaration(): string
    {
        return '';
    }

    public function allowsNull(): bool
    {
        return true;
    }
}
